<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;

class PublicListingController extends Controller
{
    /**
     * Fetch all public listings by verified "live" apartment vendors.
     */
    public function index()
    {
        $listings = Listing::whereHas('vendor', function ($query) {
            $query->where('is_verified', 1)
                ->where('category', 'service_apartment')
                ->whereHas('user', function ($q) {
                    $q->whereNotNull('phone_verified_at');
                });
        })
        ->where('is_verified', 1)
        ->latest()
        ->get();

        return response()->json([
            'message' => 'Live apartment listings fetched successfully.',
            'data' => $listings,
        ]);
    }

    /**
     * Show public details for a single apartment.
     */
    public function show($id)
    {
        $listing = Listing::with('vendor.user')->find($id);

        if (
            !$listing ||
            !$listing->is_verified ||
            !$listing->vendor ||
            !$listing->vendor->is_live ||
            $listing->vendor->category !== 'apartment'
        ) {
            return response()->json(['error' => 'Apartment not available.'], 404);
        }

        return response()->json([
            'message' => 'Apartment details fetched successfully.',
            'data' => $listing,
        ]);
    }
}
