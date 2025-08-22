<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use App\Services\AfricastalkingService;
use Illuminate\Support\Carbon;

class PhoneOtpController extends Controller
{
    /**
     * Send OTP to user's phone using Africa's Talking
     */
    public function send(Request $request, AfricastalkingService $africastalking)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
        ]);

        $user = User::where('phone', $request->phone)->first();

        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        // Store OTP
        Otp::create([
            'user_id'    => $user->id,
            'otp'        => $otpCode,
            'type'       => 'phone',
            'expires_at' => $expiresAt,
        ]);

        // Send via Africa's Talking
        $africastalking->sendOtp($request->phone, $otpCode);

        return response()->json(['message' => 'OTP sent to phone successfully']);
    }

    /**
     * Confirm OTP sent to user's phone
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'phone' => 'required|string|exists:users,phone',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('phone', $request->phone)->first();

        $otpEntry = Otp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('type', 'phone')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpEntry) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        $user->phone_verified_at = Carbon::now();
        $user->save();

        $otpEntry->delete();

        return response()->json(['message' => 'Phone number verified successfully']);
    }
}
