<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;

class LiveVendorController extends Controller
{
    /**
     * Return all live vendors.
     */
    public function index()
    {
        $vendors = Vendor::with('user')  // eager load user info
                         ->live()        // uses scopeLive() from model
                         ->get();

        return response()->json([
            'message' => 'Live vendors fetched successfully',
            'data' => $vendors
        ]);
    }

    /**
     * Return single live vendor by ID
     */
    public function show($id)
    {
        $vendor = Vendor::with('user')
                        ->live()
                        ->findOrFail($id);

        return response()->json([
            'message' => 'Vendor fetched successfully',
            'data' => $vendor
        ]);
    }
}
