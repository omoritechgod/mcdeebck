<?php

namespace App\Http\Controllers;

use App\Models\RideSetting;
use Illuminate\Http\Request;

/**
 * @OA\Tag(name="Ride Hailing Settings")
 */
class RideSettingController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/ride/settings",
     *     summary="Get ride fare settings",
     *     tags={"Ride Hailing Settings"},
     *     @OA\Response(response=200, description="Returns current fare settings")
     * )
     */
    public function index()
    {
        $setting = RideSetting::first();
        return response()->json($setting);
    }

    /**
     * @OA\Put(
     *     path="/api/ride/settings",
     *     summary="Update fare settings (admin)",
     *     tags={"Ride Hailing Settings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="base_fare", type="number"),
     *             @OA\Property(property="rate_per_km", type="number")
     *         )
     *     ),
     *     @OA\Response(response=200, description="Settings updated")
     * )
     */
    public function update(Request $request)
    {
        $request->validate([
            'base_fare' => 'required|numeric',
            'rate_per_km' => 'required|numeric',
        ]);

        $settings = RideSetting::first();
       if(!$settings){
            $settings = RideSetting::create([
                'base_fare' => $request->base_fare,
                'rate_per_km' => $request->rate_per_km,
            ]);
       } else {
            $settings->update([
                'base_fare' => $request->base_fare,
                'rate_per_km' => $request->rate_per_km ?? $settings->rate_per_km
            ]);
       }

       return response()->json([
            'message' => 'Ride settings updated successfully',
            'settings' => $settings
       ]);
    }

    public function getBaseFare()
    {
        $settings = RideSetting::first();
        if(!$settings){
            return response()->json([
                'message' => 'Ride setting not configured'
            ], 400);
        }

        return response()->json([
            'base_fare' => $settings->base_fare,
            'rate_per_km' => $settings->rate_per_km,
        ]);
    }
}
