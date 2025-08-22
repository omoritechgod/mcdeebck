<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AfricastalkingService
{
    protected $username;
    protected $apiKey;
    protected $senderId;

    public function __construct()
    {
        $this->username = config('services.africastalking.username');
        $this->apiKey   = config('services.africastalking.api_key');
        $this->senderId = config('services.africastalking.sender_id', 'AFRICASTKNG'); // fallback
    }

    public function sendOtp(string $phone, string $otp): void
    {
        $message = "Your McDee verification code is: $otp";

        $response = Http::asForm() // required to avoid 415 error
            ->withHeaders([
                'apiKey' => $this->apiKey,
                'Accept' => 'application/json',
            ])
            ->post('https://api.africastalking.com/version1/messaging', [
                'username' => $this->username,
                'to'       => $phone,
                'message'  => $message,
                'from'     => $this->senderId,
            ]);

        Log::info('Africastalking OTP response', [
            'status' => $response->status(),
            'body'   => $response->json(),
        ]);
    }
}
