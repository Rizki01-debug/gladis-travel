<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\DepartureSchedule;
use App\Models\Seat;
use App\Models\MeetingPoint;

class BookingController extends Controller
{
    public function index()
    {
        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])->get();
        return view('booking.index', compact('schedules'));
    }

    public function create($id)
    {
        $schedule = DepartureSchedule::with('vehicle')->findOrFail($id);

        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        $meetingPoints = MeetingPoint::where('city_id', $schedule->origin_city_id)->get();

        // 🔥 TAMBAHAN VALIDASI FRONTEND
        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id);
        })->pluck('seat_id')->toArray();

        return view('booking.create', compact('schedule', 'seats', 'meetingPoints', 'bookedSeats'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'schedule_id' => 'required',
            'seat_id' => 'required'
        ]);

        // 🔥 VALIDASI BACKEND
        $alreadyBooked = BookingSeat::where('seat_id', $request->seat_id)
            ->whereHas('booking', function ($q) use ($request) {
                $q->where('schedule_id', $request->schedule_id);
            })
            ->exists();

        if ($alreadyBooked) {
            return back()->withErrors([
                'seat_id' => 'Kursi sudah dibooking!'
            ]);
        }

        // 🔥 AMANKAN NULL VALUE
        $pickupType = $request->pickup_type ?? 'meeting_point';

        $booking = Booking::create([
            'user_id' => Auth::id(),
            'schedule_id' => $request->schedule_id,
            'departure_date' => now(),
            'pickup_type' => $pickupType,
            'meeting_point_id' => $request->meeting_point_id ?? null,
            'pickup_maps' => $request->pickup_maps ?? null,
            'distance_km' => $request->distance_km ?? 0,
            'price_estimation' => $request->price_estimation ?? 0,
            'status' => 'pending'
        ]);

        BookingSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $request->seat_id
        ]);

        return redirect()->route('booking.index')
            ->with('success', 'Booking berhasil!');
    }
}