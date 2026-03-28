<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\DepartureSchedule;
use App\Models\Seat;
use App\Models\MeetingPoint;
use App\Models\Tariff;

class BookingController extends Controller
{
    // 🔒 PROTECTION (Passenger + Admin)
    private function authorizeBookingAccess()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || (!$user->isPassenger() && !$user->isAdmin() && !$user->isSuperAdmin())) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeBookingAccess();

        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])->get();

        return view('booking.index', compact('schedules'));
    }

    // ================= CREATE =================
    public function create($id)
    {
        $this->authorizeBookingAccess();

        $schedule = DepartureSchedule::with('vehicle')->findOrFail($id);

        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        $meetingPoints = MeetingPoint::where('city_id', $schedule->origin_city_id)->get();

        // 🔥 kursi yang sudah dibooking
        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id);
        })->pluck('seat_id')->toArray();

        // 🔥 tarif dinamis
        $tariff = Tariff::first();

        return view('booking.create', compact(
            'schedule',
            'seats',
            'meetingPoints',
            'bookedSeats',
            'tariff'
        ));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeBookingAccess();

        $validated = $request->validate([
            'schedule_id' => 'required',
            'seat_id' => 'required'
        ]);

        // 🔥 VALIDASI BACKEND (ANTI DOUBLE BOOKING)
        $alreadyBooked = BookingSeat::where('seat_id', $validated['seat_id'])
            ->whereHas('booking', function ($q) use ($validated) {
                $q->where('schedule_id', $validated['schedule_id']);
            })
            ->exists();

        if ($alreadyBooked) {
            return back()->withErrors([
                'seat_id' => 'Kursi sudah dibooking!'
            ]);
        }

        // 🔥 default pickup
        $pickupType = $request->pickup_type ?? 'meeting_point';

        // 🔥 simpan booking
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'schedule_id' => $validated['schedule_id'],
            'departure_date' => now(),
            'pickup_type' => $pickupType,
            'meeting_point_id' => $request->meeting_point_id ?? null,
            'pickup_maps' => $request->pickup_maps ?? null,
            'distance_km' => $request->distance_km ?? 0,
            'price_estimation' => $request->price_estimation ?? 0,
            'status' => 'pending'
        ]);

        // 🔥 simpan kursi
        BookingSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $validated['seat_id']
        ]);

        return redirect()
            ->route('booking.index')
            ->with('success', 'Booking berhasil!');
    }
}