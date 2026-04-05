<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\Transaction;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    private function authorizeDriver()
    {
        $user = Auth::user();

        // 🔥 pakai role_id
        if (!$user || $user->role_id !== 3) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= LIST BOOKING =================
    public function index()
    {
        $this->authorizeDriver();

        $bookings = Booking::with(['user', 'schedule'])
            ->where('status', 'pending')
            ->latest()
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

        if ($booking->status !== 'pending') {
            return back()->withErrors('Booking sudah diproses.');
        }

        if (!$booking->schedule || !$booking->schedule->vehicle_id) {
            return back()->withErrors('Schedule atau kendaraan tidak valid!');
        }

        // ✅ update booking
        $booking->update([
            'status' => 'ongoing'
        ]);

        // ✅ create trip
        $trip = Trip::create([
            'booking_id' => $booking->id,
            'driver_id' => Auth::id(),
            'vehicle_id' => $booking->schedule->vehicle_id,
            'trip_status' => 'ongoing',
            'start_time' => now()
        ]);

        // 🔥 LOG
        logActivity('Driver Ambil Trip', 'Trip ID: ' . $trip->id);

        return redirect()
            ->route('driver.trip.index')
            ->with('success', 'Trip dimulai!');
    }

    // ================= TOLAK =================
    public function reject($id)
    {
        $this->authorizeDriver();

        $booking = Booking::findOrFail($id);

        if ($booking->status !== 'pending') {
            return back()->withErrors('Booking sudah diproses.');
        }

        $booking->update([
            'status' => 'rejected'
        ]);

        // 🔥 LOG
        logActivity('Driver Tolak Booking', 'Booking ID: ' . $booking->id);

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

        // 🔥 CEK DOUBLE INCOME
        $alreadyExists = Transaction::where('booking_id', $trip->booking->id)
            ->where('type', 'income')
            ->exists();

        if (!$alreadyExists) {
            Transaction::create([
                'booking_id' => $trip->booking->id,
                'amount' => $trip->booking->price_estimation,
                'type' => 'income',
                'payment_method' => 'cash',
                'status' => 'unpaid'
            ]);
        }

        // 🔥 LOG
        logActivity('Trip Selesai', 'Trip ID: ' . $trip->id);

        return back()->with('success', 'Trip selesai & pemasukan tercatat!');
    }
}
