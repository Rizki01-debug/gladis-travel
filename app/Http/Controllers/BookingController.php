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
    // ================= AUTH =================
    private function authorizeBookingAccess()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isPassenger()) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= LIST JADWAL =================
    public function index()
    {
        $this->authorizeBookingAccess();

        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])
            ->latest()
            ->get();

        return view('booking.index', compact('schedules'));
    }

    // ================= FORM BOOKING =================
    public function create($id)
    {
        $this->authorizeBookingAccess();

        $schedule = DepartureSchedule::with('vehicle')->findOrFail($id);

        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        $meetingPoints = MeetingPoint::where('city_id', $schedule->origin_city_id)->get();

        // 🔥 kursi sudah dibooking
        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id);
        })->pluck('seat_id')->toArray();

        $tariff = Tariff::first();

        return view('booking.create', compact(
            'schedule',
            'seats',
            'meetingPoints',
            'bookedSeats',
            'tariff'
        ));
    }

    // ================= SIMPAN BOOKING =================
    public function store(Request $request)
    {
        $this->authorizeBookingAccess();

        $validated = $request->validate([
            'schedule_id' => 'required|exists:departure_schedules,id',
            'seat_id' => 'required|exists:seats,id'
        ]);

        // ❌ CEK BOOKING AKTIF
        $hasActiveBooking = Booking::where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'ongoing'])
            ->exists();

        if ($hasActiveBooking) {
            return back()->withErrors('Masih ada booking aktif!');
        }

        // ❌ CEK DOUBLE SEAT
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

        $pickupType = $request->pickup_type ?? 'meeting_point';

        // 🔥 AMBIL TARIF
        $tariff = Tariff::first();

        // ================= FIX HARGA =================
        $price = 0;

        if ($pickupType === 'pickup_location') {
            // dari map
            $price = $request->price_estimation ?? 0;
        } else {
            // 🔥 meeting point → kasih harga default
            $price = $tariff->base_price ?? 50000;
        }

        // ================= SIMPAN BOOKING =================
        $booking = Booking::create([
            'user_id' => Auth::id(),
            'schedule_id' => $validated['schedule_id'],
            'departure_date' => now(),
            'pickup_type' => $pickupType,
            'meeting_point_id' => $request->meeting_point_id ?? null,
            'pickup_maps' => $request->pickup_maps ?? null,
            'distance_km' => $request->distance_km ?? 0,
            'price_estimation' => $price,
            'status' => 'pending'
        ]);

        // ================= SIMPAN KURSI =================
        BookingSeat::create([
            'booking_id' => $booking->id,
            'seat_id' => $validated['seat_id']
        ]);

        return redirect()
            ->route('booking.my')
            ->with('success', 'Booking berhasil!');
    }

    // ================= BOOKING SAYA =================
    public function myBooking()
    {
        $this->authorizeBookingAccess();

        $bookings = Booking::with([
            'schedule.origin',
            'schedule.destination'
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('booking.my', compact('bookings'));
    }
}
