<?php

namespace App\Http\Controllers;

use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ListingController extends Controller
{
    /**
     * Vendor lists their own apartments.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if (!$user || !$user->vendor) {
            return response()->json(['error' => 'Unauthorized or no vendor profile.'], 403);
        }

        $listings = $user->vendor->listings()->latest()->get();

        return response()->json([
            'message' => 'Listings fetched successfully.',
            'data' => $listings,
        ]);
    }

    /**
     * Vendor posts a new apartment listing.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'nullable|string',
            'location' => 'required|string',
            'price_per_night' => 'required|numeric',
            'type' => 'required|in:hotel,hostel,shortlet',
            'images' => 'nullable|array',
            'images.*' => 'url', // Expect an array of image URLs from frontend
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json(['error' => 'Vendor profile not found.'], 403);
        }

        $listing = Listing::create([
            'vendor_id' => $vendor->id,
            'title' => $request->title,
            'description' => $request->description,
            'location' => $request->location,
            'price_per_night' => $request->price_per_night,
            'type' => $request->type,
            'images' => $request->images ?? [],
            'is_verified' => $vendor->is_verified,
        ]);

        return response()->json([
            'message' => 'Listing created successfully.',
            'data' => $listing,
        ]);
    }
}
