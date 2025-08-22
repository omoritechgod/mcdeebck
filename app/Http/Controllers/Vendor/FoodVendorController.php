<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\FoodVendor;

/**
 * @group Vendor - Food Vendor
 *
 * APIs for food vendors to complete their profile
 */
class FoodVendorController extends Controller
{
    /**
     * Complete Food Vendor Setup
     *
     * @authenticated
     *
     * @bodyParam business_name string required Example: Mama Blessing Kitchen
     * @bodyParam specialty string required Example: Nigerian delicacies
     * @bodyParam location string required Example: Wuse 2, Abuja
     * @bodyParam contact_phone string required Example: 08012345678
     * @bodyParam contact_email string nullable Example: mamablessing@gmail.com
     * @bodyParam description string nullable Example: We offer healthy home-cooked meals.
     * @bodyParam logo string nullable Example: uploads/logos/mama.png
     *
     * @response 201 {
     *   "message": "Food vendor profile completed",
     *   "data": {
     *     "id": 1,
     *     "vendor_id": 12,
     *     "business_name": "Mama Blessing Kitchen",
     *     "specialty": "Nigerian delicacies",
     *     "location": "Wuse 2, Abuja",
     *     "contact_phone": "08012345678",
     *     "contact_email": "mamablessing@gmail.com",
     *     "description": "We offer healthy home-cooked meals.",
     *     "logo": "uploads/logos/mama.png"
     *   }
     * }
     */
    public function store(Request $request)
    {
        $request->validate([
            'business_name'   => 'required|string|max:255',
            'specialty'       => 'required|string|max:255',
            'location'        => 'required|string|max:255',
            'contact_phone'   => 'required|string|max:20',
            'contact_email'   => 'nullable|email',
            'description'     => 'nullable|string',
            'logo'            => 'nullable|string'
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $foodVendor = FoodVendor::updateOrCreate(
            ['vendor_id' => $vendor->id],
            $request->only([
                'business_name', 'specialty', 'location',
                'contact_phone', 'contact_email', 'description', 'logo'
            ])
        );

        // Mark vendor setup as complete
        $vendor->update(['is_setup_complete' => true]);

        return response()->json([
            'message' => 'Food vendor profile completed',
            'data'    => $foodVendor
        ], 201);
    }

    /**
     * Get Food Vendor Profile
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "vendor_id": 12,
     *     "business_name": "Mama Blessing Kitchen",
     *     "specialty": "Nigerian delicacies",
     *     "location": "Wuse 2, Abuja",
     *     "contact_phone": "08012345678",
     *     "contact_email": "mamablessing@gmail.com",
     *     "description": "We offer healthy home-cooked meals.",
     *     "logo": "uploads/logos/mama.png"
     *   }
     * }
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
}
