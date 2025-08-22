<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group User
 *
 * APIs for managing user profile
 */
class UserController extends Controller
{
    /**
     * Get authenticated user profile
     *
     * @authenticated
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Doe",
     *   "email": "john@example.com",
     *   "phone": "08012345678",
     *   "user_type": "user",
     *   "status": "active"
     * }
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Update user profile
     *
     * @authenticated
     *
     * @bodyParam name string Example: John Doe
     * @bodyParam phone string Example: 08012345678
     *
     * @response 200 {
     *   "message": "Profile updated successfully",
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "phone": "08012345678"
     *   }
     * }
     */
    public function update(Request $request)
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $request->user()->id,
        ]);

        $user = $request->user();

        $user->update($request->only('name', 'phone'));

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user
        ]);
    }
}
