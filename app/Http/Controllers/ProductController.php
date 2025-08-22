<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Products",
 *     description="Product listing, viewing, creating, updating, and deleting"
 * )
 */
class ProductController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Get all products",
     *     @OA\Response(response=200, description="List of products")
     * )
     */
    public function index()
    {
        $products = Product::with(['vendor', 'category', 'reviews'])->latest()->get();
        return response()->json($products);
    }

    /**
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Get a single product by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Product details"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function show($id)
    {
        $product = Product::with(['vendor', 'category', 'reviews'])->findOrFail($id);
        return response()->json($product);
    }

    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Create a product (vendor only)",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "stock"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="category_id", type="integer", nullable=true),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product created"),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:1',
            'stock' => 'required|integer|min:0',
            'category_id' => 'nullable|exists:product_categories,id',
            'description' => 'nullable|string',
        ]);

        $vendor = Auth::user()->vendor;

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'price' => $request->price,
            'stock' => $request->stock,
            'category_id' => $request->category_id,
            'description' => $request->description,
        ]);

        return response()->json(['message' => 'Product created', 'product' => $product]);
    }

    /**
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Update a product (vendor only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="stock", type="integer"),
     *             @OA\Property(property="category_id", type="integer", nullable=true),
     *             @OA\Property(property="description", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Product updated"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        if ($product->vendor_id !== Auth::user()->vendor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->update($request->only(['name', 'price', 'stock', 'description', 'category_id']));

        return response()->json(['message' => 'Product updated', 'product' => $product]);
    }

    /**
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"Products"},
     *     summary="Delete a product (vendor only)",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Product deleted"),
     *     @OA\Response(response=403, description="Unauthorized"),
     *     @OA\Response(response=404, description="Product not found")
     * )
     */
    public function destroy($id)
    {
        $product = Product::findOrFail($id);

        if ($product->vendor_id !== Auth::user()->vendor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }
}
