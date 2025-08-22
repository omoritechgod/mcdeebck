<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Verification;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Vendor Verification",
 *     description="APIs for managing vendor verification using NIN or CAC"
 * )
 */
class VerificationController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/verification/initiate",
     *     summary="Initiate vendor verification (NIN or CAC)",
     *     tags={"Vendor Verification"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"type"},
     *             @OA\Property(property="type", type="string", enum={"NIN", "CAC"}, example="NIN")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification initiated",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification initiated")
     *         )
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="Verification already started or completed"
     *     )
     * )
     */
    public function initiate(Request $request)
    {
        $request->validate([
            'type' => 'required|in:NIN,CAC',
        ]);

        $user = Auth::user();

        if (Verification::where('user_id', $user->id)->exists()) {
            return response()->json(['message' => 'Verification already started or completed'], 409);
        }

        Verification::create([
            'user_id' => $user->id,
            'type' => $request->type,
            'value' => '',
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Verification initiated']);
    }

    /**
     * @OA\Post(
     *     path="/api/verification/submit",
     *     summary="Submit verification value (NIN or CAC)",
     *     tags={"Vendor Verification"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"value"},
     *             @OA\Property(property="value", type="string", example="12345678901")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Verification submitted",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Verification submitted")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No verification in progress"
     *     )
     * )
     */
    public function submit(Request $request)
    {
        $request->validate([
            'value' => 'required|string|min:6',
        ]);

        $user = Auth::user();

        $verification = Verification::where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if (! $verification) {
            return response()->json(['message' => 'No verification in progress'], 404);
        }

        $verification->update([
            'value' => $request->value,
        ]);

        return response()->json(['message' => 'Verification submitted']);
    }

    /**
     * @OA\Get(
     *     path="/api/verification/status",
     *     summary="Check vendor verification status",
     *     tags={"Vendor Verification"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Verification status",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="pending"),
     *             @OA\Property(property="type", type="string", example="NIN"),
     *             @OA\Property(property="value", type="string", example="12345678901")
     *         )
     *     )
     * )
     */
    public function status()
    {
        $user = Auth::user();

        $verification = Verification::where('user_id', $user->id)->first();

        if (! $verification) {
            return response()->json(['status' => 'none']);
        }

        return response()->json([
            'status' => $verification->status,
            'type' => $verification->type,
            'value' => $verification->value
        ]);
    }
}
