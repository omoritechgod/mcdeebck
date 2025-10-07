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
 * @OA\Tag(name="Food Orders", description="Food Order APIs for customers")
 */
class FoodOrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/food/orders",
     *     summary="Create a food order",
     *     tags={"Food Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vendor_id", "items", "delivery_method", "shipping_address"},
     *             @OA\Property(property="vendor_id", type="integer", example=1),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="menu_id", type="integer", example=5),
     *                     @OA\Property(property="quantity", type="integer", example=2)
     *                 )
     *             ),
     *             @OA\Property(property="delivery_method", type="string", enum={"delivery", "pickup", "offline_rider"}, example="delivery"),
     *             @OA\Property(
     *                 property="shipping_address",
     *                 type="object",
     *                 @OA\Property(property="address", type="string", example="123 Main St"),
     *                 @OA\Property(property="city", type="string", example="Lagos"),
     *                 @OA\Property(property="phone", type="string", example="08012345678"),
     *                 @OA\Property(property="latitude", type="number", example=6.5244),
     *                 @OA\Property(property="longitude", type="number", example=3.3792)
     *             ),
     *             @OA\Property(property="tip_amount", type="number", example=500)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order created successfully")
     * )
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

        if ($subtotal < $foodVendor->minimum_order_amount) {
            return response()->json([
                'error' => "Minimum order amount is NGN {$foodVendor->minimum_order_amount}",
                'minimum' => $foodVendor->minimum_order_amount,
                'current' => $subtotal,
            ], 400);
        }

        $deliveryFee = 0;
        if ($request->delivery_method === 'delivery') {
            if ($request->has('shipping_address.latitude') && $request->has('shipping_address.longitude')) {
                $distance = $this->calculateDistance(
                    (float) $foodVendor->latitude,
                    (float) $foodVendor->longitude,
                    (float) $request->input('shipping_address.latitude'),
                    (float) $request->input('shipping_address.longitude')
                );

                if ($distance > $foodVendor->delivery_radius_km) {
                    return response()->json([
                        'error' => 'Delivery address is outside vendor delivery radius',
                        'distance_km' => $distance,
                        'max_delivery_radius_km' => $foodVendor->delivery_radius_km,
                    ], 400);
                }
            }

            $deliveryFee = $foodVendor->delivery_fee;
        }

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
                'status' => FoodOrder::STATUS_PENDING_PAYMENT,
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
                'message' => 'Order created successfully',
                'order' => $order->load('items.menuItem', 'foodVendor'),
                'payment_required' => true,
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Food order creation failed', [
                'user_id' => $user->id,
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
     * @OA\Get(
     *     path="/api/food/orders",
     *     summary="List user's food orders",
     *     tags={"Food Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Success")
     * )
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

        $orders->getCollection()->transform(function ($order) {
            if ($order->payment_status !== FoodOrder::PAYMENT_STATUS_PAID) {
                $order->foodVendor->makeHidden(['contact_phone', 'contact_email']);
            }
            return $order;
        });

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/food/orders/{id}",
     *     summary="Get food order detail",
     *     tags={"Food Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success")
     * )
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

        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/food/orders/{id}/complete",
     *     summary="Mark order as completed and release escrow",
     *     tags={"Food Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order completed and payment released")
     * )
     */
    public function complete($id)
    {
        $order = FoodOrder::where('user_id', Auth::id())->findOrFail($id);

        if ($order->status !== FoodOrder::STATUS_DELIVERED) {
            return response()->json([
                'error' => 'Order must be delivered before completion'
            ], 422);
        }

        if ($order->payment_status !== FoodOrder::PAYMENT_STATUS_PAID) {
            return response()->json([
                'error' => 'Order payment not confirmed'
            ], 422);
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

            $adminWallet->balance -= $vendorShare;
            $adminWallet->save();

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type' => 'debit',
                'amount' => $vendorShare,
                'ref' => 'food_payout_' . $order->id,
                'status' => 'success',
                'meta' => ['order_id' => $order->id, 'type' => 'vendor_payout'],
            ]);

            $vendorWallet = Wallet::firstOrCreate(
                ['user_id' => $order->vendor->user_id],
                ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
            );

            $vendorWallet->balance += $vendorShare;
            $vendorWallet->save();

            WalletTransaction::create([
                'wallet_id' => $vendorWallet->id,
                'type' => 'credit',
                'amount' => $vendorShare,
                'ref' => 'food_payout_' . $order->id,
                'status' => 'success',
            ]);

            if ($order->rider_id && ($deliveryFee > 0 || $tipAmount > 0)) {
                $riderPayout = $deliveryFee + $tipAmount;

                $adminWallet->balance -= $riderPayout;
                $adminWallet->save();

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

                $riderWallet->balance += $riderPayout;
                $riderWallet->save();

                WalletTransaction::create([
                    'wallet_id' => $riderWallet->id,
                    'type' => 'credit',
                    'amount' => $riderPayout,
                    'ref' => 'rider_payout_' . $order->id,
                    'status' => 'success',
                ]);
            }

            $order->status = FoodOrder::STATUS_COMPLETED;
            $order->save();

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

            return response()->json([
                'error' => 'Failed to complete order'
            ], 500);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/food/orders/{id}/cancel",
     *     summary="Cancel food order",
     *     tags={"Food Orders"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="reason", type="string", example="Changed my mind")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order cancelled")
     * )
     */
    public function cancel(Request $request, $id)
    {
        $request->validate([
            'reason' => 'nullable|string|max:500'
        ]);

        $order = FoodOrder::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($order->status, [
            FoodOrder::STATUS_PENDING_PAYMENT,
            FoodOrder::STATUS_AWAITING_VENDOR,
            FoodOrder::STATUS_ACCEPTED
        ])) {
            return response()->json([
                'error' => 'Order cannot be cancelled at this stage'
            ], 422);
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

                $adminWallet->balance -= $refundAmount;
                $adminWallet->save();

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

            $order->status = FoodOrder::STATUS_CANCELLED;
            $order->save();

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

            return response()->json([
                'error' => 'Failed to cancel order'
            ], 500);
        }
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);

        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        $distance = $earthRadius * $c;

        return round($distance, 2);
    }
}
