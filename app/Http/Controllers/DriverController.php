<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\DriverEarning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DriverController extends Controller
{
    // ================= AUTH =================
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

        $bookings = Booking::with([
            'user',
            'schedule.vehicle',
            'schedule.origin',
            'schedule.destination'
        ])
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('driver.index', compact('bookings'));
    }

    // ================= DETAIL =================
    public function show($id)
    {
        $this->authorizeDriver();

        $booking = Booking::with([
            'user',
            'schedule.vehicle',
            'schedule.origin',
            'schedule.destination',
            'seats'
        ])->findOrFail($id);

        return view('driver.show', compact('booking'));
    }

    // ================= TERIMA BOOKING =================
    public function confirm($id)
    {
        $this->authorizeDriver();

        try {
            return DB::transaction(function () use ($id) {

                $booking = Booking::lockForUpdate()
                    ->with('schedule.vehicle')
                    ->findOrFail($id);

                if ($booking->status !== 'pending') {
                    throw new \Exception('Booking sudah diproses.');
                }

                if (!$booking->schedule || !$booking->schedule->vehicle_id) {
                    throw new \Exception('Schedule / kendaraan tidak valid!');
                }

                // ❗ CEK SUDAH ADA TRIP
                if (Trip::where('booking_id', $booking->id)->exists()) {
                    throw new \Exception('Trip sudah dibuat!');
                }

                // ✅ UPDATE BOOKING
                $booking->update([
                    'status' => 'confirmed'
                ]);

                // ✅ CREATE TRIP
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
            });
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
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
            'status' => 'cancelled'
        ]);

        logActivity('Driver Tolak Booking', 'Booking ID: ' . $booking->id);

        return back()->with('success', 'Booking ditolak!');
    }

    // ================= LIST TRIP =================
    public function trips()
    {
        $this->authorizeDriver();

        $trips = Trip::with([
            'booking.user',
            'booking.schedule.origin',
            'booking.schedule.destination'
        ])
            ->where('driver_id', Auth::id())
            ->latest()
            ->get();

        return view('driver.trips.index', compact('trips'));
    }

    // ================= START TRIP =================
    public function start($id)
    {
        $this->authorizeDriver();

        try {
            return DB::transaction(function () use ($id) {

                $trip = Trip::where('driver_id', Auth::id())
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($trip->trip_status !== 'waiting') {
                    throw new \Exception('Trip tidak bisa dimulai.');
                }

                $trip->update([
                    'trip_status' => 'ongoing',
                    'start_time' => now()
                ]);

                logActivity('Trip Mulai', 'Trip ID: ' . $trip->id);

                return back()->with('success', 'Perjalanan dimulai!');
            });
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // ================= SELESAI TRIP =================
    public function complete($id)
    {
        $this->authorizeDriver();

        try {
            return DB::transaction(function () use ($id) {

                $trip = Trip::with('booking')
                    ->where('driver_id', Auth::id())
                    ->lockForUpdate()
                    ->findOrFail($id);

                if ($trip->trip_status !== 'ongoing') {
                    throw new \Exception('Trip belum dimulai.');
                }

                // ✅ UPDATE TRIP
                $trip->update([
                    'trip_status' => 'completed',
                    'end_time' => now()
                ]);

                // ✅ UPDATE BOOKING
                $trip->booking->update([
                    'status' => 'completed'
                ]);

                // ✅ DRIVER EARNING (ANTI DUPLICATE)
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
            });
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // ================= DRIVER EARNINGS =================
    public function earnings()
    {
        $this->authorizeDriver();

        $driverId = Auth::id();

        $query = DriverEarning::where('driver_id', $driverId);

        $earnings = (clone $query)
            ->with(['booking.user'])
            ->latest()
            ->get();

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
