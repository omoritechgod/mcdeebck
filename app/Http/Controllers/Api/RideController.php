<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideRating;
use App\Models\RideSetting;
use App\Models\Rider;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(name="Ride Hailing")
 */
class RideController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/ride/request",
     *     summary="Request a ride",
     *     tags={"Ride Hailing"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="pickup_lat", type="number"),
     *             @OA\Property(property="pickup_lng", type="number"),
     *             @OA\Property(property="dropoff_lat", type="number"),
     *             @OA\Property(property="dropoff_lng", type="number")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Ride created"
     *     )
     * )
     */
    public function requestRide(Request $request)
    {
        $request->validate([
            'pickup_lat' => 'required|numeric',
            'pickup_lng' => 'required|numeric',
            'dropoff_lat' => 'required|numeric',
            'dropoff_lng' => 'required|numeric',
        ]);

        $settings = RideSetting::first();
        if (!$settings) {
            return response()->json([
                'message' => 'Ride settings not configured'
            ], 500);
        }

        // Find an available rider
        $pickupLat = $request->pickup_lat;
        $pickupLng = $request->pickup_lng;

        $rider = Rider::select([
            'id',
            'user_id',
            'current_lat',
            'current_lng',
           DB::raw("(6371 * acos(cos(radians($pickupLat)) 
            * cos(radians(current_lat)) 
            * cos(radians(current_lng) - radians($pickupLng)) 
            + sin(radians($pickupLat)) 
            * sin(radians(current_lat)))) AS distance")
        ])
        ->where('availability', 'online')
        ->where('status', 'active')
        ->orderBy('distance', 'asc')
        ->first();

        if (!$rider) {
            return response()->json([
                'message' => 'No riders available nearby. Please try again later.'
            ], 404);
        }

        // Calculate distance between pickup and dropoff (Haversine formula)
        $theta = $request->pickup_lng - $request->dropoff_lng;
        $dist = sin(deg2rad($request->pickup_lat)) * sin(deg2rad($request->dropoff_lat))
            + cos(deg2rad($request->pickup_lat)) * cos(deg2rad($request->dropoff_lat)) * cos(deg2rad($theta));
        $dist = acos($dist);
        $dist = rad2deg($dist);
        $distanceKm = $dist * 60 * 1.1515 * 1.609344;

        // Fare calculation
        $fare = $settings->base_fare + ($distanceKm * $settings->rate_per_km);


        $ride = Ride::create([
            'user_id' => Auth::id(),
            'rider_id' => $rider->id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'status' => 'booked',
            'fare' => round($fare, 2),
            'distance' => round($distanceKm, 2),

        ]);

        $rider->update(['status' => 'booked']);

        return response()->json([
            'message' => 'Ride created successfully',
            'ride' => $ride,
            'assigned_rider' => $rider
        ]);
    }

    /**
     * @OA\Put(
     *     path="/api/ride/update-status",
     *     summary="Update ride status",
     *     tags={"Ride Hailing"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ride_id", type="integer"),
     *             @OA\Property(property="status", type="string", enum={"on_trip", "completed", "cancelled"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status updated")
     * )
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'status' => 'required|in:on_trip,completed,cancelled',
        ]);

        $ride = Ride::findOrFail($request->ride_id);
        $ride->status = $request->status;
        $ride->save();

        if (in_array($request->status, ['completed', 'cancelled'])) {
            return response()->json([
                "message" => "Ride marked as {$request->status}",
                $ride->rider->update(['status' => 'active'])
            ]);
        }

        return response()->json([
            'message' => 'Ride status updated successfully',
            'ride' => $ride
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/ride/history",
     *     summary="Get ride history",
     *     tags={"Ride Hailing"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of rides")
     * )
     */
    public function history()
    {
        $user = Auth::user();

        $rides = Ride::where('user_id', $user->id)
            ->orWhere('rider_id', $user->id)
            ->latest()
            ->get();

        return response()->json($rides);
    }

    /**
     * @OA\Post(
     *     path="/api/ride/online",
     *     summary="Set rider online",
     *     tags={"Ride Hailing"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Rider is now online")
     * )
     */
    public function goOnline(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'current_lat' => 'required|numeric',
            'current_lng' => 'required|numeric',
        ]);

        $vendor = Vendor::find($request->vendor_id);
        if (!$vendor || strtolower(trim($vendor->category)) !== 'rider') {
            return response()->json([
                'error' => 'Unauthorized or invalid vendor category'
            ], 403);
        }

        $rider = Rider::updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'vendor_id' => $vendor->id,
                'availability' => 'online',
                'status' => 'active',
                'current_lat' => $request->current_lat,
                'current_lng' => $request->current_lng,
            ]
        );
        return response()->json([
            'message' => 'You are now online',
            'rider' => $rider
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/ride/offline",
     *     summary="Set rider offline",
     *     tags={"Ride Hailing"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Rider is now offline")
     * )
     */
    public function goOffline()
    {
        $rider = Rider::where('user_id', Auth::id())->first();

        if (!$rider) {
            return response()->json(['message' => 'Rider not found'], 404);
        }

        $rider->update(['status' => 'offline']);

        return response()->json([
            'message' => 'You are now offline'
        ], 200);
    }

    /**
     * @OA\Post(
     *     path="/api/ride/rate",
     *     summary="Rate a completed ride",
     *     tags={"Ride Hailing"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ride_id", type="integer"),
     *             @OA\Property(property="rating", type="integer"),
     *             @OA\Property(property="comment", type="string")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Rating submitted")
     * )
     */
    public function rate(Request $request)
    {
        $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $rating = RideRating::create([
            'ride_id' => $request->ride_id,
            'user_id' => Auth::id(),
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['message' => 'Rating submitted', 'data' => $rating]);
    }
}
