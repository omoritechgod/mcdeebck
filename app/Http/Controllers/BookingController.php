<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Listing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Store a new booking request.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'listing_id'    => 'required|exists:listings,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date'=> 'required|date|after:check_in_date',
            'notes'         => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = Auth::user();
        $listing = Listing::with('vendor')->findOrFail($request->listing_id);

        if (!$listing->vendor) {
            return response()->json(['error' => 'Vendor for this listing not found.'], 404);
        }

        // Calculate nights & total price
        $nights = (new \DateTime($request->check_in_date))
                    ->diff(new \DateTime($request->check_out_date))
                    ->days;

        $totalPrice = $nights * $listing->price_per_night;

        $booking = Booking::create([
            'user_id'       => $user->id,
            'listing_id'    => $listing->id,
            'vendor_id'     => $listing->vendor->id,
            'check_in_date' => $request->check_in_date,
            'check_out_date'=> $request->check_out_date,
            'nights'        => $nights,
            'total_price'   => $totalPrice,
            'notes'         => $request->notes,
            'status'        => 'pending',
        ]);

        return response()->json([
            'message' => 'Booking request submitted successfully.',
            'data'    => $booking,
        ]);
    }

    /**
     * Fetch bookings for logged-in user.
     */
    public function myBookings()
    {
        $user = Auth::user();

        $bookings = Booking::with(['listing', 'vendor'])
            ->where('user_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'message' => 'My bookings fetched successfully.',
            'data'    => $bookings,
        ]);
    }

        public function checkIn($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->firstOrFail();

        if ($booking->status !== 'paid') {
            return response()->json(['error' => 'Cannot check in. Booking not paid.'], 400);
        }

        if (Carbon::now()->lt(Carbon::parse($booking->check_in_date))) {
            return response()->json(['error' => 'Check-in date has not arrived yet.'], 400);
        }

        $booking->status = 'checked_in';
        $booking->save();

        return response()->json(['message' => 'Check-in successful.', 'data' => $booking]);
    }

    public function checkOut($id)
    {
        $booking = Booking::where('id', $id)
            ->where('user_id', auth()->id())
            ->with(['listing', 'vendor'])
            ->firstOrFail();

        if ($booking->status !== 'checked_in') {
            return response()->json(['error' => 'Cannot check out. You are not checked in.'], 400);
        }

        if (Carbon::now()->lt(Carbon::parse($booking->check_out_date))) {
            return response()->json(['error' => 'Check-out date has not arrived yet.'], 400);
        }

        $booking->status = 'checked_out';
        $booking->save();

        $companyCutPercent = 10;
        $vendorAmount = $booking->total_price - (($companyCutPercent / 100) * $booking->total_price);

        // Vendor Wallet
        $wallet = \App\Models\Wallet::firstOrCreate(
            ['user_id' => $booking->vendor_id],
            ['balance' => 0.00]
        );
        $wallet->balance += $vendorAmount;
        $wallet->save();

        // Vendor Transaction
        \App\Models\WalletTransaction::create([
            'wallet_id'     => $wallet->id,
            'performed_by'  => 'vendor',
            'description'   => 'Payout for booking #' . $booking->id,
            'related_type'  => 'booking',
            'related_id'    => $booking->id,
            'type'          => 'credit',
            'amount'        => $vendorAmount,
            'ref'           => 'booking_' . $booking->id,
            'status'        => 'success',
        ]);

        // Admin Wallet (Company Cut)
        $companyCut = ($companyCutPercent / 100) * $booking->total_price;
        $adminWallet = \App\Models\AdminWallet::first();
        $adminWallet->balance += $companyCut;
        $adminWallet->save();

        // Admin Transaction
        \App\Models\AdminTransaction::create([
            'admin_wallet_id' => $adminWallet->id,
            'type'            => 'credit',
            'amount'          => $companyCut,
            'ref'             => 'booking_' . $booking->id,
            'status'          => 'success',
            'description'     => 'Company cut from booking payout',
            'related_type'    => 'booking',
            'related_id'      => $booking->id,
            'meta'            => json_encode(['source' => 'checkout_payout']),
        ]);

        return response()->json([
            'message' => 'Check-out successful. Vendor credited. Company cut recorded.',
            'data' => [
                'booking' => $booking,
                'vendor_payout' => $vendorAmount,
                'company_cut' => $companyCut
            ]
        ]);
    }

}
