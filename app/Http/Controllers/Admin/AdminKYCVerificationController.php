<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Verification;
use Illuminate\Http\Request;

class AdminKYCVerificationController extends Controller
{
    /**
     * List all vendor KYC verifications
     */
    public function index()
    {
        $verifications = Verification::with(['user', 'vendor'])->latest()->get();

        // Ensure the response always returns the Cloudinary URL directly
        $verifications->transform(function ($verification) {
            return [
                'id'              => $verification->id,
                'user'            => $verification->user,
                'vendor'          => $verification->vendor,
                'type'            => $verification->type,
                'document_url'    => $verification->document_url, // Already Cloudinary URL
                'status'          => $verification->status,
                'rejection_reason'=> $verification->rejection_reason,
                'verified_at'     => $verification->verified_at,
                'created_at'      => $verification->created_at,
                'updated_at'      => $verification->updated_at,
            ];
        });

        return response()->json([
            'data' => $verifications ?? [],
        ]);
    }

    /**
     * Approve a vendor KYC verification
     */
    public function approve($id)
    {
        $verification = Verification::with('vendor')->findOrFail($id);

        $verification->update([
            'status'      => 'approved',
            'verified_at' => now(),
        ]);

        if ($verification->vendor) {
            $verification->vendor->update([
                'is_verified'          => true,
                'verification_status'  => 'approved',
            ]);
        }

        return response()->json([
            'message'   => 'KYC approved successfully.',
            'vendor_id' => $verification->vendor?->id,
        ]);
    }

    /**
     * Reject a vendor KYC verification
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $verification = Verification::with('vendor')->findOrFail($id);

        $verification->update([
            'status'           => 'rejected',
            'rejection_reason' => $request->reason,
        ]);

        if ($verification->vendor) {
            $verification->vendor->update([
                'is_verified'          => false,
                'verification_status'  => 'rejected',
            ]);
        }

        return response()->json([
            'message'   => 'KYC rejected.',
            'vendor_id' => $verification->vendor?->id,
        ]);
    }
}
