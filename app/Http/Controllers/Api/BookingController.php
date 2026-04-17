<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Jobs\SendBookingNotification;

use App\Http\Requests\BookingRequest;

class BookingController extends Controller
{
    /**
     * Store a newly created booking in storage.
     */
    public function store(BookingRequest $request)
    {
        return DB::transaction(function () use ($request) {
            // Re-validate availability to prevent double booking (Race condition protection)
            $isBooked = Booking::where('provider_id', $request->provider_id)
                ->where('date', $request->date)
                ->where('time_slot', $request->time_slot)
                ->whereIn('status', ['pending', 'approved'])
                ->lockForUpdate()
                ->exists();

            if ($isBooked) {
                return response()->json(['message' => 'This time slot is no longer available.'], 422);
            }

            $booking = Booking::create([
                'user_id' => $request->user_id,
                'provider_id' => $request->provider_id,
                'date' => $request->date,
                'time_slot' => $request->time_slot,
                'status' => 'pending',
            ]);

            // Dispatch notification job
            SendBookingNotification::dispatch($booking, 'created');

            return response()->json([
                'message' => 'Booking created successfully and is awaiting approval.',
                'booking' => $booking
            ], 210);
        });
    }

    /**
     * Update the booking status (Approve/Reject).
     */
    public function updateStatus(Request $request, Booking $booking)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $booking->update([
            'status' => $request->status
        ]);

        // Dispatch notification job
        SendBookingNotification::dispatch($booking, $request->status);

        return response()->json([
            'message' => "Booking has been {$request->status}.",
            'booking' => $booking
        ]);
    }

    /**
     * Reschedule an appointment (Bonus Feature).
     */
    public function reschedule(Request $request, Booking $booking)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'time_slot' => 'required|string',
        ]);

        return DB::transaction(function () use ($request, $booking) {
            // Verify slot availability
            $isBooked = Booking::where('provider_id', $booking->provider_id)
                ->where('date', $request->date)
                ->where('time_slot', $request->time_slot)
                ->whereIn('status', ['pending', 'approved'])
                ->where('id', '!=', $booking->id)
                ->lockForUpdate()
                ->exists();

            if ($isBooked) {
                return response()->json(['message' => 'The selected time slot is not available.'], 422);
            }

            $booking->update([
                'date' => $request->date,
                'time_slot' => $request->time_slot,
                'status' => 'pending', // Require re-approval after rescheduling
            ]);

            SendBookingNotification::dispatch($booking, 'rescheduled');

            return response()->json([
                'message' => 'Booking rescheduled successfully and awaits re-approval.',
                'booking' => $booking
            ]);
        });
    }
}
