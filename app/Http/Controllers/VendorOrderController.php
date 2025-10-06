<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class VendorOrderController extends Controller
{
    /**
     * List orders for the authenticated vendor.
     */
    public function index(Request $request)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor) {
            return response()->json(['message' => 'Vendor account not found.'], 404);
        }

        $orders = Order::where('vendor_id', $vendor->id)
            ->with(['items.product', 'user'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($orders);
    }

    /**
     * View a specific order belonging to this vendor.
     */
    public function show(Request $request, Order $order)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($order->load(['items.product', 'user']));
    }

    /**
     * Vendor accepts an order (moves to awaiting_payment).
     */
    public function accept(Request $request, Order $order)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($order->status !== 'pending_vendor') {
            return response()->json(['message' => 'Order cannot be accepted in current state'], 422);
        }

        $order->status = 'awaiting_payment';
        $order->save();

        return response()->json([
            'message' => 'Order accepted. Waiting for user payment.',
            'order' => $order->fresh()->load('items.product')
        ]);
    }

    /**
     * Vendor rejects an order (marks as cancelled).
     */
    public function reject(Request $request, Order $order)
    {
        $vendor = $request->user()->vendor;

        if (! $vendor || $order->vendor_id !== $vendor->id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($order->status !== 'pending_vendor') {
            return response()->json(['message' => 'Order cannot be rejected in current state'], 422);
        }

        $order->status = 'cancelled';
        $order->save();

        return response()->json([
            'message' => 'Order rejected and cancelled.',
            'order' => $order->fresh()->load('items.product')
        ]);
    }
}
