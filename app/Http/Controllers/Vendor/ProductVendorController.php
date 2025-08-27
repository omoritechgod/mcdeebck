<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductUpdateRequest;
use App\Http\Requests\ProductValidationRequest;
use App\Http\Resources\ProductResource;
use App\Models\ProductColor;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

/**
 * @group Vendor - Product Vendor
 *
 * API for managing product vendor setup
 */
class ProductVendorController extends Controller
{

    public function index()
    {
        $products = Product::with(['vendor', 'category', 'reviews'])->latest()->get();

        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'success',
                'message' => 'No products found'
            ], 200);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully',
            'products' => ProductResource::collection($products),
        ], 200);
    }

    public function store(ProductValidationRequest $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();

        if (!$vendor || trim(strtolower($vendor->category)) !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }



        $product = new Product();


        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->condition = $request->condition;
        if ($request->filled('image')) {
            $product->image = $request->image;
        }

        $product->category_id = $request->category_id;
        $product->vendor_id = $vendor->id;
        $product->save();

        if ($request->has('color') && $request->filled('color')) {
            foreach ($request->color as $color) {
                ProductColor::create([
                    'color' => $color,
                    'product_id' => $product->id
                ]);
            }
        }
        // Save additional images
        if ($request->filled('images')) {
            foreach ($request->images as $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imageUrl
                ]);
            }
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'product created successfully'
        ], 200);
    }

    public function show(string $id)
    {

        $products = Product::with('vendor', 'category', 'reviews')->findOrFail($id);
        if ($products->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found',
            ], 404);
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Product retrieved successfully',
            'product' => $products,
        ], 200);
    }

    public function update(ProductUpdateRequest $request, string $id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();

        if (!$vendor || trim(strtolower($vendor->category)) !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $product = Product::findOrFail($id);

        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->stock = $request->stock;
        $product->condition = $request->condition;
        $product->product_category_id = $request->product_category_id;
       

        if ($request->filled('image')) {
            $product->image = $request->image;
        }

        $product->vendor_id = $request->vendor_id;

        if ($request->has('color')  && $request->filled('color')) {
            foreach ($product->color as $color) {
                $color->delete();
            }
            foreach ($product->color as $color) {
                ProductColor::create([
                    'color' => $color,
                    'product_id' => $product->id
                ]);
            }
        }

        if ($request->filled('images')) {
            $product->images()->delete();

            foreach ($request->images as $imageUrl) {
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $imageUrl
                ]);
            }
        }

        return response()->json([
            'status' => 'Ok',
            'message' => 'Product Updated Successfully'
        ], 200);
    }

    public function destroy(string $id)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();

        if (!$vendor || trim(strtolower($vendor->category)) !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $product = Product::findOrFail($id);

        if (!$product) {
            return response()->json(['message' => 'Product Not Found']);
        }
        $product->delete();

        return response()->json([
            'status' => 'Ok',
            'message' => 'Product Deleted'
        ], 200);
    }
}
