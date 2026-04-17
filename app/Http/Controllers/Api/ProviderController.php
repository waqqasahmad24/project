<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\Booking;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ProviderController extends Controller
{
    /**
     * Display a listing of the providers.
     */
    public function index()
    {
        return response()->json(Provider::all());
    }

    /**
     * Display the specified provider.
     */
    public function show(Provider $provider)
    {
        return response()->json($provider);
    }

    /**
     * Get available slots for a provider on a specific date.
     */
    public function getAvailableSlots(Request $request, Provider $provider)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $date = Carbon::parse($request->date);
        $dayName = $date->format('l');

        // Check if provider works on this day
        if (!in_array($dayName, $provider->available_days)) {
            return response()->json([
                'message' => 'Provider does not work on this day.',
                'slots' => []
            ]);
        }

        $workingHours = $provider->working_hours;
        $start = Carbon::parse($request->date . ' ' . $workingHours['start']);
        $end = Carbon::parse($request->date . ' ' . $workingHours['end']);

        // Generate 1-hour slots
        $period = CarbonPeriod::since($start)->hours(1)->until($end->subHour());
        $allSlots = [];

        foreach ($period as $slotStart) {
            $slotEnd = $slotStart->copy()->addHour();
            $allSlots[] = $slotStart->format('H:i') . ' - ' . $slotEnd->format('H:i');
        }

        // Fetch existing bookings for this provider on this date
        $bookedSlots = Booking::where('provider_id', $provider->id)
            ->where('date', $request->date)
            ->whereIn('status', ['pending', 'approved'])
            ->pluck('time_slot')
            ->toArray();

        // Filter out booked slots
        $availableSlots = array_values(array_filter($allSlots, function ($slot) use ($bookedSlots) {
            return !in_array($slot, $bookedSlots);
        }));

        return response()->json([
            'date' => $request->date,
            'day' => $dayName,
            'available_slots' => $availableSlots
        ]);
    }
}
