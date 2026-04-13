<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\DriverEarning;
use Illuminate\Support\Facades\Auth;

class DriverController extends Controller
{
    private function authorizeDriver()
    {
        if (!Auth::check() || Auth::user()->role_id !== 3) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= LIST BOOKING =================
    public function index()
    {
        $this->authorizeDriver();

        $bookings = Booking::with(['user', 'schedule.vehicle'])
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

    // ================= TERIMA BOOKING =================
    public function confirm($id)
    {
        $this->authorizeDriver();

        $booking = Booking::with('schedule.vehicle')->findOrFail($id);

        if ($booking->status !== 'pending') {
            return back()->withErrors('Booking sudah diproses.');
        }

        if (!$booking->schedule || !$booking->schedule->vehicle_id) {
            return back()->withErrors('Schedule / kendaraan tidak valid!');
        }

        // ✅ update booking
        $booking->update([
            'status' => 'confirmed'
        ]);

        // ✅ buat trip (BELUM MULAI)
        $trip = Trip::create([
            'booking_id' => $booking->id,
            'driver_id' => Auth::id(),
            'vehicle_id' => $booking->schedule->vehicle_id,
            'trip_status' => 'waiting'
        ]);

        logActivity('Driver Terima Booking', 'Trip ID: ' . $trip->id);

        return redirect()
            ->route('driver.trips')
            ->with('success', 'Booking diterima!');
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

    // ================= START TRIP =================
    public function start($id)
    {
        $this->authorizeDriver();

        $trip = Trip::where('driver_id', Auth::id())->findOrFail($id);

        if ($trip->trip_status !== 'waiting') {
            return back()->withErrors('Trip tidak bisa dimulai.');
        }

        $trip->update([
            'trip_status' => 'ongoing',
            'start_time' => now()
        ]);

        return back()->with('success', 'Perjalanan dimulai!');
    }

    // ================= SELESAI TRIP =================
    public function complete($id)
    {
        $this->authorizeDriver();

        $trip = Trip::with('booking')->where('driver_id', Auth::id())->findOrFail($id);

        if ($trip->trip_status !== 'ongoing') {
            return back()->withErrors('Trip belum dimulai.');
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

        // ✅ DRIVER EARNING (FINAL FIX)
        DriverEarning::firstOrCreate(
            ['booking_id' => $trip->booking_id],
            [
                'driver_id' => $trip->driver_id,
                'amount' => $trip->booking->price_estimation,
                'status' => 'unpaid'
            ]
        );

        logActivity('Trip Selesai', 'Trip ID: ' . $trip->id);

        return back()->with('success', 'Trip selesai & pemasukan tercatat!');
    }

    // ================= DRIVER EARNINGS =================
    public function earnings()
    {
        $this->authorizeDriver();

        $driverId = Auth::id();

        // 🔥 BASE QUERY
        $query = DriverEarning::where('driver_id', $driverId);

        // 🔥 DATA LIST
        $earnings = (clone $query)
            ->with('booking') // optional biar bisa tampil detail
            ->latest()
            ->get();

        // 🔥 SUMMARY (LEBIH CEPAT)
        $total = (clone $query)->sum('amount');

        $paid = (clone $query)
            ->where('status', 'paid')
            ->sum('amount');

        $unpaid = (clone $query)
            ->where('status', 'unpaid')
            ->sum('amount');

        return view('driver.earnings.index', compact(
            'earnings',
            'total',
            'paid',
            'unpaid'
        ));
    }
}
