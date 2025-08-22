<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ride;
use App\Models\RideRating;
use App\Models\RideSetting;
use App\Models\Rider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Find an available rider
        $rider = Rider::where('status', 'online')->inRandomOrder()->first();

        if (!$rider) {
            return response()->json(['message' => 'No rider available'], 400);
        }

        // Fetch fare from base setting
        $settings = RideSetting::first();
        $fare = $settings ? $settings->base_fare : 500; // fallback default fare

        $ride = Ride::create([
            'user_id' => Auth::id(),
            'rider_id' => $rider->user_id,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'dropoff_lat' => $request->dropoff_lat,
            'dropoff_lng' => $request->dropoff_lng,
            'status' => 'booked',
            'fare' => $fare,
        ]);

        return response()->json(['message' => 'Ride created', 'ride' => $ride]);
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

        return response()->json(['message' => 'Status updated', 'ride' => $ride]);
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
    public function goOnline()
    {
        Rider::updateOrCreate(
            ['user_id' => Auth::id()],
            ['status' => 'online']
        );

        return response()->json(['message' => 'You are now online']);
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
        Rider::updateOrCreate(
            ['user_id' => Auth::id()],
            ['status' => 'offline']
        );

        return response()->json(['message' => 'You are now offline']);
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
