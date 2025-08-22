<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\OtpVerificationMail;

class SendOtpController extends Controller
{
    public function sendEmailOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $request->email)->first();

        $otpCode = rand(100000, 999999);
        $expiresAt = Carbon::now()->addMinutes(10);

        // Save OTP
        Otp::create([
            'user_id'    => $user->id,
            'otp'        => $otpCode,
            'type'       => 'email',
            'expires_at' => $expiresAt,
        ]);

        // Send Mail
        Mail::to($user->email)->send(new OtpVerificationMail($otpCode, $user->name));

        return response()->json(['message' => 'OTP sent successfully']);
    }
}
