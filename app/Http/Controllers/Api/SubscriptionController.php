<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Subscription;

/**

 * @OA\Post(
 *      path="/api/subscribe",
 *      tags={"Subscription"},
 *      summary="Subscribe to the newsletter",
 *      description="Stores an email for newsletter subscription",
 *      @OA\RequestBody(
 *          required=true,
 *          @OA\JsonContent(
 *              required={"email"},
 *              @OA\Property(property="email", type="string", example="user@example.com")
 *          )
 *      ),
 *      @OA\Response(response=200, description="Subscription successful"),
 *      @OA\Response(response=422, description="Validation error")
 * )
 */
class SubscriptionController extends Controller
{
    public function subscribe(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:subscriptions,email'
        ]);

        Subscription::create([
            'email' => $request->email
        ]);

        return response()->json(['message' => 'Subscription successful'], 200);
    }
}
