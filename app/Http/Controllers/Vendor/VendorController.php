<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

/**
 * @group Vendor
 *
 * APIs for managing vendor accounts
 */
class VendorController extends Controller
{
    /**
     * Register a new vendor
     *
     * Register either an individual or business vendor.
     *
     * @OA\Post(
     *     path="/api/vendor/register",
     *     tags={"Vendor"},
     *     summary="Register vendor",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vendor_type", "category"},
     *             @OA\Property(property="vendor_type", type="string", enum={"individual", "business"}, example="individual"),
     *             @OA\Property(property="business_name", type="string", example="McDee Motors"),
     *             @OA\Property(property="category", type="string", enum={"product_vendor","mechanic","service_vendor","service_apartment","food_vendor","rider"}, example="rider")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Vendor registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Vendor registered successfully"),
     *             @OA\Property(property="vendor", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="vendor_type", type="string", example="individual"),
     *                 @OA\Property(property="business_name", type="string", example="McDee Motors"),
     *                 @OA\Property(property="category", type="string", example="rider"),
     *                 @OA\Property(property="is_verified", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Only vendor-type users can register as vendors"
     *     )
     * )
     */
    public function register(Request $request)
    {
        $categories = config('vendor.categories');

        $request->validate([
            'vendor_type' => 'required|in:individual,business',
            'business_name' => 'nullable|string',
            'category' => 'required|in:' . implode(',', $categories),
        ]);

        $user = Auth::user()->load('vendor');

        if ($user->user_type !== 'vendor') {
            return response()->json(['error' => 'Only vendor-type users can register as vendors.'], 403);
        }

        $vendor = Vendor::updateOrCreate(
            ['user_id' => $user->id],
            [
                'vendor_type'   => $request->vendor_type,
                'business_name' => $request->business_name,
                'category'      => $request->category,
            ]
        );

        return response()->json([
            'message' => 'Vendor registered successfully',
            'vendor'  => $vendor
        ], 201);
    }

    /**
     * Get current vendor live status
     *
     * @OA\Get(
     *     path="/api/vendor/is-live",
     *     tags={"Vendor"},
     *     summary="Check vendor live status",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Live status response",
     *         @OA\JsonContent(
     *             @OA\Property(property="is_live", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function isLive()
    {
        $user = Auth::user()->load('vendor');

        $vendor = Vendor::where('user_id', $user->id)->first();

        if (!$vendor) {
            return response()->json(['is_live' => false]);
        }

        return response()->json(['is_live' => $vendor->is_verified]);
    }
}
