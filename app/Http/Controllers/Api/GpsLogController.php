<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GpsLog;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Ride Hailing")
 */
class GpsLogController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/ride/location/update",
     *     summary="Update GPS location for a ride",
     *     tags={"Ride Hailing"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="ride_id", type="integer"),
     *             @OA\Property(property="lat", type="number"),
     *             @OA\Property(property="lng", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="GPS logged")
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'ride_id' => 'required|exists:rides,id',
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        GpsLog::create([
            'ride_id' => $request->ride_id,
            'lat' => $request->lat,
            'lng' => $request->lng,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Location updated']);
    }
}
