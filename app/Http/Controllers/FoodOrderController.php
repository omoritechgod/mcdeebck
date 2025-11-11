<?php

namespace App\Http\Controllers;

use App\Models\FoodMenu;
use App\Models\FoodOrder;
use App\Models\FoodOrderItem;
use App\Models\FoodVendor;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\AdminWallet;
use App\Models\AdminTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="Food Orders", description="Food Order APIs for customers and vendors")
 */
class FoodOrderController extends Controller
{
    /**
     * Create a food order - user places an order pending vendor approval.
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:food_menus,id',
            'items.*.quantity' => 'required|integer|min:1',
            'delivery_method' => 'required|in:delivery,pickup,offline_rider',
            'shipping_address' => 'required|array',
            'shipping_address.address' => 'required|string',
            'shipping_address.city' => 'required|string',
            'shipping_address.phone' => 'required|string',
            'shipping_address.latitude' => 'nullable|numeric',
            'shipping_address.longitude' => 'nullable|numeric',
            'tip_amount' => 'nullable|numeric|min:0',
        ]);

        $user = Auth::user();
        $vendorId = $request->vendor_id;

        $foodVendor = FoodVendor::where('vendor_id', $vendorId)->first();
        if (!$foodVendor) {
            return response()->json(['error' => 'Food vendor not found'], 404);
        }

        if (!$foodVendor->is_open) {
            return response()->json(['error' => 'Vendor is currently closed'], 400);
        }

        $subtotal = 0;
        $orderItems = [];

        foreach ($request->items as $item) {
            $menu = FoodMenu::where('id', $item['menu_id'])
                ->where('vendor_id', $vendorId)
                ->first();

            if (!$menu) {
                return response()->json([
                    'error' => "Menu item {$item['menu_id']} does not belong to this vendor"
                ], 400);
            }

            if (!$menu->is_available) {
                return response()->json([
                    'error' => "Menu item '{$menu->name}' is not available"
                ], 400);
            }

            $itemTotal = $menu->price * $item['quantity'];
            $subtotal += $itemTotal;

            $orderItems[] = [
                'menu' => $menu,
                'quantity' => $item['quantity'],
                'price' => $menu->price,
                'total_price' => $itemTotal,
            ];
        }

        // ✅ Enforce minimum order total
        if ($subtotal < $foodVendor->minimum_order_amount) {
            return response()->json([
                'error' => "Minimum order amount is NGN {$foodVendor->minimum_order_amount}",
                'minimum' => $foodVendor->minimum_order_amount,
                'current' => $subtotal,
            ], 400);
        }

        // ✅ Delivery fee and tip calculation
        $deliveryFee = $request->delivery_method === 'delivery' ? $foodVendor->delivery_fee : 0;
        $tipAmount = $request->input('tip_amount', 0);
        $commissionRate = (float) config('commissions.food', 10) / 100;
        $commissionAmount = round($subtotal * $commissionRate, 2);
        $totalAmount = $subtotal + $deliveryFee + $tipAmount;

        DB::beginTransaction();
        try {
            $order = FoodOrder::create([
                'user_id' => $user->id,
                'vendor_id' => $vendorId,
                'total' => $totalAmount,
                'tip_amount' => $tipAmount,
                'delivery_fee' => $deliveryFee,
                'commission_amount' => $commissionAmount,
                'payment_status' => FoodOrder::PAYMENT_STATUS_PENDING,
                'status' => FoodOrder::STATUS_AWAITING_VENDOR,
                'delivery_method' => $request->delivery_method,
                'shipping_address' => $request->shipping_address,
            ]);

            foreach ($orderItems as $item) {
                FoodOrderItem::create([
                    'food_order_id' => $order->id,
                    'food_menu_id' => $item['menu']->id,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'total_price' => $item['total_price'],
                ]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully, awaiting vendor approval.',
                'order' => $order->load('items.menuItem', 'foodVendor'),
                'awaiting_vendor' => true,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Food order creation failed', [
                'user_id' => $user->id ?? null,
                'vendor_id' => $vendorId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Failed to create order',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * List user's food orders.
     */
    public function index(Request $request)
    {
        $query = FoodOrder::with([
            'items.menuItem:id,name,image,price',
            'foodVendor:id,vendor_id,business_name,logo,contact_phone,contact_email',
            'rider:id,user_id'
        ])->where('user_id', Auth::id());

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $orders = $query->latest()->paginate(20);

        // ✅ Fix: Properly handle collection transformation to avoid IDE red underline
        $orders->setCollection(
            $orders->getCollection()->map(function ($order) {
                if ($order->payment_status !== FoodOrder::PAYMENT_STATUS_PAID) {
                    $order->foodVendor->makeHidden(['contact_phone', 'contact_email']);
                }
                return $order;
            })
        );

        return response()->json($orders);
    }

