<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Verification;

class ComplianceController extends Controller
{
    /**
     * Get compliance status for the authenticated vendor.
     */
    public function status()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        $verification = Verification::where('user_id', $user->id)->latest()->first();

        return response()->json([
            'phone_verified'    => !is_null($user->phone_verified_at),
            'phone_verified_at' => $user->phone_verified_at,
            'document_uploaded' => $verification !== null,
            'document_type'     => $verification?->type,
            'compliance_status' => $verification?->status,
            'is_verified'       => $vendor?->is_verified ?? 0,
            'document_url'      => $verification?->document_url, // already Cloudinary URL
        ]);
    }

    /**
     * Save compliance document (URL from frontend).
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'type'         => 'required|in:nin,cac',
            'document_url' => 'required|url',
        ]);

        $user = Auth::user();

        // Create verification record with Cloudinary URL
        $verification = Verification::create([
            'user_id'      => $user->id,
            'type'         => $request->type,
            'document_url' => $request->document_url,
            'status'       => 'pending',
        ]);

        return response()->json([
            'message'      => 'Document uploaded successfully',
            'document_url' => $verification->document_url,
        ]);
    }

    /**
     * Submit the document for admin review.
     */
    public function submitReview()
    {
        $user = Auth::user();
        $verification = Verification::where('user_id', $user->id)->latest()->first();

        if (!$verification) {
            return response()->json(['message' => 'No document uploaded'], 422);
        }

        if ($verification->status !== 'pending') {
            return response()->json(['message' => 'Already reviewed'], 400);
        }

        return response()->json([
            'message' => 'Document submitted for review. You’ll be notified once reviewed.'
        ]);
    }
}
