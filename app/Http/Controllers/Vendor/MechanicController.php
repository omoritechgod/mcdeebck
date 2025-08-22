<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Mechanic;
use Illuminate\Support\Facades\Auth;

/**
 * @group Vendor - Mechanic
 *
 * APIs for mechanic vendors to complete their profile
 */
class MechanicController extends Controller
{
    /**
     * Complete Mechanic Registration
     *
     * @OA\Post(
     *     path="/api/vendor/mechanic/setup",
     *     summary="Complete mechanic vendor registration",
     *     tags={"Vendor - Mechanic"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="workshop_name", type="string", example="FixIt Autos"),
     *             @OA\Property(property="services_offered", type="string", example="Oil change, Engine diagnostics"),
     *             @OA\Property(property="location", type="string", example="Ikeja, Lagos"),
     *             @OA\Property(property="contact_number", type="string", example="+2348012345678")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Mechanic profile completed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Mechanic profile completed successfully"),
     *             @OA\Property(property="mechanic", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="vendor_id", type="integer", example=2),
     *                 @OA\Property(property="workshop_name", type="string", example="FixIt Autos"),
     *                 @OA\Property(property="services_offered", type="string", example="Oil change, Engine diagnostics"),
     *                 @OA\Property(property="location", type="string", example="Ikeja, Lagos"),
     *                 @OA\Property(property="contact_number", type="string", example="+2348012345678"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'workshop_name'     => 'nullable|string|max:255',
            'services_offered'  => 'nullable|string|max:500',
            'location'          => 'nullable|string|max:255',
            'contact_number'    => 'nullable|string|max:20',
        ]);

        $vendor = $user->vendor;

        if (!$vendor || $vendor->category !== 'mechanic') {
            return response()->json([
                'error' => 'Only mechanic vendors can access this endpoint.'
            ], 403);
        }

        $mechanic = Mechanic::updateOrCreate(
            ['user_id' => $user->id],
            [
                'vendor_id'        => $vendor->id,
                'workshop_name'    => $request->workshop_name,
                'services_offered' => $request->services_offered,
                'location'         => $request->location,
                'contact_number'   => $request->contact_number,
            ]
        );
                // Mark vendor setup as complete
        $vendor->update(['is_setup_complete' => true]);
        
        return response()->json([
            'message'  => 'Mechanic profile completed successfully',
            'mechanic' => $mechanic
        ], 201);
    }

    /**
     * Get Mechanic Profile
     *
     * @OA\Get(
     *     path="/api/vendor/mechanic/profile",
     *     summary="Get current mechanic profile",
     *     tags={"Vendor - Mechanic"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful response",
     *         @OA\JsonContent(
     *             @OA\Property(property="mechanic", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="user_id", type="integer", example=5),
     *                 @OA\Property(property="vendor_id", type="integer", example=2),
     *                 @OA\Property(property="workshop_name", type="string", example="FixIt Autos"),
     *                 @OA\Property(property="services_offered", type="string", example="Oil change, Engine diagnostics"),
     *                 @OA\Property(property="location", type="string", example="Ikeja, Lagos"),
     *                 @OA\Property(property="contact_number", type="string", example="+2348012345678"),
     *                 @OA\Property(property="status", type="string", example="pending")
     *             )
     *         )
     *     )
     * )
     */
    public function show()
    {
        $user = Auth::user();
        $mechanic = Mechanic::where('user_id', $user->id)->first();

        if (!$mechanic) {
            return response()->json(['error' => 'Mechanic profile not found.'], 404);
        }

        return response()->json(['mechanic' => $mechanic]);
    }
}
