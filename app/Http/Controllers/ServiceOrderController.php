<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\ServiceOrder;
use App\Models\ServiceVendor;
use App\Models\ServicePricing;
use App\Models\Wallet;
use App\Models\WalletTransaction;

class ServiceOrderController extends Controller
{
    /**
     * Step 1: User creates a service order request.
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_vendor_id'  => 'required|exists:service_vendors,id',
            'service_pricing_id' => 'required|exists:service_pricings,id',
            'notes'              => 'nullable|string',
            'deadline'           => 'nullable|date|after_or_equal:today',
        ]);

        // Ensure pricing belongs to the vendor
        $pricing = ServicePricing::where('id', $request->service_pricing_id)
            ->where('service_vendor_id', $request->service_vendor_id)
            ->firstOrFail();

        $order = ServiceOrder::create([
            'user_id'            => Auth::id(),
            'service_vendor_id'  => $request->service_vendor_id,
            'notes'              => $request->notes,
            'deadline'           => $request->deadline,
            'amount'             => $pricing->price,
            'status'             => 'pending_vendor_response',
        ]);

        return response()->json([
            'message' => 'Service order request sent successfully',
            'data'    => $order
        ], 201);
    }

    /**
     * Step 2: Vendor views incoming requests (pending only).
     */
    public function vendorRequests()
    {
        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Not a vendor'], 403);
        }

        $requests = ServiceOrder::with('user')
            ->whereHas('serviceVendor', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })
            ->where('status', 'pending_vendor_response')
            ->get();

        return response()->json($requests);
    }

    /**
     * Step 3: Vendor responds (accept, decline, busy).
     */
    public function vendorRespond(Request $request, $id)
    {
        $request->validate([
            'action' => 'required|in:accept,decline,busy'
        ]);

        $vendor = Auth::user()->vendor;
        if (!$vendor) {
            return response()->json(['error' => 'Not a vendor'], 403);
        }

        $order = ServiceOrder::whereHas('serviceVendor', function($q) use ($vendor) {
                $q->where('vendor_id', $vendor->id);
            })
            ->findOrFail($id);

        if ($order->status !== 'pending_vendor_response') {
            return response()->json(['error' => 'This order is no longer pending'], 400);
        }

        if ($request->action === 'accept') {
            $order->status = 'awaiting_payment';
        } elseif ($request->action === 'decline') {
            $order->status = 'declined';
        } else {
            $order->status = 'vendor_busy';
        }

        $order->save();

        return response()->json(['message' => 'Order updated successfully', 'data' => $order]);
    }

    /**
     * Step 4: User initiates payment after vendor accepts.
     */
    public function initiatePayment($id)
    {
        $order = ServiceOrder::with('user')->findOrFail($id);

        if ($order->status !== 'awaiting_payment') {
            return response()->json(['error' => 'Order not ready for payment'], 400);
        }

        $tx_ref = uniqid('flw_');

        $response = Http::withToken(config('services.flutterwave.secret'))
            ->acceptJson()
            ->post(config('services.flutterwave.payment_url') . '/payments', [
                'tx_ref'       => $tx_ref,
                'amount'       => $order->amount,
                'currency'     => 'NGN',
                'redirect_url' => env('FRONTEND_URL') . '/payment-success',
                'customer' => [
                    'email' => $order->user->email,
                    'name'  => $order->user->name,
                ],
                'meta' => [
                    'service_order_id' => $order->id,
                    'type'             => 'service_order',
                ],
                'customizations' => [
                    'title'       => 'Service Vendor Payment',
                    'description' => 'Payment for Service Order #' . $order->id,
                ]
            ]);

        if (!$response->successful()) {
            return response()->json(['error' => 'Failed to initialize payment'], 500);
        }

        return response()->json($response->json());
    }

    /**
     * Step 6: User marks service as completed -> vendor gets paid.
     */
    public function markCompleted($id)
    {
        /** @var ServiceOrder $order */
        $order = ServiceOrder::with('serviceVendor.vendor')->findOrFail($id);

        if ($order->status !== 'paid') {
            return response()->json(['error' => 'Order not in paid state'], 400);
        }

        if ((int)$order->user_id !== (int)Auth::id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        DB::transaction(function () use ($order) {
            $order->status = 'completed';
            $order->save();

            $companyCut     = round(0.10 * (float)$order->amount, 2);
            $vendorEarnings = round((float)$order->amount - $companyCut, 2);

            $vendorUserId = optional($order->serviceVendor->vendor)->user_id;
            if (!$vendorUserId) {
                throw new \RuntimeException('Vendor user not found for service order #' . $order->id);
            }

            $vendorWallet = Wallet::firstOrCreate(
                ['user_id' => $vendorUserId],
                ['balance' => 0.00]
            );
            $vendorWallet->balance = (float)$vendorWallet->balance + $vendorEarnings;
            $vendorWallet->save();

            WalletTransaction::create([
                'wallet_id'     => $vendorWallet->id,
                'performed_by'  => 'system',
                'description'   => 'Payout for completed service order #' . $order->id,
                'related_type'  => 'service_order',
                'related_id'    => $order->id,
                'type'          => 'credit',
                'amount'        => $vendorEarnings,
                'ref'           => 'release_' . $order->id,
                'status'        => 'success',
            ]);
        });

        return response()->json(['message' => 'Order marked as completed. Vendor paid.']);
    }
}
