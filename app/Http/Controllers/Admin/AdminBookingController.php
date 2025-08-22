<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AdminBookingController extends Controller
{
    /**
     * Fetch all bookings (any category).
     */
    public function index()
    {
        $bookings = Booking::with(['user', 'listing', 'vendor'])
            ->latest()
            ->get();

        return response()->json([
            'message' => 'All bookings fetched successfully.',
            'data'    => $bookings,
        ]);
    }

    /**
     * Fetch only apartment bookings.
     */
    public function apartmentBookings()
    {
        $bookings = Booking::with(['user', 'listing', 'vendor'])
            ->whereHas('listing.vendor', function ($query) {
                $query->where('category', 'service_apartment');
            })
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Apartment bookings fetched successfully.',
            'data'    => $bookings,
        ]);
    }

    /**
     * View a single booking.
     */
    public function show($id)
    {
        $booking = Booking::with(['user', 'listing', 'vendor'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'Booking fetched successfully.',
            'data'    => $booking,
        ]);
    }

    /**
     * Update booking status.
     */
    public function updateStatus(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,processing,paid,checked_in,checked_out,completed,cancelled,refunded',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $booking = Booking::with(['user', 'listing'])->findOrFail($id);
        $oldStatus = $booking->status;

        $booking->status = $request->status;
        $booking->save();

        // Notify user via email (optional)
        try {
            Mail::raw(
                "Hello {$booking->user->name},\n\nYour booking for '{$booking->listing->title}' has been updated from '{$oldStatus}' to '{$booking->status}'.",
                function ($message) use ($booking) {
                    $message->to($booking->user->email)
                            ->subject('Booking Status Update');
                }
            );
        } catch (\Exception $e) {
            // Optionally log email errors
        }

        return response()->json([
            'message' => "Booking status updated to {$booking->status}.",
            'data'    => $booking,
        ]);
    }
}
