<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfilePictureController extends Controller
{
    /**
     * Save the Cloudinary profile picture URL.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'profile_picture_url' => 'required|url',
        ]);

        $user = Auth::user();

        // Save Cloudinary URL directly
        $user->profile_picture = $request->profile_picture_url;
        $user->save();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'url'     => $user->profile_picture,
        ]);
    }

    /**
     * Retrieve the current user's profile picture.
     */
    public function get()
    {
        $user = Auth::user();

        if (!$user->profile_picture) {
            return response()->json(['error' => 'No profile picture found'], 404);
        }

        return response()->json([
            'url' => $user->profile_picture,
        ]);
    }
}
