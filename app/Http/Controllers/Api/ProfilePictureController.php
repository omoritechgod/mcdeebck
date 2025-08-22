<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfilePictureController extends Controller
{
    /**
     * Upload or update the user's profile picture.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'profile_picture' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = Auth::user();

        // Delete old profile picture if it exists
        if ($user->profile_picture && Storage::disk('public')->exists(str_replace('storage/', '', $user->profile_picture))) {
            Storage::disk('public')->delete(str_replace('storage/', '', $user->profile_picture));
        }

        // Store new profile picture in storage/app/public/profile_pictures
        $path = $request->file('profile_picture')->store('profile_pictures', 'public');

        // Save path as 'storage/profile_pictures/filename.jpg'
        $user->profile_picture = 'storage/' . $path;
        $user->save();

        return response()->json([
            'message' => 'Profile picture updated successfully',
            'url' => asset('storage/' . $path),
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
            'url' => asset($user->profile_picture),
        ]);
    }
}
