<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductReviewController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/products/{id}/review",
     *     tags={"Reviews"},
     *     summary="Submit a review for a product",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id", in="path", required=true, description="Product ID",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rating"},
     *             @OA\Property(property="rating", type="integer", minimum=1, maximum=5),
     *             @OA\Property(property="comment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Review submitted"),
     *     @OA\Response(response=404, description="Product not found"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function store(Request $request, $productId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:500',
        ]);

        $product = Product::findOrFail($productId);

        $review = ProductReview::create([
            'user_id' => Auth::id(),
            'product_id' => $product->id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review submitted',
            'review' => $review,
        ]);
    }
}
