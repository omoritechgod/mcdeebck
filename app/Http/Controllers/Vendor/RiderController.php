<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Rider;
use Illuminate\Support\Facades\Auth;

/**
 * @group Vendor - Rider
 *
 * APIs for managing rider-specific registration and profile
 */
class RiderController extends Controller
{
    /**
     * Complete Rider Registration
     *
     * @OA\Post(
     *     path="/api/vendor/rider/complete-registration",
     *     summary="Complete rider registration",
     *     tags={"Vendor - Rider"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"vehicle_type"},
     *             @OA\Property(property="vehicle_type", type="string", enum={"bike", "tricycle", "car"}, example="bike"),
     *             @OA\Property(property="license_number", type="string", example="ABC123456"),
     *             @OA\Property(property="experience_years", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Rider profile created/updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Rider profile created/updated successfully"),
     *             @OA\Property(property="rider", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="vendor_id", type="integer", example=1),
     *                 @OA\Property(property="vehicle_type", type="string", example="bike"),
     *                 @OA\Property(property="license_number", type="string", example="ABC123456"),
     *                 @OA\Property(property="experience_years", type="integer", example=3),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     )
     * )
     */
    public function completeRegistration(Request $request)
    {
        $request->validate([
            'vehicle_type'     => 'required|in:bike,tricycle,car',
            'license_number'   => 'nullable|string',
            'experience_years' => 'nullable|integer',
        ]);

        $user = Auth::user();

        if ($user->user_type !== 'vendor') {
            return response()->json(['error' => 'Only vendors can complete rider registration.'], 403);
        }

        $vendor = $user->vendor;

        if (!$vendor || $vendor->category !== 'rider') {
            return response()->json(['error' => 'You are not registered under the rider category.'], 403);
        }

        $rider = Rider::updateOrCreate(
            ['user_id' => $user->id],
            [
                'vendor_id'        => $vendor->id,
                'vehicle_type'     => $request->vehicle_type,
                'license_number'   => $request->license_number,
                'experience_years' => $request->experience_years,
            ]
        );
                // Mark vendor setup as complete
        $vendor->update(['is_setup_complete' => true]);

        return response()->json([
            'message' => 'Rider profile created/updated successfully',
            'rider'   => $rider
        ], 201);
    }

    /**
     * Get Rider Profile
     *
     * @OA\Get(
     *     path="/api/vendor/rider/profile",
     *     summary="Get rider profile",
     *     tags={"Vendor - Rider"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Rider profile retrieved",
     *         @OA\JsonContent(
     *             @OA\Property(property="rider", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=2),
     *                 @OA\Property(property="vendor_id", type="integer", example=1),
     *                 @OA\Property(property="vehicle_type", type="string", example="bike"),
     *                 @OA\Property(property="license_number", type="string", example="ABC123456"),
     *                 @OA\Property(property="experience_years", type="integer", example=3),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     )
     * )
     */
    public function profile()
    {
        $user = Auth::user();

        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found.'], 404);
        }

        return response()->json(['rider' => $rider]);
    }
}
