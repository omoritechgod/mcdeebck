<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;


class ApartmentController extends Controller
{
    /**
     * List apartments posted by verified vendors (shown on main site).
     */
    public function index()
    {
        $apartments = Apartment::with(['vendor.user'])
            ->whereHas('vendor.user', function ($query) {
                $query->whereNotNull('phone_verified_at');
            })
            ->whereHas('vendor', function ($q) {
                $q->where('is_verified', true);
            })
            ->latest()
            ->get()
            ->map(function ($apartment) {
                $apartment->images = collect($apartment->images ?? []);
                return $apartment;
            });

        return response()->json([
            'data' => $apartments,
        ]);
    }

    /**
     * Fetch details of a single apartment.
     */
    public function show($id)
    {
        $apartment = Apartment::with(['vendor.user'])->findOrFail($id);

        $apartment->images = collect($apartment->images ?? []);

        return response()->json([
            'data' => $apartment,
        ]);
    }

    /**
     * Vendor creates a new apartment listing.
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
            'images.*' => 'image|mimes:jpg,jpeg,png|max:4096',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $vendor = $user->vendor;

        if (!$vendor) {
            return response()->json([
                'error' => 'Vendor profile not found.'
            ], 403);
        }

        $uploadedImages = [];

        if ($request->hasFile('images')) {
            Log::info('Images found in request', ['count' => count($request->file('images'))]);

            foreach ($request->file('images') as $index => $imageFile) {
                Log::info("Processing image #$index", [
                    'originalName' => $imageFile->getClientOriginalName(),
                    'mimeType' => $imageFile->getMimeType(),
                ]);

                try {
                    $uploaded = \CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary::upload($imageFile->getRealPath(), [
                        'folder' => 'apartments'
                    ]);

                    $secureUrl = $uploaded->getSecurePath();
                    Log::info("Image #$index uploaded", ['url' => $secureUrl]);

                    $uploadedImages[] = $secureUrl;
                } catch (\Exception $e) {
                    Log::error("Failed to upload image #$index", ['error' => $e->getMessage()]);
                }
            }
        } else {
            Log::warning('No images were uploaded. hasFile returned false.');
        }


        try {
            $apartment = Apartment::create([
                'vendor_id' => $vendor->id,
                'title' => $request->title,
                'description' => $request->description,
                'location' => $request->location,
                'price_per_night' => $request->price_per_night,
                'type' => $request->type,
                'images' => $uploadedImages,
                'is_verified' => $vendor->is_verified,
            ]);

            return response()->json([
                'message' => 'Apartment listed successfully.',
                'data' => $apartment,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to save apartment listing', ['exception' => $e]);

            return response()->json([
                'error' => 'An error occurred while saving the apartment.',
            ], 500);
        }
    }
}
