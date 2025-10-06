<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Cart;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use App\Models\AdminWallet;
use App\Models\AdminTransaction;

class OrderController extends Controller
{
    /**
     * User's orders list
     */
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with(['items.product', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * View one order
     */
    public function show(Request $request, Order $order)
    {
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($order->load(['items.product', 'vendor']));
    }

    /**
     * Checkout user cart → creates one order per vendor.
     */
    public function checkoutFromCart(Request $request)
    {
        $validated = $request->validate([
            'delivery_address' => 'nullable|string|max:255',
            'delivery_method'  => 'nullable|string|max:50',
        ]);

        $user = $request->user();
        $cartItems = Cart::where('user_id', $user->id)->with('product')->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $grouped = $cartItems->groupBy(fn($item) => $item->product->vendor_id);

        $orders = [];

        DB::transaction(function () use ($grouped, $user, $validated, &$orders) {
            foreach ($grouped as $vendorId => $items) {
                $total = 0;
                $orderItemsData = [];

                foreach ($items as $cartItem) {
                    $product = $cartItem->product;

                    if ($cartItem->quantity > $product->stock_quantity) {
                        throw new \Exception("Insufficient stock for product {$product->title}");
                    }

                    $price = $product->price;
                    $total += $price * $cartItem->quantity;

                    $orderItemsData[] = [
                        'product_id' => $product->id,
                        'quantity'   => $cartItem->quantity,
                        'price'      => $price,
                    ];
                }

                $order = Order::create([
                    'user_id'          => $user->id,
                    'vendor_id'        => $vendorId,
                    'total_amount'     => $total,
                    'status'           => 'pending_vendor',
                    'delivery_address' => $validated['delivery_address'] ?? null,
                    'delivery_method'  => $validated['delivery_method'] ?? null,
                ]);

                foreach ($orderItemsData as $data) {
                    $data['order_id'] = $order->id;
                    OrderItem::create($data);
                }

                $orders[] = $order->load(['items.product', 'vendor']);
            }

            Cart::where('user_id', $user->id)->delete();
        });

        return response()->json([
            'message' => 'Checkout complete. Orders created.',
            'orders'  => $orders,
        ], 201);
    }

    /**
     * Mark order as completed → release escrow to vendor.
     */
    public function markCompleted(Request $request, Order $order)
    {
        $user = $request->user();

        // Only the order owner or admin can complete
        if ($order->user_id !== $user->id && !$user->is_admin) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($order->status !== 'shipped') {
            return response()->json(['message' => 'Order not yet delivered'], 422);
        }

        DB::transaction(function () use ($order) {
            $order->status = 'completed';
            $order->save();

            $commissionRate = config('commissions.ecommerce', 5) / 100;
            $companyCut = round($commissionRate * (float)$order->total_amount, 2);
            $vendorShare = (float)$order->total_amount - $companyCut;

            // Admin commission
            $adminWallet = AdminWallet::firstOrCreate(
                ['name' => 'Main'],
                ['balance' => 0.00, 'currency' => 'NGN']
            );
            $adminWallet->increment('balance', $companyCut);

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type'            => 'credit',
                'amount'          => $companyCut,
                'ref'             => 'order_'.$order->id.'_commission',
                'status'          => 'success',
                'meta'            => ['entity' => 'ecommerce_order', 'entity_id' => $order->id],
            ]);

            // Vendor payout
            $vendorWallet = Wallet::firstOrCreate(
                ['user_id' => $order->vendor->user_id],
                ['balance' => 0.00, 'currency' => 'NGN']
            );
            $vendorWallet->increment('balance', $vendorShare);

            WalletTransaction::create([
                'wallet_id' => $vendorWallet->id,
                'type'     => 'credit',
                'amount'   => $vendorShare,
                'ref'      => 'order_'.$order->id.'_payout',
                'status'   => 'success',
            ]);
        });

        return response()->json(['message' => 'Order completed and funds released']);
    }
}
