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
        $vendor = Auth::user();

        if (!$vendor || $vendor->category !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $product = new Product();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $fileName = $image->store('', 'public');
            $filePath = 'uploads/' . $fileName;
            $product->image = $filePath;
        }
        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->condition = $request->condition;
        $product->status = $request->status ?? 'active';

        $product->product_category_id = $request->product_category_id;
        $product->vendor_id = $vendor->id;
        $product->vendor_category_id = $vendor->vendor_category_id;
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
        if ($request->hasFile('images')) {
            foreach ($request->images as $image) {
                $fileName = $image->store('', 'public');
                $filePath = 'uploads/products/' . $fileName;
                ProductImage::create([
                    'image_path' => $filePath,
                    'product_id' => $product->id
                ]);
            }
        }
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
        $vendor = Auth::user();
        if (!$vendor || $vendor->category !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $product = Product::findOrFail($id);


        if ($request->hasFile('image')) {
            File::delete(public_path('storage/' . $product->image));
            $image = $request->file('image');
            $fileName = $image->store('', 'public');
            $filePath = 'uploads/' . $fileName;

            $product->image = $filePath;
        };

        $product->name = $request->name;
        $product->description = $request->description;
        $product->price = $request->price;
        $product->product_category_id = $request->product_category_id;
        $product->condition = $request->condition;
        $product->status = $request->status ?? 'active';
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

        if ($request->hasFile('images')) {
            foreach ($product->images as $image) {
                File::delete(public_path($image->path));
            }
            $product->images()->delete();

            foreach ($request->images as $image) {
                $fileName = $image->store('', 'public');
                $filePath = "uploads/" . $fileName;
                ProductImage::create([
                    'product_id' => $product->id,
                    'Image_path' => $filePath,
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
        $vendor = Auth::user();
        if (!$vendor || $vendor->category !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $product = Product::findOrFail($id);

        if ($product->isEmpty()) {
            return response()->json(['message' => 'Product Not Found']);
        }
        $product->colors()->delete();

        if ($product->image && file_exists(public_path('storage/' . $product->image))) {
            File::delete(public_path('storage/' . $product->image));
        }
        foreach ($product->images as $image) {
            if ($image->image_path && file_exists(public_path('storage/' . $image->image_path))) {
                File::delete(public_path('storage/' . $image->image_path));
            }
        }
        $product->images()->delete();
        $product->delete();

        return response()->json([
            'status' => 'Ok',
            'message' => 'Product Deleted'
        ], 200);
    }
}
