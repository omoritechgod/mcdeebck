<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductVendor;
use Illuminate\Support\Facades\Auth;

/**
 * @group Vendor - Product Vendor
 *
 * API for managing product vendor setup
 */
class ProductVendorController extends Controller
{
    /**
     * Complete Product Vendor Setup
     *
     * @OA\Post(
     *     path="/api/vendor/product-vendor/setup",
     *     summary="Complete product vendor setup",
     *     tags={"Vendor - Product Vendor"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"contact_person", "store_address", "store_phone"},
     *             @OA\Property(property="contact_person", type="string", example="John Doe"),
     *             @OA\Property(property="store_address", type="string", example="123 Main Street, Ikeja"),
     *             @OA\Property(property="store_phone", type="string", example="08012345678"),
     *             @OA\Property(property="store_email", type="string", format="email", example="store@example.com"),
     *             @OA\Property(property="store_description", type="string", example="We sell school bags and lunch boxes"),
     *             @OA\Property(property="logo", type="string", example="https://example.com/logo.png")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product vendor setup complete",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Product vendor setup complete"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="vendor_id", type="integer", example=5),
     *                 @OA\Property(property="contact_person", type="string", example="John Doe"),
     *                 @OA\Property(property="store_address", type="string", example="123 Main Street, Ikeja"),
     *                 @OA\Property(property="store_phone", type="string", example="08012345678"),
     *                 @OA\Property(property="store_email", type="string", example="store@example.com"),
     *                 @OA\Property(property="store_description", type="string", example="We sell school bags and lunch boxes"),
     *                 @OA\Property(property="logo", type="string", example="https://example.com/logo.png")
     *             )
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'contact_person'    => 'required|string',
            'store_address'     => 'required|string',
            'store_phone'       => 'required|string',
            'store_email'       => 'nullable|email',
            'store_description' => 'nullable|string',
            'logo'              => 'nullable|string', // or change to 'image' if handling file uploads
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'product_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $productVendor = ProductVendor::updateOrCreate(
            ['vendor_id' => $vendor->id],
            $request->only([
                'contact_person',
                'store_address',
                'store_phone',
                'store_email',
                'store_description',
                'logo',
            ])
        );

        $vendor->update(['is_setup_complete' => true]);

        return response()->json([
            'message' => 'Product vendor setup complete',
            'data'    => $productVendor
        ], 201);
    }
}
