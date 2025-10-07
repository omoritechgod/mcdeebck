<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\FoodOrder;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="Rider Food Delivery", description="Rider food delivery operations")
 */
class RiderFoodController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/rider/food/orders/available",
     *     summary="List available food delivery orders",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function availableOrders()
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $orders = FoodOrder::with([
            'items.menuItem:id,name,image,price',
            'foodVendor:id,vendor_id,business_name,location,logo,contact_phone',
            'user:id,name,phone'
        ])
        ->where('status', FoodOrder::STATUS_READY_FOR_PICKUP)
        ->whereNull('rider_id')
        ->where('delivery_method', FoodOrder::DELIVERY_METHOD_DELIVERY)
        ->where('payment_status', FoodOrder::PAYMENT_STATUS_PAID)
        ->latest()
        ->paginate(20);

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/rider/food/orders/assigned",
     *     summary="List rider's assigned food orders",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter by status",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function assignedOrders(Request $request)
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $query = FoodOrder::with([
            'items.menuItem:id,name,image,price',
            'foodVendor:id,vendor_id,business_name,location,logo,contact_phone',
            'user:id,name,phone'
        ])
        ->where('rider_id', $rider->id);

        if ($request->has('status')) {
            $query->byStatus($request->status);
        } else {
            $query->whereIn('status', [
                FoodOrder::STATUS_ASSIGNED,
                FoodOrder::STATUS_PICKED_UP,
                FoodOrder::STATUS_ON_THE_WAY
            ]);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/rider/food/orders/{id}",
     *     summary="Get food order detail",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getOrder($id)
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $order = FoodOrder::with([
            'items.menuItem',
            'foodVendor',
            'user'
        ])
        ->where('rider_id', $rider->id)
        ->findOrFail($id);

        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/rider/food/orders/{id}/accept",
     *     summary="Accept food delivery order",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order accepted")
     * )
     */
    public function acceptOrder($id)
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        if (!$rider->is_available) {
            return response()->json(['error' => 'Rider is not available'], 422);
        }

        $order = FoodOrder::where('id', $id)
            ->where('status', FoodOrder::STATUS_READY_FOR_PICKUP)
            ->whereNull('rider_id')
            ->firstOrFail();

        $order->rider_id = $rider->id;
        $order->status = FoodOrder::STATUS_ASSIGNED;
        $order->save();

        return response()->json([
            'message' => 'Order accepted successfully',
            'order' => $order->load('foodVendor', 'user')
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/rider/food/orders/{id}/pickup",
     *     summary="Mark order as picked up from vendor",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order marked as picked up")
     * )
     */
    public function markPickedUp($id)
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $order = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_ASSIGNED)
            ->findOrFail($id);

        $order->status = FoodOrder::STATUS_PICKED_UP;
        $order->save();

        return response()->json([
            'message' => 'Order marked as picked up',
            'order' => $order->fresh()
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/rider/food/orders/{id}/on-the-way",
     *     summary="Mark order as on the way to customer",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Order marked as on the way")
     * )
     */
    public function markOnTheWay($id)
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $order = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_PICKED_UP)
            ->findOrFail($id);

        $order->status = FoodOrder::STATUS_ON_THE_WAY;
        $order->save();

        return response()->json([
            'message' => 'Order marked as on the way',
            'order' => $order->fresh()
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/rider/food/orders/{id}/deliver",
     *     summary="Mark order as delivered",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="delivery_notes", type="string", example="Delivered to customer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order marked as delivered")
     * )
     */
    public function markDelivered(Request $request, $id)
    {
        $request->validate([
            'delivery_notes' => 'nullable|string|max:500'
        ]);

        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $order = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_ON_THE_WAY)
            ->findOrFail($id);

        $order->status = FoodOrder::STATUS_DELIVERED;
        $order->save();

        return response()->json([
            'message' => 'Order marked as delivered. Customer will confirm and payment will be released.',
            'order' => $order->fresh()
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/rider/food/availability",
     *     summary="Toggle rider availability status",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"is_available"},
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Availability updated")
     * )
     */
    public function toggleAvailability(Request $request)
    {
        $request->validate([
            'is_available' => 'required|boolean'
        ]);

        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $rider->is_available = $request->is_available;
        $rider->save();

        return response()->json([
            'message' => 'Availability updated successfully',
            'rider' => $rider
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/rider/food/location",
     *     summary="Update rider current location",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"latitude", "longitude"},
     *             @OA\Property(property="latitude", type="number", example=6.5244),
     *             @OA\Property(property="longitude", type="number", example=3.3792)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Location updated")
     * )
     */
    public function updateLocation(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $rider->current_latitude = $request->latitude;
        $rider->current_longitude = $request->longitude;
        $rider->save();

        return response()->json([
            'message' => 'Location updated successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/rider/food/earnings",
     *     summary="Get rider earnings from food deliveries",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function earnings()
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $completedOrders = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_COMPLETED)
            ->count();

        $totalEarnings = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_COMPLETED)
            ->sum(\DB::raw('delivery_fee + tip_amount'));

        $pendingEarnings = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_DELIVERED)
            ->sum(\DB::raw('delivery_fee + tip_amount'));

        return response()->json([
            'completed_orders' => $completedOrders,
            'total_earnings' => round($totalEarnings, 2),
            'pending_earnings' => round($pendingEarnings, 2),
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/rider/food/dashboard",
     *     summary="Get rider dashboard statistics",
     *     tags={"Rider Food Delivery"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function dashboard()
    {
        $user = Auth::user();
        $rider = Rider::where('user_id', $user->id)->first();

        if (!$rider) {
            return response()->json(['error' => 'Rider profile not found'], 404);
        }

        $activeOrders = FoodOrder::where('rider_id', $rider->id)
            ->whereIn('status', [
                FoodOrder::STATUS_ASSIGNED,
                FoodOrder::STATUS_PICKED_UP,
                FoodOrder::STATUS_ON_THE_WAY
            ])
            ->count();

        $todayDeliveries = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_DELIVERED)
            ->whereDate('updated_at', today())
            ->count();

        $todayEarnings = FoodOrder::where('rider_id', $rider->id)
            ->where('status', FoodOrder::STATUS_COMPLETED)
            ->whereDate('updated_at', today())
            ->sum(\DB::raw('delivery_fee + tip_amount'));

        $totalDeliveries = FoodOrder::where('rider_id', $rider->id)
            ->whereIn('status', [FoodOrder::STATUS_DELIVERED, FoodOrder::STATUS_COMPLETED])
            ->count();

        return response()->json([
            'active_orders' => $activeOrders,
            'today_deliveries' => $todayDeliveries,
            'today_earnings' => round($todayEarnings, 2),
            'total_deliveries' => $totalDeliveries,
            'is_available' => $rider->is_available,
        ]);
    }
}
