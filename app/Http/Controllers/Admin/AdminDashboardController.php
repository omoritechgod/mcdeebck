<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;

class AdminDashboardController extends Controller
{
    public function stats()
    {
        $totalVendors = Vendor::count();
        $pendingKyc = Vendor::where('is_verified', 0)->count();
        $approvedVendors = Vendor::where('is_verified', 1)->count();
        $rejectedVendors = Vendor::where('is_verified', 2)->count(); // Optional
        $totalUsers = User::count();
        $activeVendors = Vendor::whereHas('user', function ($q) {
            $q->whereNotNull('phone_verified_at');
        })->where('is_verified', 1)->count();

        return response()->json([
            'data' => [
                'total_vendors' => $totalVendors,
                'pending_kyc' => $pendingKyc,
                'approved_vendors' => $approvedVendors,
                'rejected_vendors' => $rejectedVendors,
                'total_users' => $totalUsers,
                'active_vendors' => $activeVendors,
            ]
        ]);
    }
}
