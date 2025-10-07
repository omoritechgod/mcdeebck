<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\FoodMenu;
use App\Models\FoodOrder;
use App\Models\FoodVendor;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * @OA\Tag(name="Vendor Food", description="Food vendor order and menu management")
 */
class VendorFoodController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/vendor/food/menu",
     *     summary="Create menu item",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "category"},
     *             @OA\Property(property="name", type="string", example="Jollof Rice"),
     *             @OA\Property(property="description", type="string", example="Delicious spicy jollof rice"),
     *             @OA\Property(property="price", type="number", example=2500),
     *             @OA\Property(property="preparation_time_minutes", type="integer", example=30),
     *             @OA\Property(property="category", type="string", example="Main Course"),
     *             @OA\Property(property="image", type="string", example="https://cloudinary.com/..."),
     *             @OA\Property(property="image_urls", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="tags", type="array", @OA\Items(type="string")),
     *             @OA\Property(property="is_available", type="boolean", example=true)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Menu item created")
     * )
     */
    public function createMenu(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'preparation_time_minutes' => 'nullable|integer|min:1',
            'category' => 'required|string|max:100',
            'image' => 'nullable|string',
            'image_urls' => 'nullable|array',
            'image_urls.*' => 'string',
            'tags' => 'nullable|array',
            'tags.*' => 'string',
            'is_available' => 'nullable|boolean',
        ]);

        $vendor = Auth::user()->vendor;

        if (!$vendor || $vendor->category !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }

        $slug = Str::slug($request->name) . '-' . Str::random(6);

        $menu = FoodMenu::create([
            'vendor_id' => $vendor->id,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $request->image,
            'image_urls' => $request->input('image_urls', []),
            'preparation_time_minutes' => $request->input('preparation_time_minutes', 30),
            'category' => $request->category,
            'tags' => $request->input('tags', []),
            'is_available' => $request->input('is_available', true),
        ]);

        return response()->json([
            'message' => 'Menu item created successfully',
            'menu' => $menu
        ], 201);
    }

    /**
     * @OA\Put(
     *     path="/api/vendor/food/menu/{id}",
     *     summary="Update menu item",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="is_available", type="boolean")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Menu item updated")
     * )
     */
    public function updateMenu(Request $request, $id)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'preparation_time_minutes' => 'nullable|integer|min:1',
            'category' => 'sometimes|string|max:100',
            'image' => 'nullable|string',
            'image_urls' => 'nullable|array',
            'tags' => 'nullable|array',
            'is_available' => 'nullable|boolean',
        ]);

        $vendor = Auth::user()->vendor;
        $menu = FoodMenu::where('vendor_id', $vendor->id)->findOrFail($id);

        $menu->update($request->only([
            'name', 'description', 'price', 'preparation_time_minutes',
            'category', 'image', 'image_urls', 'tags', 'is_available'
        ]));

        if ($request->has('name')) {
            $menu->slug = Str::slug($request->name) . '-' . Str::random(6);
            $menu->save();
        }

        return response()->json([
            'message' => 'Menu item updated successfully',
            'menu' => $menu->fresh()
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/vendor/food/menu/{id}",
     *     summary="Delete menu item",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Menu item deleted")
     * )
     */
    public function deleteMenu($id)
    {
        $vendor = Auth::user()->vendor;
        $menu = FoodMenu::where('vendor_id', $vendor->id)->findOrFail($id);

        $menu->delete();

        return response()->json([
            'message' => 'Menu item deleted successfully'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/vendor/food/menu",
     *     summary="List vendor's menu items",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function listMenu(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $query = FoodMenu::where('vendor_id', $vendor->id);

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('is_available')) {
            $query->where('is_available', $request->boolean('is_available'));
        }

        $menus = $query->latest()->paginate(20);

        return response()->json($menus);
    }

    /**
     * @OA\Patch(
     *     path="/api/vendor/food/menu/{id}/availability",
     *     summary="Toggle menu item availability",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
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
    public function toggleAvailability(Request $request, $id)
    {
        $request->validate([
            'is_available' => 'required|boolean'
        ]);

        $vendor = Auth::user()->vendor;
        $menu = FoodMenu::where('vendor_id', $vendor->id)->findOrFail($id);

        $menu->is_available = $request->is_available;
        $menu->save();

        return response()->json([
            'message' => 'Menu availability updated',
            'menu' => $menu
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/vendor/food/orders",
     *     summary="List vendor's food orders",
     *     tags={"Vendor Food"},
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
    public function listOrders(Request $request)
    {
        $vendor = Auth::user()->vendor;

        $query = FoodOrder::with([
            'items.menuItem:id,name,image,price',
            'user:id,name,email,phone',
            'rider:id,user_id'
        ])
        ->where('vendor_id', $vendor->id)
        ->paid();

        if ($request->has('status')) {
            $query->byStatus($request->status);
        }

        $orders = $query->latest()->paginate(20);

        return response()->json($orders);
    }

    /**
     * @OA\Get(
     *     path="/api/vendor/food/orders/{id}",
     *     summary="Get order detail",
     *     tags={"Vendor Food"},
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
        $vendor = Auth::user()->vendor;

        $order = FoodOrder::with([
            'items.menuItem',
            'user',
            'rider.user'
        ])
        ->where('vendor_id', $vendor->id)
        ->findOrFail($id);

        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/vendor/food/orders/{id}/accept",
     *     summary="Accept food order",
     *     tags={"Vendor Food"},
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
        $vendor = Auth::user()->vendor;
        $order = FoodOrder::where('vendor_id', $vendor->id)->findOrFail($id);

        if ($order->status !== FoodOrder::STATUS_AWAITING_VENDOR) {
            return response()->json([
                'error' => 'Order cannot be accepted at this stage'
            ], 422);
        }

        $order->status = FoodOrder::STATUS_ACCEPTED;
        $order->save();

        return response()->json([
            'message' => 'Order accepted successfully',
            'order' => $order->fresh()
        ]);
    }

    /**
     * @OA\Patch(
     *     path="/api/vendor/food/orders/{id}/status",
     *     summary="Update order status",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 enum={"accepted", "preparing", "ready_for_pickup", "cancelled"}
     *             )
     *         )
     *     ),
     *     @OA\Response(response=200, description="Order status updated")
     * )
     */
    public function updateOrderStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:accepted,preparing,ready_for_pickup,cancelled',
            'cancellation_reason' => 'required_if:status,cancelled|string|max:500'
        ]);

        $vendor = Auth::user()->vendor;
        $order = FoodOrder::where('vendor_id', $vendor->id)->findOrFail($id);

        $allowedTransitions = [
            FoodOrder::STATUS_AWAITING_VENDOR => [FoodOrder::STATUS_ACCEPTED, FoodOrder::STATUS_CANCELLED],
            FoodOrder::STATUS_ACCEPTED => [FoodOrder::STATUS_PREPARING, FoodOrder::STATUS_CANCELLED],
            FoodOrder::STATUS_PREPARING => [FoodOrder::STATUS_READY_FOR_PICKUP],
        ];

        $currentStatus = $order->status;
        $newStatus = $request->status;

        if (!isset($allowedTransitions[$currentStatus]) ||
            !in_array($newStatus, $allowedTransitions[$currentStatus])) {
            return response()->json([
                'error' => "Cannot transition from {$currentStatus} to {$newStatus}"
            ], 422);
        }

        if ($newStatus === FoodOrder::STATUS_CANCELLED) {
            DB::beginTransaction();
            try {
                $order->status = FoodOrder::STATUS_CANCELLED;
                $order->save();

                DB::commit();
            } catch (\Throwable $e) {
                DB::rollBack();
                Log::error('Vendor order cancellation failed', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage()
                ]);
                return response()->json(['error' => 'Cancellation failed'], 500);
            }
        } else {
            $order->status = $newStatus;
            $order->save();
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->fresh()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/vendor/food/orders/{id}/assign-rider",
     *     summary="Assign rider to order",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"rider_id"},
     *             @OA\Property(property="rider_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rider assigned")
     * )
     */
    public function assignRider(Request $request, $id)
    {
        $request->validate([
            'rider_id' => 'required|integer|exists:riders,id'
        ]);

        $vendor = Auth::user()->vendor;
        $order = FoodOrder::where('vendor_id', $vendor->id)->findOrFail($id);

        if ($order->status !== FoodOrder::STATUS_READY_FOR_PICKUP) {
            return response()->json([
                'error' => 'Order must be ready for pickup before assigning rider'
            ], 422);
        }

        if ($order->delivery_method !== FoodOrder::DELIVERY_METHOD_DELIVERY) {
            return response()->json([
                'error' => 'Rider assignment only allowed for delivery orders'
            ], 422);
        }

        $rider = Rider::where('id', $request->rider_id)
            ->available()
            ->firstOrFail();

        $order->rider_id = $rider->id;
        $order->status = FoodOrder::STATUS_ASSIGNED;
        $order->save();

        return response()->json([
            'message' => 'Rider assigned successfully',
            'order' => $order->load('rider.user')
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/vendor/food/available-riders",
     *     summary="Get list of available riders",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function availableRiders()
    {
        $riders = Rider::with('user:id,name,phone')
            ->available()
            ->get();

        return response()->json([
            'riders' => $riders
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/vendor/food/dashboard",
     *     summary="Get vendor food dashboard statistics",
     *     tags={"Vendor Food"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function dashboard()
    {
        $vendor = Auth::user()->vendor;

        $totalOrders = FoodOrder::where('vendor_id', $vendor->id)
            ->where('payment_status', FoodOrder::PAYMENT_STATUS_PAID)
            ->count();

        $pendingOrders = FoodOrder::where('vendor_id', $vendor->id)
            ->where('status', FoodOrder::STATUS_AWAITING_VENDOR)
            ->count();

        $preparingOrders = FoodOrder::where('vendor_id', $vendor->id)
            ->whereIn('status', [
                FoodOrder::STATUS_ACCEPTED,
                FoodOrder::STATUS_PREPARING,
                FoodOrder::STATUS_READY_FOR_PICKUP
            ])
            ->count();

        $completedOrders = FoodOrder::where('vendor_id', $vendor->id)
            ->where('status', FoodOrder::STATUS_COMPLETED)
            ->count();

        $totalRevenue = FoodOrder::where('vendor_id', $vendor->id)
            ->where('status', FoodOrder::STATUS_COMPLETED)
            ->sum(DB::raw('total - commission_amount - delivery_fee - tip_amount'));

        $totalMenuItems = FoodMenu::where('vendor_id', $vendor->id)->count();
        $availableMenuItems = FoodMenu::where('vendor_id', $vendor->id)
            ->where('is_available', true)
            ->count();

        return response()->json([
            'total_orders' => $totalOrders,
            'pending_orders' => $pendingOrders,
            'preparing_orders' => $preparingOrders,
            'completed_orders' => $completedOrders,
            'total_revenue' => round($totalRevenue, 2),
            'total_menu_items' => $totalMenuItems,
            'available_menu_items' => $availableMenuItems,
        ]);
    }
}
