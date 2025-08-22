<?php 

namespace App\Http\Controllers;

use App\Models\ApartmentBooking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(name="Apartment Bookings")
 */
class ApartmentBookingController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/apartment/bookings",
     *     summary="Book an apartment",
     *     tags={"Apartment Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"apartment_id", "check_in", "check_out"},
     *             @OA\Property(property="apartment_id", type="integer"),
     *             @OA\Property(property="check_in", type="string", format="date"),
     *             @OA\Property(property="check_out", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Booking created")
     * )
     */
    public function store(Request $request)
    {
        $request->validate([
            'apartment_id' => 'required|exists:apartments,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after_or_equal:check_in',
        ]);

        $booking = ApartmentBooking::create([
            'user_id' => Auth::id(),
            'apartment_id' => $request->apartment_id,
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
        ]);

        return response()->json(['message' => 'Booking created', 'booking' => $booking], 201);
    }

    /**
     * @OA\Get(
     *     path="/api/apartment/bookings",
     *     summary="Get my apartment bookings",
     *     tags={"Apartment Bookings"},
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="List of bookings")
     * )
     */
    public function index()
    {
        return response()->json(Auth::user()->apartmentBookings);
    }
}
