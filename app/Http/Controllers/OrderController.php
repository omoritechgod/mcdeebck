<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class OrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Place a new order",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"items"},
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="product_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order placed successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $total = 0;
        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);
            $total += $product->price * $item['quantity'];
        }

        $order = Order::create([
            'user_id' => Auth::id(),
            'total' => $total,
            'status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $product = Product::find($item['product_id']);

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $product->id,
                'quantity' => $item['quantity'],
                'price' => $product->price,
            ]);

            $product->decrement('stock', $item['quantity']);
        }

        return response()->json(['message' => 'Order placed', 'order' => $order]);
    }

    /**
     * @OA\Get(
     *     path="/api/orders",
     *     tags={"Orders"},
     *     summary="Get current user's orders",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of user's orders")
     * )
     */
    public function userOrders()
    {
        $orders = Order::where('user_id', Auth::id())
                       ->with('items.product')
                       ->latest()->get();

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/orders/vendor",
     *     tags={"Orders"},
     *     summary="Get orders for vendor's products",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of vendor's orders")
     * )
     */
    public function vendorOrders()
    {
        $vendorId = Auth::user()->vendor->id;

        $orders = Order::whereHas('items.product', function ($query) use ($vendorId) {
            $query->where('vendor_id', $vendorId);
        })->with('items.product')->latest()->get();

        return response()->json($orders);
    }

    /**
     * @OA\Put(
     *     path="/api/orders/{id}/status",
     *     tags={"Orders"},
     *     summary="Update order status (e.g., processing, completed)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"processing", "completed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated"),
     *     @OA\Response(response=403, description="Unauthorized or not found")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:processing,completed,cancelled',
        ]);

        $order = Order::findOrFail($id);
        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated']);
    }
}
