<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TermiiService
{
    public function sendOtp(string $phone, string $otp): void
    {
        $apiKey = config('services.termii.api_key');

        try {
            $response = Http::post('https://v3.api.termii.com/api/sms/send', [
                'api_key' => $apiKey,
                'to' => $phone,
                'from' => 'Termii', // ✅ TEMPORARY WORKING OPTION
                'channel' => 'generic',
                'type' => 'plain',
                'sms' => "Your McDee OTP is $otp",
            ]);


            Log::info('Termii OTP Send Attempt', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            if (!$response->successful()) {
                Log::error('Termii OTP failed', [
                    'status' => $response->status(),
                    'error' => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Termii API connection error', ['error' => $e->getMessage()]);
        }
    }

}
