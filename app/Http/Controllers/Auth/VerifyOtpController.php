<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Carbon;

class VerifyOtpController extends Controller
{
    public function confirmOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp'   => 'required|digits:6',
        ]);

        $user = User::where('email', $request->email)->first();

        $otpEntry = Otp::where('user_id', $user->id)
            ->where('otp', $request->otp)
            ->where('type', 'email')
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$otpEntry) {
            return response()->json(['message' => 'Invalid or expired OTP'], 422);
        }

        // Mark email as verified
        $user->email_verified_at = Carbon::now();
        $user->save();

        // Delete OTP
        $otpEntry->delete();

        return response()->json(['message' => 'Email verified successfully']);
    }
}
