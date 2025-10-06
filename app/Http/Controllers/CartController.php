<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\Product;

class CartController extends Controller
{
    /**
     * Display the user's cart.
     */
    public function index(Request $request)
    {
        $cartItems = Cart::where('user_id', $request->user()->id)
            ->with('product.category')
            ->get();

        return response()->json($cartItems);
    }

    /**
     * Add product to cart or update quantity if already exists.
     * Expects JSON:
     * {
     *   "product_id": 12,
     *   "quantity": 2
     * }
     */
    public function add(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $userId = $request->user()->id;
        $product = Product::findOrFail($validated['product_id']);

        // Check stock
        if ($validated['quantity'] > $product->stock_quantity) {
            return response()->json([
                'message' => "Insufficient stock for {$product->title}"
            ], 422);
        }

        // Check if item already exists in cart
        $cartItem = Cart::where('user_id', $userId)
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($cartItem) {
            // Update quantity
            $cartItem->quantity = $validated['quantity'];
            $cartItem->save();
        } else {
            // Create new cart entry
            $cartItem = Cart::create([
                'user_id' => $userId,
                'product_id' => $validated['product_id'],
                'quantity' => $validated['quantity'],
            ]);
        }

        return response()->json($cartItem->load('product.category'), 201);
    }

    /**
     * Remove an item from the cart.
     */
    public function destroy(Request $request, Cart $cart)
    {
        if ($cart->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $cart->delete();

        return response()->json(['message' => 'Item removed from cart']);
    }
}
