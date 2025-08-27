<?php

namespace App\Http\Controllers;

use App\Models\FoodMenu;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Food", description="Food Menu APIs")
 */
class FoodMenuController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/food/menus",
     *     summary="List all food menus",
     *     tags={"Food"},
     *     @OA\Response(response=200, description="Success")
     * )
     */
    public function index()
    {
        if (FoodMenu::count() === 0) {
            return response()->json(['message' => 'No food menus found'], 404);
        }

        return response()->json(FoodMenu::all());
    }

    /**
     * @OA\Post(
     *     path="/api/food/menus",
     *     summary="Create a food menu (Vendor only)",
     *     tags={"Food"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price"},
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number", format="float"),
     *             @OA\Property(property="image", type="string", example="https://...jpg")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Food menu created")
     * )
     */
    public function store(Request $request)
    {
        $vendor = Vendor::where('user_id', Auth::id())->first();

        if (!$vendor || trim(strtolower($vendor->category)) !== 'food_vendor') {
            return response()->json(['error' => 'Unauthorized or invalid vendor category'], 403);
        }
        $request->validate([
            'name' => 'required|string',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'image' => 'required|url',
        ]);


        $menu = FoodMenu::create([
            'vendor_id' => Auth::user()->vendor->id,
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'image' => $request->image,
        ]);

        return response()->json($menu, 201);
    }
}
