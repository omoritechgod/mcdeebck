<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

// Models
use App\Models\Booking;
use App\Models\ServiceOrder;
use App\Models\Order;
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
        $status  = $payload['data']['status'] ?? null;
        $meta    = $payload['data']['meta'] ?? [];

        Log::info('FLW Webhook: parsed', compact('status', 'meta'));

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

                case 'ecommerce_order':
                    $this->handleEcommerceOrder($payload);
                    break;

                default:
                    Log::warning('FLW Webhook: unknown type, ignoring', ['type' => $type]);
                    return response()->json(['status' => 'ignored']);
            }
        } catch (\Throwable $e) {
            Log::error('FLW Webhook: processing error', [
                'err' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['status' => 'processed_with_error']);
        }

        return response()->json(['status' => 'success']);
    }

    private function handleApartmentBooking(array $payload): void
    {
        $bookingId = $payload['data']['meta']['booking_id'] ?? null;
        if (!$bookingId) return;

        DB::transaction(function () use ($bookingId, $payload) {
            $booking = Booking::find($bookingId);
            if (!$booking || $booking->status !== 'processing') return;

            $booking->status = 'paid';
            $booking->save();

            // Company cut 10%
            $companyCut = round(0.10 * (float)$booking->total_price, 2);
            $adminWallet = AdminWallet::firstOrCreate(['name' => 'Main'], ['balance' => 0.00, 'currency' => 'NGN']);
            $adminWallet->increment('balance', $companyCut);

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type' => 'credit',
                'amount' => $companyCut,
                'ref' => $payload['data']['tx_ref'] ?? ('flw_' . $bookingId),
                'status' => 'success',
                'meta' => ['entity' => 'booking', 'entity_id' => $bookingId],
            ]);
        });
    }

    private function handleServiceOrder(array $payload): void
    {
        $orderId = $payload['data']['meta']['service_order_id'] ?? null;
        if (!$orderId) return;

        DB::transaction(function () use ($orderId, $payload) {
            $order = ServiceOrder::with(['user'])->find($orderId);
            if (!$order || $order->status !== 'awaiting_payment') return;

            $order->status = 'paid';
            $order->save();

            // Log user debit
            $userWallet = Wallet::firstOrCreate(['user_id' => $order->user_id], ['balance' => 0.00]);
            WalletTransaction::create([
                'wallet_id' => $userWallet->id,
                'type' => 'debit',
                'amount' => $order->amount,
                'ref' => $payload['data']['tx_ref'] ?? ('flw_' . $order->id),
                'status' => 'success',
            ]);

            // Admin cut
            $commissionRate = config('commissions.general_services', 5) / 100;
            $companyCut = round($commissionRate * (float)$order->amount, 2);

            $adminWallet = AdminWallet::firstOrCreate(['name' => 'Main'], ['balance' => 0.00, 'currency' => 'NGN']);
            $adminWallet->increment('balance', $companyCut);

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type' => 'credit',
                'amount' => $companyCut,
                'ref' => $payload['data']['tx_ref'] ?? ('flw_' . $order->id),
                'status' => 'success',
                'meta' => ['entity' => 'service_order', 'entity_id' => $order->id],
            ]);
        });
    }

    private function handleEcommerceOrder(array $payload): void
    {
        $orderId = $payload['data']['meta']['ecommerce_order_id'] ?? null;
        if (!$orderId) return;

        DB::transaction(function () use ($orderId, $payload) {
            $order = Order::with(['user', 'vendor'])->find($orderId);
            if (!$order || $order->status !== 'awaiting_payment') return;

            $order->status = 'paid';
            $order->save();

            // Log user debit
            $userWallet = Wallet::firstOrCreate(['user_id' => $order->user_id], ['balance' => 0.00]);
            WalletTransaction::create([
                'wallet_id' => $userWallet->id,
                'type' => 'debit',
                'amount' => $order->total_amount,
                'ref' => $payload['data']['tx_ref'] ?? ('flw_' . $order->id),
                'status' => 'success',
            ]);

            // Admin cut
            $commissionRate = config('commissions.ecommerce', 5) / 100;
            $companyCut = round($commissionRate * (float)$order->total_amount, 2);

            $adminWallet = AdminWallet::firstOrCreate(['name' => 'Main'], ['balance' => 0.00, 'currency' => 'NGN']);
            $adminWallet->increment('balance', $companyCut);

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type' => 'credit',
                'amount' => $companyCut,
                'ref' => $payload['data']['tx_ref'] ?? ('flw_' . $order->id),
                'status' => 'success',
                'meta' => ['entity' => 'ecommerce_order', 'entity_id' => $order->id],
            ]);
        });
    }

    public function manualTrigger(Request $request)
    {
        $payload = $request->all();
        $status = $payload['data']['status'] ?? null;
        $meta   = $payload['data']['meta'] ?? [];

        if ($status !== 'successful') {
            return response()->json(['error' => 'Payment not successful'], 400);
        }

        try {
            switch ($meta['type'] ?? null) {
                case 'apartment_booking':
                    $this->handleApartmentBooking($payload);
                    break;

                case 'service_order':
                    $this->handleServiceOrder($payload);
                    break;

                case 'ecommerce_order':
                    $this->handleEcommerceOrder($payload);
                    break;

                default:
                    return response()->json(['error' => 'Unknown payment type'], 400);
            }
        } catch (\Throwable $e) {
            Log::error('ManualTrigger error', ['err' => $e->getMessage()]);
            return response()->json(['error' => 'Failed to process payment'], 500);
        }

        return response()->json(['status' => 'success']);
    }
}
