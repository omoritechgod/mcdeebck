<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Booking;
use App\Models\AdminWallet;
use App\Models\AdminTransaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;


class PaymentController extends Controller
{
    public function payForBooking($id)
    {
        $booking = Booking::with('user')->findOrFail($id);

        if ($booking->status !== 'processing') {
            return response()->json(['error' => 'Booking not ready for payment.'], 400);
        }

        $amount = $booking->total_price;
        $tx_ref = uniqid('flw_');

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->acceptJson() // ensures proper headers
            ->post(config('services.flutterwave.payment_url') . '/payments', [
                'tx_ref' => $tx_ref,
                'amount' => $amount,
                'currency' => 'NGN',
                'redirect_url' => env('FRONTEND_URL') . '/payment-success',
 // public URL
                'customer' => [
                    'email' => $booking->user->email,
                    'name' => $booking->user->name,
                ],
                'meta' => [
                    'booking_id' => $booking->id,
                    'type' => 'apartment_booking',
                ],
                'customizations' => [
                    'title' => 'Apartment Booking Payment',
                    'description' => 'Payment for Booking #' . $booking->id,
                ]
            ]);

        // Log the full response for debugging
        \Log::info('Flutterwave payment init response', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to initialize payment.',
                'details' => $response->json()
            ], 500);
        }

        return response()->json($response->json());
    }





}
