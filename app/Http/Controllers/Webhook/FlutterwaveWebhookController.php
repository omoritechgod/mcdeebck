<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Models
use App\Models\Booking;
use App\Models\ServiceOrder;
use App\Models\AdminWallet;
use App\Models\AdminTransaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class FlutterwaveWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1) Signature verification (support both headers just in case)
        $secretHash = env('FLW_SECRET_HASH');
        $sig = $request->header('verif-hash') ?? $request->header('flutterwave-signature');

        Log::info('FLW Webhook: headers', $request->headers->all());
        Log::info('FLW Webhook: raw', [$request->getContent()]);

        if (!$sig || $sig !== $secretHash) {
            Log::warning('FLW Webhook: invalid signature', ['received' => $sig, 'expected' => $secretHash]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        // 2) Parse payload
        $payload = $request->all();
        $event   = $payload['event'] ?? null;
        $status  = $payload['data']['status'] ?? null;
        $meta    = $payload['data']['meta'] ?? [];

        Log::info('FLW Webhook: parsed', compact('event', 'status', 'meta'));

        // Only handle successful charges
        if ($status !== 'successful') {
            Log::info('FLW Webhook: non-successful status, ignoring', ['status' => $status]);
            return response()->json(['status' => 'ignored']);
        }

        // 3) Route by meta.type
        $type = $meta['type'] ?? null;

        try {
            switch ($type) {
                case 'apartment_booking':
                    $this->handleApartmentBooking($payload);
                    break;

                case 'service_order':
                    $this->handleServiceOrder($payload);
                    break;

                default:
                    Log::warning('FLW Webhook: unknown type, ignoring', ['type' => $type]);
                    return response()->json(['status' => 'ignored']);
            }
        } catch (\Throwable $e) {
            Log::error('FLW Webhook: processing error', ['err' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            // Return 200 so Flutterwave doesn’t keep retrying while we investigate
            return response()->json(['status' => 'processed_with_error']);
        }

        return response()->json(['status' => 'success']);
    }

    private function handleApartmentBooking(array $payload): void
    {
        $bookingId = $payload['data']['meta']['booking_id'] ?? null;
        if (!$bookingId) {
            Log::warning('Booking webhook without booking_id');
            return;
        }

        DB::transaction(function () use ($bookingId, $payload) {
            $booking = Booking::find($bookingId);
            if (!$booking) {
                Log::error('Booking not found', ['id' => $bookingId]);
                return;
            }

            // Idempotency: only move from processing -> paid once
            if ($booking->status !== 'processing') {
                Log::info('Booking not in processing; skip', ['id' => $bookingId, 'status' => $booking->status]);
                return;
            }

            $booking->status = 'paid';
            $booking->save();
            Log::info('Booking marked paid', ['id' => $bookingId]);

            // Company cut 10%
            $companyCut  = round(0.10 * (float)$booking->total_price, 2);
            $adminWallet = AdminWallet::firstOrCreate(['name' => 'Main'], ['balance' => 0.00, 'currency' => 'NGN']);
            $adminWallet->balance = (float)$adminWallet->balance + $companyCut;
            $adminWallet->save();

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type'            => 'credit',
                'amount'          => $companyCut,
                'ref'             => $payload['data']['tx_ref'] ?? ('flw_' . $bookingId),
                'status'          => 'success',
                'meta'            => ['payment_gateway' => 'flutterwave', 'entity' => 'booking', 'entity_id' => $bookingId],
            ]);
        });
    }

    private function handleServiceOrder(array $payload): void
    {
        $orderId = $payload['data']['meta']['service_order_id'] ?? null;
        if (!$orderId) {
            Log::warning('Service order webhook without service_order_id');
            return;
        }

        DB::transaction(function () use ($orderId, $payload) {
            /** @var ServiceOrder $order */
            $order = ServiceOrder::with(['user', 'serviceVendor.vendor'])->find($orderId);
            if (!$order) {
                Log::error('ServiceOrder not found', ['id' => $orderId]);
                return;
            }

            // Idempotency
            if ($order->status !== 'awaiting_payment') {
                Log::info('ServiceOrder not awaiting_payment; skip', ['id' => $orderId, 'status' => $order->status]);
                return;
            }

            // Mark paid
            $order->status = 'paid';
            $order->save();
            Log::info('ServiceOrder marked paid', ['id' => $orderId]);

            // Log user debit in wallet (for history)
            $userWallet = Wallet::firstOrCreate(['user_id' => $order->user_id], ['balance' => 0.00]);
            WalletTransaction::create([
                'wallet_id'     => $userWallet->id,
                'performed_by'  => 'user',
                'description'   => 'Payment for service order #' . $order->id,
                'related_type'  => 'service_order',
                'related_id'    => $order->id,
                'type'          => 'debit',
                'amount'        => $order->amount,
                'ref'           => $payload['data']['tx_ref'] ?? ('flw_' . $order->id),
                'status'        => 'success',
            ]);

            // Company cut 10% into AdminWallet (escrow fee portion now)
            $companyCut  = round(0.10 * (float)$order->amount, 2);
            $adminWallet = AdminWallet::firstOrCreate(['name' => 'Main'], ['balance' => 0.00, 'currency' => 'NGN']);
            $adminWallet->balance = (float)$adminWallet->balance + $companyCut;
            $adminWallet->save();

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type'            => 'credit',
                'amount'          => $companyCut,
                'ref'             => $payload['data']['tx_ref'] ?? ('flw_' . $order->id),
                'status'          => 'success',
                'meta'            => ['payment_gateway' => 'flutterwave', 'entity' => 'service_order', 'entity_id' => $order->id],
            ]);
        });
    }
}
