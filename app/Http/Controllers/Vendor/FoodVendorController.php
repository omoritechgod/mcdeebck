<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FoodVendor;

/**
 * @OA\Tag(name="Food Vendor Setup", description="APIs for food vendors to manage their profile")
 */
class FoodVendorController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/vendor/food/setup",
     *     summary="Complete food vendor profile setup",
     *     tags={"Food Vendor Setup"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"business_name", "specialty", "location", "contact_phone"},
     *             @OA\Property(property="business_name", type="string", example="Mama Blessing Kitchen"),
     *             @OA\Property(property="specialty", type="string", example="Nigerian delicacies"),
     *             @OA\Property(property="cuisines", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="location", type="string", example="Wuse 2, Abuja"),
     *             @OA\Property(property="latitude", type="number", example=9.0579),
     *             @OA\Property(property="longitude", type="number", example=7.4951),
     *             @OA\Property(property="contact_phone", type="string", example="08012345678"),
     *             @OA\Property(property="contact_email", type="string", example="mama@example.com"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="logo", type="string"),
     *             @OA\Property(property="estimated_preparation_time", type="integer", example=30),
     *             @OA\Property(property="operating_hours", type="object"),
     *             @OA\Property(property="delivery_radius_km", type="number", example=10.5),
     *             @OA\Property(property="minimum_order_amount", type="number", example=1000),
     *             @OA\Property(property="delivery_fee", type="number", example=500),
     *             @OA\Property(property="accepts_cash", type="boolean", example=true),
     *             @OA\Property(property="accepts_card", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Food vendor profile completed")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:255',
            'specialty' => 'required|string|max:255',
            'cuisines' => 'nullable|array',
            'cuisines.*' => 'string',
            'location' => 'required|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_phone' => 'required|string|max:20',
            'contact_email' => 'nullable|email',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'estimated_preparation_time' => 'nullable|integer|min:1',
            'operating_hours' => 'nullable|array',
            'delivery_radius_km' => 'nullable|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'accepts_cash' => 'nullable|boolean',
            'accepts_card' => 'nullable|boolean',
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $data = $request->only([
            'business_name', 'specialty', 'cuisines', 'location',
            'latitude', 'longitude', 'contact_phone', 'contact_email',
            'description', 'logo', 'estimated_preparation_time',
            'operating_hours', 'delivery_radius_km', 'minimum_order_amount',
            'delivery_fee', 'accepts_cash', 'accepts_card'
        ]);

        $data['cuisines'] = $request->input('cuisines', []);
        $data['operating_hours'] = $request->input('operating_hours', []);
        $data['estimated_preparation_time'] = $request->input('estimated_preparation_time', 30);
        $data['delivery_radius_km'] = $request->input('delivery_radius_km', 5);
        $data['minimum_order_amount'] = $request->input('minimum_order_amount', 0);
        $data['delivery_fee'] = $request->input('delivery_fee', 0);
        $data['accepts_cash'] = $request->input('accepts_cash', true);
        $data['accepts_card'] = $request->input('accepts_card', true);
        $data['is_open'] = true;

        $foodVendor = FoodVendor::updateOrCreate(
            ['vendor_id' => $vendor->id],
            $data
        );

        $vendor->update(['is_setup_complete' => true]);

        return response()->json([
            'message' => 'Food vendor profile completed',
            'data' => $foodVendor
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/vendor/food/profile",
     *     summary="Get food vendor profile",
     *     tags={"Food Vendor Setup"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function profile()
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = $vendor->foodVendor;

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        return response()->json(['data' => $profile]);
    }

    /**
     * @OA\Put(
     *     path="/api/vendor/food/profile",
     *     summary="Update food vendor profile",
     *     tags={"Food Vendor Setup"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="business_name", type="string"),
     *             @OA\Property(property="specialty", type="string"),
     *             @OA\Property(property="cuisines", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="operating_hours", type="object"),
     *             @OA\Property(property="delivery_radius_km", type="number"),
     *             @OA\Property(property="minimum_order_amount", type="number"),
     *             @OA\Property(property="delivery_fee", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Profile updated")
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'business_name' => 'sometimes|string|max:255',
            'specialty' => 'sometimes|string|max:255',
            'cuisines' => 'nullable|array',
            'location' => 'sometimes|string|max:255',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'contact_phone' => 'sometimes|string|max:20',
            'contact_email' => 'nullable|email',
            'description' => 'nullable|string',
            'logo' => 'nullable|string',
            'estimated_preparation_time' => 'nullable|integer|min:1',
            'operating_hours' => 'nullable|array',
            'delivery_radius_km' => 'nullable|numeric|min:0',
            'minimum_order_amount' => 'nullable|numeric|min:0',
            'delivery_fee' => 'nullable|numeric|min:0',
            'accepts_cash' => 'nullable|boolean',
            'accepts_card' => 'nullable|boolean',
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $foodVendor = $vendor->foodVendor;

        if (!$foodVendor) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $foodVendor->update($request->only([
            'business_name', 'specialty', 'cuisines', 'location',
            'latitude', 'longitude', 'contact_phone', 'contact_email',
            'description', 'logo', 'estimated_preparation_time',
            'operating_hours', 'delivery_radius_km', 'minimum_order_amount',
            'delivery_fee', 'accepts_cash', 'accepts_card'
        ]));

        return response()->json([
            'message' => 'Profile updated successfully',
            'data' => $foodVendor->fresh()
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/vendor/food/toggle-open",
     *     summary="Toggle vendor open/closed status",
     *     tags={"Food Vendor Setup"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_open"},
     *             @OA\Property(property="is_open", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated")
     * )
     */
    public function toggleOpen(Request $request)
    {
        $request->validate([
            'is_open' => 'required|boolean'
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $foodVendor = $vendor->foodVendor;

        if (!$foodVendor) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        $foodVendor->is_open = $request->is_open;
        $foodVendor->save();

        return response()->json([
            'message' => 'Vendor status updated',
            'is_open' => $foodVendor->is_open
        ]);
    }
}
