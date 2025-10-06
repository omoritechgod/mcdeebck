<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;

class AdminOrderController extends Controller
{
    /**
     * List all orders (admin view).
     */
    public function index(Request $request)
    {
        $orders = Order::with(['items.product', 'user', 'vendor'])
            ->orderBy('created_at', 'desc')
            ->paginate(30);

        return response()->json($orders);
    }

    /**
     * View one order in detail.
     */
    public function show($id)
    {
        $order = Order::with(['items.product', 'user', 'vendor'])->findOrFail($id);

        return response()->json($order);
    }

    /**
     * Mark order as disputed (admin intervention).
     */
    public function markDisputed(Order $order)
    {
        if (!in_array($order->status, ['paid', 'processing', 'shipped'])) {
            return response()->json(['message' => 'Order cannot be disputed in this state'], 422);
        }

        $order->status = 'disputed';
        $order->save();

        return response()->json([
            'message' => 'Order marked as disputed.',
            'order' => $order->fresh()->load(['items.product', 'user', 'vendor'])
        ]);
    }

    /**
     * Resolve dispute → refund user.
     * (Refund logic will be handled via PaymentController / Flutterwave API)
     */
    public function refund(Order $order)
    {
        if ($order->status !== 'disputed') {
            return response()->json(['message' => 'Only disputed orders can be refunded'], 422);
        }

        // Mark refunded here; actual refund handled in PaymentController
        $order->status = 'refunded';
        $order->save();

        return response()->json([
            'message' => 'Refund initiated for this order.',
            'order' => $order->fresh()->load(['items.product', 'user', 'vendor'])
        ]);
    }
}
