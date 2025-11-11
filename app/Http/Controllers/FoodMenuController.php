<?php

namespace App\Http\Controllers;

use App\Models\FoodMenu;
use App\Models\FoodVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Food Menu", description="Public Food Menu APIs for browsing")
 */
class FoodMenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/food/vendors",
     *     summary="List all live food vendors",
     *     tags={"Food Menu"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function listVendors(Request $request)
    {
        $query = FoodVendor::with('vendor.user:id,name,email')
            ->live()
            ->where('is_open', true);

        if ($request->has('cuisine')) {
            $query->whereJsonContains('cuisines', $request->cuisine);
        }

        if ($request->has('is_open')) {
            $query->where('is_open', $request->boolean('is_open'));
        }

        $vendors = $query->select([
            'id', 'vendor_id', 'business_name', 'specialty', 'cuisines',
            'location', 'latitude', 'longitude', 'estimated_preparation_time',
            'delivery_radius_km', 'minimum_order_amount', 'delivery_fee',
            'is_open', 'logo', 'average_rating', 'total_reviews', 'total_orders'
        ])->get();

        if ($request->has('latitude') && $request->has('longitude')) {
            $userLat = (float) $request->latitude;
            $userLon = (float) $request->longitude;

            $vendors = $vendors->map(function ($vendor) use ($userLat, $userLon) {
                if ($vendor->latitude && $vendor->longitude) {
                    $vendor->distance_km = $this->calculateDistance(
                        $userLat, $userLon,
                        (float) $vendor->latitude, (float) $vendor->longitude
                    );
                    $vendor->can_deliver = $vendor->distance_km <= $vendor->delivery_radius_km;
                } else {
                    $vendor->distance_km = null;
                    $vendor->can_deliver = false;
                }
                return $vendor;
            })->sortBy('distance_km')->values();
        }

        return response()->json([
            'vendors' => $vendors
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/food/vendors-with-menus",
     *     summary="List all live vendors with sample menu items",
     *     tags={"Food Menu"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function vendorsWithMenus(Request $request)
    {
        $vendors = FoodVendor::with(['menuItems' => function ($query) {
            $query->available()
                ->select([
                    'id', 'vendor_id', 'name', 'price', 'image',
                    'category', 'is_available'
                ])
                ->limit(3);
        }])
        ->live()
        ->where('is_open', true)
        ->select([
            'id', 'vendor_id', 'business_name', 'specialty', 'cuisines',
            'location', 'latitude', 'longitude', 'estimated_preparation_time',
            'delivery_radius_km', 'minimum_order_amount', 'delivery_fee',
            'is_open', 'logo', 'average_rating', 'total_reviews', 'total_orders'
        ])
        ->get();

        return response()->json([
            'vendors' => $vendors
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/food/vendors/{id}",
     *     summary="Get vendor detail with menu",
     *     tags={"Food Menu"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function getVendor($id)
    {
        // We want to fetch using vendor_id, not the model's id
        $vendor = FoodVendor::with([
            'vendor.user:id,name,email',
            'menuItems' => function ($query) {
                $query->available()->select([
                    'id', 'vendor_id', 'name', 'slug', 'description', 'price',
                    'image', 'image_urls', 'preparation_time_minutes', 'category',
                    'tags', 'is_available'
                ]);
            }
        ])
            ->live()
            ->where('vendor_id', $id)
            ->firstOrFail();

        $vendor->makeHidden(['contact_phone', 'contact_email']);

        return response()->json([
            'vendor' => $vendor,
        ]);
    }


    /**
     * @OA\Get(
     *     path="/api/food/menus",
     *     summary="Browse all available menu items",
     *     tags={"Food Menu"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index(Request $request)
    {
        $query = FoodMenu::with('foodVendor:id,vendor_id,business_name,logo')
            ->available();

        if ($request->has('category')) {
            $query->byCategory($request->category);
        }

        if ($request->has('vendor_id')) {
            $query->where('vendor_id', $request->vendor_id);
        }

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('min_price')) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->has('max_price')) {
            $query->where('price', '<=', $request->max_price);
        }

        $menus = $query->paginate(20);

        return response()->json($menus);
    }

    /**
     * @OA\Get(
     *     path="/api/food/menus/{id}",
     *     summary="Get menu item detail",
     *     tags={"Food Menu"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function show($id)
    {
        $menu = FoodMenu::with([
            'foodVendor:id,vendor_id,business_name,location,logo,estimated_preparation_time,minimum_order_amount,delivery_fee'
        ])->findOrFail($id);

        return response()->json([
            'menu' => $menu
        ]);
    }

    /**
     * Calculate distance between two coordinates (Haversine formula)
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
