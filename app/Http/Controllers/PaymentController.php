<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

use App\Models\Booking;
use App\Models\Order;
use App\Models\AdminWallet;
use App\Models\AdminTransaction;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class PaymentController extends Controller
{
    /**
     * ========== APARTMENT BOOKING ==========
     */
    public function payForBooking($id)
    {
        $booking = Booking::with('user')->findOrFail($id);

        if ($booking->status !== 'processing') {
            return response()->json(['error' => 'Booking not ready for payment.'], 400);
        }

        $amount = (float) $booking->total_price;
        $tx_ref = uniqid('flw_booking_');

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->acceptJson()
            ->post(rtrim(config('services.flutterwave.payment_url'), '/') . '/payments', [
                'tx_ref' => $tx_ref,
                'amount' => $amount,
                'currency' => config('app.currency', 'NGN'),
                'redirect_url' => env('FRONTEND_URL') . '/payment-success',
                'customer' => [
                    'email' => $booking->user->email,
                    'name'  => $booking->user->name,
                ],
                'meta' => [
                    'booking_id' => $booking->id,
                    'type'       => 'apartment_booking',
                ],
                'customizations' => [
                    'title' => 'Apartment Booking Payment',
                    'description' => 'Payment for Booking #' . $booking->id,
                ]
            ]);

        Log::info('FLW init booking', [
            'booking_id' => $booking->id,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to initialize payment',
                'details' => $response->json()
            ], 500);
        }

        return response()->json($response->json());
    }

    /**
     * ========== E-COMMERCE ORDER ==========
     */
    public function payForOrder($id)
    {
        $order = Order::with('user')->findOrFail($id);

        if ($order->status !== 'awaiting_payment') {
            return response()->json(['error' => 'Order not ready for payment.'], 400);
        }

        $amount = (float) $order->total_amount;
        $tx_ref = uniqid('flw_order_');

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->acceptJson()
            ->post(rtrim(config('services.flutterwave.payment_url'), '/') . '/payments', [
                'tx_ref' => $tx_ref,
                'amount' => $amount,
                'currency' => config('app.currency', 'NGN'),
                'redirect_url' => env('FRONTEND_URL') . '/payment-success',
                'customer' => [
                    'email' => $order->user->email,
                    'name'  => $order->user->name,
                ],
                'meta' => [
                    'order_id' => $order->id,
                    'type'     => 'ecommerce_order',
                ],
                'customizations' => [
                    'title' => 'Marketplace Payment',
                    'description' => 'Payment for Order #' . $order->id,
                ]
            ]);

        Log::info('FLW init ecommerce order', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            return response()->json([
                'error' => 'Failed to initialize payment',
                'details' => $response->json()
            ], 500);
        }

        return response()->json($response->json());
    }

    /**
     * Release escrow to vendor (buyer confirms delivery).
     */
    public function releaseEscrow(Request $request, Order $order)
    {
        $user = $request->user();
        if ($order->user_id !== $user->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($order->status !== 'delivered') {
            return response()->json(['message' => 'Order not eligible for release'], 422);
        }

        $commissionRate = (float) config('commissions.ecommerce', 5) / 100;
        $total = (float) $order->total_amount;
        $commission = round($total * $commissionRate, 2);
        $vendorShare = round($total - $commission, 2);

        DB::beginTransaction();
        try {
            $adminWallet = AdminWallet::firstOrCreate(
                ['name' => 'Main'],
                ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
            );

            if ((float)$adminWallet->balance < $vendorShare) {
                DB::rollBack();
                return response()->json(['message' => 'Escrow insufficient.'], 500);
            }

            // Debit escrow (payout vendor share)
            $adminWallet->balance -= $vendorShare;
            $adminWallet->save();

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type' => 'debit',
                'amount' => $vendorShare,
                'ref' => 'payout_order_' . $order->id,
                'status' => 'success',
                'meta' => ['order_id' => $order->id, 'action' => 'vendor_payout'],
            ]);

            // Credit vendor wallet
            $vendorWallet = Wallet::firstOrCreate(
                ['user_id' => $order->vendor->user_id],
                ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
            );

            $vendorWallet->balance += $vendorShare;
            $vendorWallet->save();

            WalletTransaction::create([
                'wallet_id' => $vendorWallet->id,
                'type'      => 'credit',
                'amount'    => $vendorShare,
                'ref'       => 'payout_order_' . $order->id,
                'status'    => 'success',
            ]);

            $vendorWallet->balance += $vendorShare;
            $vendorWallet->save();

            $order->status = 'completed';
            $order->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('releaseEscrow error', ['order_id' => $order->id, 'err' => $e->getMessage()]);
            return response()->json(['message' => 'Escrow release failed'], 500);
        }

        return response()->json([
            'message' => 'Escrow released, order completed.',
            'order'   => $order->fresh(),
        ]);
    }

    /**
     * Refund order (Admin only).
     */
    public function refundOrder(Request $request, Order $order)
    {
        if (!in_array($order->status, ['paid', 'disputed'])) {
            return response()->json(['message' => 'Order not eligible for refund'], 422);
        }

        DB::beginTransaction();
        try {
            $adminWallet = AdminWallet::firstOrCreate(
                ['name' => 'Main'],
                ['balance' => 0.00, 'currency' => config('app.currency', 'NGN')]
            );

            $total = (float) $order->total_amount;

            if ($adminWallet->balance < $total) {
                DB::rollBack();
                return response()->json(['message' => 'Escrow insufficient for refund'], 500);
            }

            $adminWallet->balance -= $total;
            $adminWallet->save();

            AdminTransaction::create([
                'admin_wallet_id' => $adminWallet->id,
                'type'   => 'debit',
                'amount' => $total,
                'ref'    => 'refund_order_' . $order->id,
                'status' => 'success',
                'meta'   => ['order_id' => $order->id],
            ]);

            $order->status = 'refunded';
            $order->save();

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('refundOrder error', ['order_id' => $order->id, 'err' => $e->getMessage()]);
            return response()->json(['message' => 'Refund failed'], 500);
        }

        return response()->json([
            'message' => 'Refund successful.',
            'order'   => $order->fresh(),
        ]);
    }
}
