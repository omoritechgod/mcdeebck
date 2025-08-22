<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

/**
 * @group Authentication
 *
 * APIs for user and vendor authentication
 */
class AuthController extends Controller
{
    /**
     * Register a new user or vendor
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"Authentication"},
     *     summary="Register a new user or vendor",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "phone", "email", "password", "user_type"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="phone", type="string", example="08012345678"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123"),
     *             @OA\Property(property="user_type", type="string", enum={"user", "vendor"}, example="vendor")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="your_token_here"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="user_type", type="string", example="vendor")
     *             )
     *         )
     *     )
     * )
     */
    public function register(Request $request)
    {
        $messages = [
            'email.unique' => 'This email is already registered.',
            'phone.unique' => 'This phone number is already in use.',
        ];

        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'required|string|max:20|unique:users,phone',
            'email'     => 'required|string|email|max:255|unique:users,email',
            'password'  => 'required|string|min:6',
            'user_type' => 'required|in:user,vendor',
        ], $messages);

        $user = User::create([
            'name'      => $validated['name'],
            'phone'     => $validated['phone'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'user_type' => $validated['user_type'],
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user'  => $user,
        ], 201);
    }

    /**
     * Login user or vendor
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"Authentication"},
     *     summary="Login user or vendor",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="password", type="string", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="your_token_here"),
     *             @OA\Property(property="user", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="user_type", type="string", example="vendor")
     *             ),
     *             @OA\Property(property="vendor", type="object",
     *                 @OA\Property(property="category", type="string", example="mechanic"),
     *                 @OA\Property(property="is_verified", type="boolean", example=false)
     *             )
     *         )
     *     )
     * )
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        $vendor = null;
        if ($user->user_type === 'vendor') {
            $vendor = $user->vendor; // Relationship defined in User model
        }

        return response()->json([
            'token'  => $token,
            'user'   => $user,
            'vendor' => $vendor,
        ]);
    }

    /**
     * Logout the authenticated user
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"Authentication"},
     *     summary="Logout the authenticated user",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logged out",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out")
     *         )
     *     )
     * )
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Get authenticated user
     *
     * @OA\Get(
     *     path="/api/me",
     *     tags={"Authentication"},
     *     summary="Get the authenticated user",
     *     security={{"sanctum": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User profile",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer", example=1),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="user_type", type="string", example="vendor"),
     *             @OA\Property(property="vendor", type="object",
     *                 @OA\Property(property="category", type="string", example="rider"),
     *                 @OA\Property(property="is_setup_complete", type="boolean", example=true)
     *             )
     *         )
     *     )
     * )
     */
    public function me(Request $request)
    {
        $user = $request->user();

        $vendor = null;

        if ($user->user_type === 'vendor') {
            $vendorModel = $user->vendor;

            // Get latest verification record for vendor
            $latestVerification = \App\Models\Verification::where('user_id', $user->id)
                ->latest()
                ->first();

            // Build vendor response with verification_status injected
            $vendor = [
                'id' => $vendorModel->id,
                'category' => $vendorModel->category,
                'business_name' => $vendorModel->business_name,
                'vendor_type' => $vendorModel->vendor_type,
                'verification_status' => $latestVerification?->status ?? 'pending',
            ];
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'user_type' => $user->user_type,
                'profile_image' => $user->profile_picture ? asset($user->profile_picture) : null,
            ],
            'vendor' => $vendor,
        ]);
    }


}