    /**
     * Get food order detail for a user.
     */
    public function show($id)
    {
        $order = FoodOrder::with([
            'items.menuItem',
            'foodVendor',
            'vendor.user:id,name,email',
            'rider.user:id,name,phone',
            'user:id,name,email'
        ])
        ->where('user_id', Auth::id())
        ->findOrFail($id);

        if ($order->payment_status !== FoodOrder::PAYMENT_STATUS_PAID) {
            $order->foodVendor->makeHidden(['contact_phone', 'contact_email']);
            if ($order->rider) {
                $order->rider->user->makeHidden(['phone']);
            }
        }

        return response()->json(['order' => $order]);
    }

    /**
     * Vendor views their own food orders.
     */
    public function vendorOrders(Request $request)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Not authorized as vendor'], 403);
        }

        $query = FoodOrder::with(['items.menuItem', 'user:id,name,email'])
            ->where('vendor_id', $vendor->id);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $orders = $query->latest()->paginate(20);
        return response()->json(['orders' => $orders]);
    }

    /**
     * Vendor accepts an order (user can now proceed to payment).
     */
    public function acceptOrder($id)
    {
        $vendor = Auth::user()->vendor;
        $order = FoodOrder::where('vendor_id', $vendor->id)->findOrFail($id);

        if ($order->status !== FoodOrder::STATUS_AWAITING_VENDOR) {
            return response()->json(['error' => 'Order cannot be accepted at this stage'], 400);
        }

        $order->update(['status' => FoodOrder::STATUS_ACCEPTED]);

        return response()->json([
            'message' => 'Order accepted. User can now proceed to payment.',
            'order' => $order
        ]);
    }

    /**
     * Vendor rejects an order before payment.
     */
    public function rejectOrder($id)
    {
        $vendor = Auth::user()->vendor;
        $order = FoodOrder::where('vendor_id', $vendor->id)->findOrFail($id);

        if (!in_array($order->status, [FoodOrder::STATUS_AWAITING_VENDOR, FoodOrder::STATUS_ACCEPTED])) {
            return response()->json(['error' => 'Order cannot be rejected at this stage'], 400);
        }

        $order->update([
            'status' => FoodOrder::STATUS_CANCELLED,
            'payment_status' => FoodOrder::PAYMENT_STATUS_REFUNDED,
        ]);

        return response()->json(['message' => 'Order rejected successfully']);
    }

    /**
     * Mark order as completed and release escrow.
     */
    public function complete($id)
    {
        $order = FoodOrder::where('user_id', Auth::id())->findOrFail($id);

        if ($order->status !== FoodOrder::STATUS_DELIVERED) {
            return response()->json(['error' => 'Order must be delivered before completion'], 422);
        }

        if ($order->payment_status !== FoodOrder::PAYMENT_STATUS_PAID) {
            return response()->json(['error' => 'Order payment not confirmed'], 422);
        }

        DB::beginTransaction();
        try {
            $adminWallet = AdminWallet::firstOrCreate(
                ['name' => 'Main'],
                ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
            );

            $total = (float) $order->total;
            $commission = (float) $order->commission_amount;
            $deliveryFee = (float) $order->delivery_fee;
            $tipAmount = (float) $order->tip_amount;
            $vendorShare = $total - $commission - $deliveryFee - $tipAmount;

            if ($adminWallet->balance < ($vendorShare + $deliveryFee + $tipAmount)) {
                DB::rollBack();
                return response()->json(['error' => 'Escrow insufficient for payout'], 500);
            }

            // ✅ Debit admin wallet for vendor payout
            $adminWallet->decrement('balance', $vendorShare);

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type' => 'debit',
                'amount' => $vendorShare,
                'ref' => 'food_payout_' . $order->id,
                'status' => 'success',
                'meta' => ['order_id' => $order->id, 'type' => 'vendor_payout'],
            ]);

            // ✅ Credit vendor wallet
            $vendorWallet = Wallet::firstOrCreate(
                ['user_id' => $order->vendor->user_id],
                ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
            );

            $vendorWallet->increment('balance', $vendorShare);

            WalletTransaction::create([
                'wallet_id' => $vendorWallet->id,
                'type' => 'credit',
                'amount' => $vendorShare,
                'ref' => 'food_payout_' . $order->id,
                'status' => 'success',
            ]);

            // ✅ Rider payout (if applicable)
            if ($order->rider_id && ($deliveryFee > 0 || $tipAmount > 0)) {
                $riderPayout = $deliveryFee + $tipAmount;

                $adminWallet->decrement('balance', $riderPayout);

                AdminTransaction::create([
                    'admin_wallet_id' => $adminWallet->id,
                    'type' => 'debit',
                    'amount' => $riderPayout,
                    'ref' => 'rider_payout_' . $order->id,
                    'status' => 'success',
                    'meta' => ['order_id' => $order->id, 'type' => 'rider_payout'],
                ]);

                $riderWallet = Wallet::firstOrCreate(
                    ['user_id' => $order->rider->user_id],
                    ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
                );

                $riderWallet->increment('balance', $riderPayout);

                WalletTransaction::create([
                    'wallet_id' => $riderWallet->id,
                    'type' => 'credit',
                    'amount' => $riderPayout,
                    'ref' => 'rider_payout_' . $order->id,
                    'status' => 'success',
                ]);
            }

            $order->update(['status' => FoodOrder::STATUS_COMPLETED]);

            DB::commit();
            return response()->json([
                'message' => 'Order completed and payment released',
                'order' => $order->fresh()
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Food order completion failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to complete order'], 500);
        }
    }

    /**
     * Cancel a food order.
     */
    public function cancel(Request $request, $id)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $order = FoodOrder::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($order->status, [
            FoodOrder::STATUS_PENDING_PAYMENT,
            FoodOrder::STATUS_AWAITING_VENDOR,
            FoodOrder::STATUS_ACCEPTED
        ])) {
            return response()->json(['error' => 'Order cannot be cancelled at this stage'], 422);
        }

        DB::beginTransaction();
        try {
            if ($order->payment_status === FoodOrder::PAYMENT_STATUS_PAID) {
                $adminWallet = AdminWallet::where('name', 'Main')->firstOrFail();
                $refundAmount = (float) $order->total;

                if ($adminWallet->balance < $refundAmount) {
                    DB::rollBack();
                    return response()->json(['error' => 'Escrow insufficient for refund'], 500);
                }

                $adminWallet->decrement('balance', $refundAmount);

                AdminTransaction::create([
                    'admin_wallet_id' => $adminWallet->id,
                    'type' => 'debit',
                    'amount' => $refundAmount,
                    'ref' => 'food_refund_' . $order->id,
                    'status' => 'success',
                    'meta' => ['order_id' => $order->id, 'reason' => $request->reason],
                ]);

                $order->payment_status = FoodOrder::PAYMENT_STATUS_REFUNDED;
            }

            $order->update(['status' => FoodOrder::STATUS_CANCELLED]);

            DB::commit();
            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->fresh()
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Food order cancellation failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);

            return response()->json(['error' => 'Failed to cancel order'], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return round($earthRadius * $c, 2);
    }
}
