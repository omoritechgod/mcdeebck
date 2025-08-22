<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Carbon;

class OtpController extends Controller
{
    /**
     * Send voice OTP using SmartSMS to user's phone number
     */
    public function sendPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::find($request->user_id);

        if (!$user->phone) {
            return response()->json(['message' => 'User does not have a phone number'], 400);
        }

        $phone = $user->phone;

        if (!str_starts_with($phone, '0') && !str_starts_with($phone, '+234')) {
            return response()->json(['message' => 'Phone number must start with 0 or +234'], 400);
        }

        $otpCode = rand(100, 999); // 3-digit OTP for voice

        // Save OTP
        Otp::create([
            'user_id'    => $user->id,
            'otp'        => $otpCode,
            'type'       => 'phone',
            'used'       => false,
            'expires_at' => now()->addMinutes(10),
        ]);

        try {
            $response = Http::asMultipart()->post('https://app.smartsmssolutions.com/io/api/client/v1/voiceotp/send/', [
                ['name' => 'token', 'contents' => env('SMARTSMS_TOKEN')],
                ['name' => 'phone', 'contents' => $phone],
                ['name' => 'otp', 'contents' => $otpCode],
                ['name' => 'class', 'contents' => env('SMARTSMS_VOICE_CLASS')],
            ]);

            \Log::info('Voice OTP Response', [
                'response' => $response->body()
            ]);

            $json = json_decode($response->body(), true);

            if ($json && $json['success'] === true) {
                return response()->json(['message' => 'Voice OTP sent successfully']);
            }

            return response()->json([
                'message' => 'Voice OTP failed',
                'gateway_response' => $response->body()
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Voice OTP request failed',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Verify OTP for the user and mark phone as verified
     */
    public function verifyPhoneOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|exists:users,id',
            'otp' => 'required|digits:3',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $otp = Otp::where('user_id', $request->user_id)
            ->where('otp', $request->otp)
            ->where('type', 'phone')
            ->where('used', false)
            ->where('expires_at', '>', now())
            ->first();

        if (!$otp) {
            return response()->json(['message' => 'Invalid or expired OTP'], 400);
        }

        // Mark OTP as used
        $otp->used = true;
        $otp->save();

        // ✅ UPDATE user's phone_verified_at
        $user = User::find($request->user_id);
        $user->phone_verified_at = now();
        $user->save();

        return response()->json(['message' => 'OTP verified successfully']);
    }

}
