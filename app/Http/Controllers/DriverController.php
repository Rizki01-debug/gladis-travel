<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    private function authorizeDriver()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !$user->isDriver()) {
            abort(403);
        }
    }

    // ================= LIST BOOKING =================
    public function index()
    {
        $this->authorizeDriver();

        $bookings = Booking::with(['user', 'schedule'])
            ->where('status', 'pending')
            ->get();

        return view('driver.index', compact('bookings'));
    }

    // ================= DETAIL =================
    public function show($id)
    {
        $this->authorizeDriver();

        $booking = Booking::with(['user', 'schedule.vehicle'])
            ->findOrFail($id);

        return view('driver.show', compact('booking'));
    }

    // ================= KONFIRMASI =================
    public function confirm($id)
    {
        $this->authorizeDriver();

        $booking = Booking::with('schedule')->findOrFail($id);

        // 🔥 CEGAH DOUBLE TRIP
        if ($booking->status !== 'pending') {
            return back()->withErrors('Booking sudah diproses.');
        }

        // update status booking
        $booking->update([
            'status' => 'confirmed'
        ]);

        // buat trip
        Trip::create([
            'booking_id' => $booking->id,
            'driver_id' => Auth::id(),
            'vehicle_id' => $booking->schedule->vehicle_id,
            'trip_status' => 'ongoing',
            'start_time' => now()
        ]);

        return redirect()->route('driver.trip.index')
            ->with('success', 'Trip dimulai!');
    }

    // ================= TOLAK =================
    public function reject($id)
    {
        $this->authorizeDriver();

        $booking = Booking::findOrFail($id);

        $booking->update([
            'status' => 'rejected'
        ]);

        return back()->with('success', 'Booking ditolak!');
    }

    // ================= LIST TRIP =================
    public function trips()
    {
        $this->authorizeDriver();

        $trips = Trip::with(['booking.user', 'vehicle'])
            ->where('driver_id', Auth::id())
            ->latest()
            ->get();

        return view('driver.trips.index', compact('trips'));
    }

    // ================= SELESAI TRIP =================
    public function complete($id)
    {
        $this->authorizeDriver();

        $trip = Trip::with('booking')->findOrFail($id);

        if ($trip->trip_status !== 'ongoing') {
            return back()->withErrors('Trip sudah selesai.');
        }

        // ✅ update trip
        $trip->update([
            'trip_status' => 'completed',
            'end_time' => now()
        ]);

        // ✅ update booking
        $trip->booking->update([
            'status' => 'completed'
        ]);

        // 🔥 AUTO INCOME MASUK KE FINANCE
        // (sementara pakai booking langsung)

        return back()->with('success', 'Trip selesai & pemasukan tercatat!');
    }
}
