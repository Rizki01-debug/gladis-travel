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
    private function authorizeDriver(): void
    {
        if (!Auth::check() || Auth::user()->role_id !== 3) {
            abort(403, 'Akses ditolak');
        }
    }

    private function driverId(): int
    {
        return Auth::id();
    }

    // ================= LIST BOOKING =================
    public function index()
    {
        $this->authorizeDriver();

        $bookings = Booking::with([
            'user',
            'seats',
            'schedule.vehicle',
            'schedule.origin',
            'schedule.destination'
        ])
            ->where('status', 'pending')
            ->whereHas('schedule.vehicle', function ($q) {
                $q->where('driver_id', $this->driverId());
            })
            ->latest()
            ->paginate(10);

        return view('driver.index', compact('bookings'));
    }

    // ================= DETAIL =================
    public function show($id)
    {
        $this->authorizeDriver();

        $booking = Booking::with([
            'user',
            'seats',
            'meetingPoint',
            'schedule.vehicle',
            'schedule.origin',
            'schedule.destination'
        ])
            ->whereHas('schedule.vehicle', function ($q) {
                $q->where('driver_id', $this->driverId());
            })
            ->findOrFail($id);

        return view('driver.show', compact('booking'));
    }

    // ================= TERIMA BOOKING =================
    public function confirm($id)
    {
        $this->authorizeDriver();

        try {
            return DB::transaction(function () use ($id) {

                // 🔥 FIX: filter di query (bukan setelah ambil)
                $booking = Booking::with('schedule.vehicle')
                    ->where('status', 'pending')
                    ->whereHas('schedule.vehicle', function ($q) {
                        $q->where('driver_id', $this->driverId());
                    })
                    ->lockForUpdate()
                    ->findOrFail($id);

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
                    'driver_id' => $this->driverId(),
                    'vehicle_id' => $booking->schedule->vehicle_id,
                    'trip_status' => 'waiting'
                ]);

                logActivity('Driver Terima Booking', 'Trip ID: ' . $trip->id);

                return redirect()
                    ->route('driver.trips')
                    ->with('success', 'Booking diterima!');
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /* Pengembangan Selanjutnya */

    // ================= TOLAK =================
    // public function reject($id)
    // {
    //     $this->authorizeDriver();

    //     $booking = Booking::where('status', 'pending')
    //         ->whereHas('schedule.vehicle', function ($q) {
    //             $q->where('driver_id', $this->driverId());
    //         })
    //         ->findOrFail($id);

    //     $booking->update([
    //         'status' => 'cancelled'
    //     ]);

    //     logActivity('Driver Tolak Booking', 'Booking ID: ' . $booking->id);

    //     return back()->with('success', 'Booking ditolak!');
    // }

    // ================= LIST TRIP =================
    public function trips()
    {
        $this->authorizeDriver();

        $trips = Trip::with([
            'booking.user',
            'booking.seats',
            'booking.meetingPoint',
            'booking.schedule.origin',
            'booking.schedule.destination'
        ])
            ->where('driver_id', $this->driverId())
            ->latest()
            ->paginate(10);

        return view('driver.trips.index', compact('trips'));
    }

    // ================= START TRIP =================
    public function start($id)
    {
        $this->authorizeDriver();

        try {
            return DB::transaction(function () use ($id) {

                $trip = Trip::where('driver_id', $this->driverId())
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
        } catch (\Throwable $e) {
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
                    ->where('driver_id', $this->driverId())
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

                logActivity('Trip Selesai', 'Trip ID: ' . $trip->id);

                return back()->with('success', 'Trip selesai!');
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // ================= DRIVER EARNINGS =================

    public function earnings()
    {
        $this->authorizeDriver();

        $query = DriverEarning::where('driver_id', $this->driverId());

        $earnings = (clone $query)
            ->with(['booking.user'])
            ->latest()
            ->paginate(10);

        // Hanya hitung yang masih valid (bukan cancelled)
        $total = (clone $query)
            ->whereIn('status', ['paid', 'unpaid'])
            ->sum('amount');

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
