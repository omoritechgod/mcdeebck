<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;

class AdminVendorController extends Controller
{
    public function index()
    {
        $vendors = Vendor::with(['user'])
            ->latest()
            ->get();

        return response()->json([
            'data' => $vendors
        ]);
    }
}
