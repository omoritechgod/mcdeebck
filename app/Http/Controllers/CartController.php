<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Cart",
 *     description="Cart endpoints"
 * )
 */
class CartController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/cart",
     *     summary="Get current user's cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Cart retrieved successfully")
     * )
     */
    public function index()
    {
        $cartItems = Cart::where('user_id', Auth::id())->get();
        return response()->json($cartItems);
    }

    /**
     * @OA\Post(
     *     path="/api/cart/add",
     *     summary="Add item to cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"item_type", "item_id", "quantity"},
     *             @OA\Property(property="item_type", type="string", example="product"),
     *             @OA\Property(property="item_id", type="integer"),
     *             @OA\Property(property="quantity", type="integer", minimum=1)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Item added to cart")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_type' => 'required|string',
            'item_id' => 'required|integer',
            'quantity' => 'required|integer|min:1',
        ]);

        $cart = Cart::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'item_type' => $request->item_type,
                'item_id' => $request->item_id,
            ],
            ['quantity' => $request->quantity]
        );

        return response()->json(['message' => 'Item added to cart', 'cart' => $cart]);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/remove/{item_id}",
     *     summary="Remove item from cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(name="item_id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Item removed from cart")
     * )
     */
    public function destroy($itemId)
    {
        $deleted = Cart::where('user_id', Auth::id())
            ->where('item_id', $itemId)
            ->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }

    /**
     * @OA\Delete(
     *     path="/api/cart/clear",
     *     summary="Clear entire cart",
     *     tags={"Cart"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Cart cleared")
     * )
     */
    public function clear()
    {
        Cart::where('user_id', Auth::id())->delete();
        return response()->json(['message' => 'Cart cleared']);
    }
}
