<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceApartment;

/**
 * @group Vendor - Service Apartment
 *
 * APIs for service apartment vendors to complete their profile
 */
class ServiceApartmentController extends Controller
{
    /**
     * Complete service apartment vendor setup
     *
     * @OA\Post(
     *     path="/api/vendor/apartment/setup",
     *     tags={"Vendor - Service Apartment"},
     *     summary="Setup service apartment vendor profile",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={
     *                 "full_name", "phone_number", "organization_name", "organization_address", "years_of_experience"
     *             },
     *             @OA\Property(property="full_name", type="string", example="Jane Doe"),
     *             @OA\Property(property="phone_number", type="string", example="08012345678"),
     *             @OA\Property(property="organization_name", type="string", example="Palm View Homes"),
     *             @OA\Property(property="organization_address", type="string", example="24 Allen Avenue, Ikeja, Lagos"),
     *             @OA\Property(property="website", type="string", format="url", example="https://palmviewhomes.ng"),
     *             @OA\Property(property="years_of_experience", type="integer", example=3)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Service apartment profile completed",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Service apartment profile completed"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="vendor_id", type="integer", example=12),
     *                 @OA\Property(property="full_name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="phone_number", type="string", example="08012345678"),
     *                 @OA\Property(property="organization_name", type="string", example="Palm View Homes"),
     *                 @OA\Property(property="organization_address", type="string", example="24 Allen Avenue, Ikeja, Lagos"),
     *                 @OA\Property(property="website", type="string", example="https://palmviewhomes.ng"),
     *                 @OA\Property(property="years_of_experience", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function setup(Request $request)
    {
        $request->validate([
            'full_name'             => 'required|string|max:255',
            'phone_number'          => 'required|string|max:20',
            'organization_name'     => 'required|string|max:255',
            'organization_address'  => 'required|string|max:500',
            'website'               => 'nullable|url',
            'years_of_experience'   => 'required|integer|min:0',
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'service_apartment') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = ServiceApartment::updateOrCreate(
            ['vendor_id' => $vendor->id],
            $request->only([
                'full_name',
                'phone_number',
                'organization_name',
                'organization_address',
                'website',
                'years_of_experience',
            ])
        );

        $vendor->update(['is_setup_complete' => true]);

        return response()->json([
            'message' => 'Service apartment profile completed',
            'data' => $profile
        ], 201);
    }

    /**
     * Get service apartment profile
     *
     * @OA\Get(
     *     path="/api/vendor/apartment/profile",
     *     tags={"Vendor - Service Apartment"},
     *     summary="Get vendor's service apartment profile",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Retrieved profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="vendor_id", type="integer", example=12),
     *                 @OA\Property(property="full_name", type="string", example="Jane Doe"),
     *                 @OA\Property(property="phone_number", type="string", example="08012345678"),
     *                 @OA\Property(property="organization_name", type="string", example="Palm View Homes"),
     *                 @OA\Property(property="organization_address", type="string", example="24 Allen Avenue, Ikeja, Lagos"),
     *                 @OA\Property(property="website", type="string", example="https://palmviewhomes.ng"),
     *                 @OA\Property(property="years_of_experience", type="integer", example=3)
     *             )
     *         )
     *     )
     * )
     */
    public function profile()
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'service_apartment') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = $vendor->serviceApartment;

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        return response()->json(['data' => $profile]);
    }
}
