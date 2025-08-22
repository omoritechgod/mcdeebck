<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Verification;
use Illuminate\Http\Request;

class AdminKYCVerificationController extends Controller
{
    public function index()
    {
        $verifications = Verification::with(['user', 'vendor'])->latest()->get();

        return response()->json([
            'data' => $verifications ?? [],
        ]);
    }

    public function approve($id)
    {
        $verification = Verification::with('vendor')->findOrFail($id);

        $verification->update([
            'status' => 'approved',
            'verified_at' => now(),
        ]);

        if ($verification->vendor) {
            $verification->vendor->update([
                'is_verified' => true,
                'verification_status' => 'approved',
            ]);
        }

        return response()->json([
            'message' => 'KYC approved successfully.',
            'vendor_id' => $verification->vendor?->id,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $verification = Verification::with('vendor')->findOrFail($id);

        $verification->update([
            'status' => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        if ($verification->vendor) {
            $verification->vendor->update([
                'is_verified' => false,
                'verification_status' => 'rejected',
            ]);
        }

        return response()->json([
            'message' => 'KYC rejected.',
            'vendor_id' => $verification->vendor?->id,
        ]);
    }
}
