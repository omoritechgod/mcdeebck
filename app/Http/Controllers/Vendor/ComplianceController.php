<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\Vendor;
use App\Models\Verification;

class ComplianceController extends Controller
{
    /**
     * Get the compliance status for the authenticated vendor.
     */
    public function status()
    {
        $user = Auth::user();
        $vendor = $user->vendor;
        $verification = Verification::where('user_id', $user->id)->latest()->first();

        return response()->json([
            'phone_verified' => !is_null($user->phone_verified_at),
            'phone_verified_at' => $user->phone_verified_at,
            'document_uploaded' => $verification !== null,
            'document_type' => $verification?->type,
            'compliance_status' => $verification?->status,
            'is_verified' => $vendor?->is_verified ?? 0,
            'document_url' => $verification ? asset('storage/' . $verification->document_url) : null,
        ]);
    }

    /**
     * Upload a compliance document (NIN or CAC).
     */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'type' => 'required|in:nin,cac',
            'document' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $user = Auth::user();

        // Store file in storage/app/public/compliance_docs
        $path = $request->file('document')->store('compliance_docs', 'public');

        // Create verification record
        $verification = Verification::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'document_url' => $path,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document_url' => asset('storage/' . $path),
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

        return response()->json(['message' => 'Document submitted for review. You’ll be notified once reviewed.']);
    }
}
