<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * List products belonging to authenticated vendor.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor ?? Vendor::where('user_id', $user->id)->first();

        if (! $vendor) {
            return response()->json(['message' => 'Vendor account not found.'], 404);
        }

        $query = Product::where('vendor_id', $vendor->id)
                        ->with('category')
                        ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->paginate(20);

        return response()->json($products);
    }

    /**
     * Store new product for the vendor.
     * Expects JSON with image URLs (Cloudinary links).
     */
    public function store(Request $request)
    {
        $user = $request->user();
        $vendor = $user->vendor ?? Vendor::where('user_id', $user->id)->first();

        if (! $vendor) {
            return response()->json(['message' => 'Vendor account not found.'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock_quantity' => 'required|integer|min:0',
            'category_id' => ['nullable', 'exists:categories,id'],
            'condition' => ['required', Rule::in(['new', 'used'])],
            'allow_pickup' => 'sometimes|boolean',
            'allow_shipping' => 'sometimes|boolean',
            'images' => 'nullable|array',
            'images.*' => 'url'
        ]);

        // determine status: make active only if vendor is live
        $isLive = ($vendor->is_verified && $user->phone_verified_at);
        $status = $isLive ? 'active' : 'draft';

        $product = Product::create([
            'vendor_id' => $vendor->id,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'images' => $validated['images'] ?? [],
            'price' => $validated['price'],
            'stock_quantity' => $validated['stock_quantity'],
            'category_id' => $validated['category_id'] ?? null,
            'condition' => $validated['condition'],
            'allow_pickup' => $request->boolean('allow_pickup'),
            'allow_shipping' => $request->boolean('allow_shipping'),
            'status' => $status,
        ]);

        return response()->json($product->load('category'), 201);
    }

    /**
     * Show a specific product for the vendor.
     */
    public function show(Request $request, Product $product)
    {
        $user = $request->user();
        $vendor = $user->vendor ?? Vendor::where('user_id', $user->id)->first();

        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($product->load('category'));
    }

    /**
     * Update vendor's product.
     * Expects JSON with image URLs.
     */
    public function update(Request $request, Product $product)
    {
        $user = $request->user();
        $vendor = $user->vendor ?? Vendor::where('user_id', $user->id)->first();

        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock_quantity' => 'sometimes|integer|min:0',
            'category_id' => ['nullable', 'exists:categories,id'],
            'condition' => [Rule::in(['new', 'used'])],
            'allow_pickup' => 'sometimes|boolean',
            'allow_shipping' => 'sometimes|boolean',
            'images' => 'nullable|array',
            'images.*' => 'url',
        ]);

        DB::transaction(function () use ($request, $product, $validated) {
            $product->fill($validated);
            $product->allow_pickup = $request->boolean('allow_pickup', $product->allow_pickup);
            $product->allow_shipping = $request->boolean('allow_shipping', $product->allow_shipping);

            // overwrite images if provided
            if (isset($validated['images'])) {
                $product->images = $validated['images'];
            }

            $product->save();
        });

        return response()->json($product->fresh()->load('category'));
    }

    /**
     * Delete product.
     */
    public function destroy(Request $request, Product $product)
    {
        $user = $request->user();
        $vendor = $user->vendor ?? Vendor::where('user_id', $user->id)->first();

        if (!$vendor || $product->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product->delete();

        return response()->json(['message' => 'Product deleted.']);
    }
}
