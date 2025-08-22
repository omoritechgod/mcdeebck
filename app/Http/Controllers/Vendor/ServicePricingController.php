<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ServiceVendor;
use App\Models\ServicePricing;

class ServicePricingController extends Controller
{
    // Vendor: list own pricing items
    public function index()
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = ServiceVendor::where('vendor_id', $vendor->id)->first();
        if (!$profile) {
            return response()->json(['data' => []]);
        }

        $pricings = $profile->pricings()->orderBy('id', 'desc')->get();
        return response()->json(['data' => $pricings]);
    }

    // Vendor: create price category
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:191',
            'price' => 'required|numeric|min:0.01',
        ]);

        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = ServiceVendor::where('vendor_id', $vendor->id)->first();
        if (!$profile) {
            return response()->json(['error' => 'Create your service vendor profile first'], 422);
        }

        $pricing = ServicePricing::create([
            'service_vendor_id' => $profile->id,
            'title'             => $request->title,
            'price'             => $request->price,
        ]);

        return response()->json(['message' => 'Pricing created', 'data' => $pricing], 201);
    }

    // Vendor: update price category
    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:191',
            'price' => 'sometimes|required|numeric|min:0.01',
        ]);

        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = ServiceVendor::where('vendor_id', $vendor->id)->firstOrFail();

        $pricing = ServicePricing::where('id', $id)
            ->where('service_vendor_id', $profile->id)
            ->firstOrFail();

        $pricing->fill($request->only('title', 'price'));
        $pricing->save();

        return response()->json(['message' => 'Pricing updated', 'data' => $pricing]);
    }

    // Vendor: delete price category
    public function destroy($id)
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor || $vendor->category !== 'service_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $profile = ServiceVendor::where('vendor_id', $vendor->id)->firstOrFail();

        $pricing = ServicePricing::where('id', $id)
            ->where('service_vendor_id', $profile->id)
            ->firstOrFail();

        $pricing->delete();

        return response()->json(['message' => 'Pricing deleted']);
    }
}
