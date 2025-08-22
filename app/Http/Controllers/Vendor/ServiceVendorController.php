<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceVendor;
use App\Models\ServiceOrder;
use App\Models\ServicePricing;

class ServiceVendorController extends Controller
{
    /**
     * Store or update a service vendor profile.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_name' => 'required|string',
            'description'  => 'nullable|string',
            'location'     => 'required|string',
            'phone'        => 'required|string',
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = ServiceVendor::updateOrCreate(
            ['vendor_id' => $vendor->id],
            $request->only('service_name', 'description', 'location', 'phone')
        );

        // Mark vendor setup as complete
        $vendor->update(['is_setup_complete' => true]);
       
        return response()->json([
            'message' => 'Service vendor profile created successfully',
            'data'    => $profile
        ], 201);
    }

    /**
     * Vendor's own profile (with pricing list).
     */
    public function profile()
    {
        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = $vendor->serviceVendor()->with('pricings')->first();

        if (!$profile) {
            return response()->json(['error' => 'Profile not found'], 404);
        }

        return response()->json(['data' => $profile]);
    }

    /**
     * Manage vendor pricing - add/update.
     */
    public function addPricing(Request $request)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
        ]);

        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $serviceVendor = $vendor->serviceVendor;
        if (!$serviceVendor) {
            return response()->json(['error' => 'Service vendor profile not found'], 404);
        }

        $pricing = $serviceVendor->pricings()->create($request->only('title', 'description', 'price'));

        return response()->json([
            'message' => 'Pricing added successfully',
            'data'    => $pricing
        ], 201);
    }

    /**
     * Update a pricing item.
     */
    public function updatePricing(Request $request, $id)
    {
        $request->validate([
            'title'       => 'required|string',
            'description' => 'nullable|string',
            'price'       => 'required|numeric|min:0',
        ]);

        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $pricing = ServicePricing::whereHas('serviceVendor', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })
            ->findOrFail($id);

        $pricing->update($request->only('title', 'description', 'price'));

        return response()->json([
            'message' => 'Pricing updated successfully',
            'data'    => $pricing
        ]);
    }

    /**
     * Delete a pricing item.
     */
    public function deletePricing($id)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $pricing = ServicePricing::whereHas('serviceVendor', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })
            ->findOrFail($id);

        $pricing->delete();

        return response()->json(['message' => 'Pricing deleted successfully']);
    }

    /**
     * Public endpoint: get all verified service vendors (with pricing, hide phone).
     */
    public function index()
    {
        $vendors = ServiceVendor::with(['vendor.user', 'pricings'])
            ->whereHas('vendor', function ($query) {
                $query->where('is_verified', 1)
                      ->whereHas('user', function ($q) {
                          $q->whereNotNull('phone_verified_at');
                      });
            })
            ->get()
            ->map(function ($vendor) {
                $vendor->phone = null; // Hide phone in public list
                return $vendor;
            });

        return response()->json($vendors);
    }

    /**
     * Public endpoint: show vendor details (with pricing, conditional phone).
     */
    public function show($id)
    {
        $vendor = ServiceVendor::with(['vendor.user', 'pricings'])
            ->whereHas('vendor', function ($query) {
                $query->where('is_verified', 1)
                      ->whereHas('user', function ($q) {
                          $q->whereNotNull('phone_verified_at');
                      });
            })
            ->findOrFail($id);

        $showPhone = false;

        if (Auth::check()) {
            $userId = Auth::id();
            $hasPaidOrder = ServiceOrder::where('service_vendor_id', $vendor->id)
                ->where('user_id', $userId)
                ->where('status', 'paid')
                ->exists();

            if ($hasPaidOrder) {
                $showPhone = true;
            }
        }

        if (!$showPhone) {
            $vendor->phone = null;
        }

        return response()->json($vendor);
    }
}
