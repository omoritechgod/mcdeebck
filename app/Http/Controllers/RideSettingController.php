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

        $setting = RideSetting::first();
        if (!$setting) {
            $setting = new RideSetting();
        }

        $setting->base_fare = $request->base_fare;
        $setting->rate_per_km = $request->rate_per_km;
        $setting->save();

        return response()->json(['message' => 'Settings updated', 'data' => $setting]);
    }
}
