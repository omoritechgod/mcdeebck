<?php

namespace App\Http\Controllers;

use App\Models\FoodMenu;
use App\Models\FoodOrder;
use App\Models\FoodOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Food", description="Food Order APIs")
 */
class FoodOrderController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/food/orders",
     *     summary="Place a food order",
     *     tags={"Food"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="vendor_id", type="integer"),
     *             @OA\Property(
     *                 property="items",
     *                 type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="menu_id", type="integer"),
     *                     @OA\Property(property="quantity", type="integer")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(response=201, description="Order placed")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|integer|exists:vendors,id',
            'items' => 'required|array|min:1',
            'items.*.menu_id' => 'required|integer|exists:food_menus,id',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $total = 0;

        foreach ($request->items as $item) {
            $menu = FoodMenu::find($item['menu_id']);
            $total += $menu->price * $item['quantity'];
        }

        $order = FoodOrder::create([
            'user_id' => Auth::id(),
            'vendor_id' => $request->vendor_id,
            'total' => $total,
        ]);

        foreach ($request->items as $item) {
            $menu = FoodMenu::find($item['menu_id']);
            FoodOrderItem::create([
                'food_order_id' => $order->id,
                'food_menu_id' => $menu->id,
                'quantity' => $item['quantity'],
                'price' => $menu->price,
            ]);
        }

        return response()->json(['message' => 'Order placed', 'order_id' => $order->id], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/food/orders",
     *     summary="Get user's food orders",
     *     tags={"Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index()
    {
        $orders = FoodOrder::with('items')->where('user_id', Auth::id())->get();
        return response()->json($orders);
    }

    /**
     * @OA\Put(
     *     path="/api/food/orders/{id}/status",
     *     summary="Update food order status (Vendor only)",
     *     tags={"Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","preparing","delivered","cancelled"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order status updated")
     * )
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,preparing,delivered,cancelled',
        ]);

        $order = FoodOrder::findOrFail($id);

        // Optional: check vendor ownership
        if (Auth::user()->vendor->id !== $order->vendor_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->status = $request->status;
        $order->save();

        return response()->json(['message' => 'Order status updated']);
    }
}
