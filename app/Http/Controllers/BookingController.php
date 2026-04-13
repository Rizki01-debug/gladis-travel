<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Models\Booking;
use App\Models\BookingSeat;
use App\Models\DepartureSchedule;
use App\Models\Seat;
use App\Models\Tariff;
use App\Models\DriverEarning;

class BookingController extends Controller
{
    // ================= AUTH =================
    private function authorizeBookingAccess()
    {
        if (!Auth::check() || Auth::user()->role_id !== 4) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= LIST JADWAL =================
    public function index()
    {
        $this->authorizeBookingAccess();

        $schedules = DepartureSchedule::with([
            'origin',
            'destination',
            'vehicle'
        ])->latest()->get();

        return view('booking.index', compact('schedules'));
    }

    // ================= FORM BOOKING =================
    public function create($id)
    {
        $this->authorizeBookingAccess();

        $schedule = DepartureSchedule::with([
            'vehicle',
            'origin',
            'destination',
            'routePoints.meetingPoint' // 🔥 WAJIB
        ])->findOrFail($id);

        // ================= VALIDASI =================
        if (!$schedule->origin || !$schedule->destination) {
            abort(500, 'Origin / Destination belum diset!');
        }

        if (
            !$schedule->origin->latitude ||
            !$schedule->origin->longitude ||
            !$schedule->destination->latitude ||
            !$schedule->destination->longitude
        ) {
            abort(500, 'Koordinat kota belum diisi!');
        }

        $tariff = Tariff::latest()->first();
        if (!$tariff) {
            abort(500, 'Tarif belum tersedia!');
        }

        // ================= DATA =================
        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        // 🔥 FIX UTAMA: AMBIL DARI ROUTE
        $meetingPoints = $schedule->routePoints
            ->pluck('meetingPoint')
            ->filter()
            ->map(function ($mp) {
                return [
                    'id' => $mp->id,
                    'name' => $mp->name,
                    'latitude' => (float) $mp->latitude,
                    'longitude' => (float) $mp->longitude
                ];
            })
            ->values();

        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        return view('booking.create', [
            'schedule' => $schedule,
            'seats' => $seats,
            'meetingPoints' => $meetingPoints,
            'bookedSeats' => $bookedSeats,
            'tariff' => $tariff,

            // MAP
            'origin_lat' => (float) $schedule->origin->latitude,
            'origin_lng' => (float) $schedule->origin->longitude,
            'dest_lat' => (float) $schedule->destination->latitude,
            'dest_lng' => (float) $schedule->destination->longitude
        ]);
    }

    // ================= SIMPAN BOOKING =================
    public function store(Request $request)
    {
        $this->authorizeBookingAccess();

        $validated = $request->validate([
            'schedule_id' => 'required|exists:departure_schedules,id',
            'seat_id' => 'required|array|min:1',
            'seat_id.*' => 'exists:seats,id'
        ]);

        $tariff = Tariff::latest()->first();
        if (!$tariff) {
            return back()->withErrors('Tarif belum tersedia!');
        }

        try {
            return DB::transaction(function () use ($request, $validated, $tariff) {

                // ================= SCHEDULE =================
                $schedule = DepartureSchedule::with([
                    'routePoints.meetingPoint',
                    'vehicle'
                ])->findOrFail($validated['schedule_id']);

                if ($schedule->routePoints->isEmpty()) {
                    throw new \Exception('Rute belum diset!');
                }

                if (!$schedule->vehicle) {
                    throw new \Exception('Kendaraan belum diset!');
                }

                // ================= CEK DOUBLE SEAT =================
                $conflict = BookingSeat::whereIn('seat_id', $validated['seat_id'])
                    ->whereHas('booking', function ($q) use ($validated) {
                        $q->where('schedule_id', $validated['schedule_id'])
                            ->whereIn('status', ['pending', 'confirmed']);
                    })
                    ->exists();

                if ($conflict) {
                    throw new \Exception('Ada kursi yang sudah dibooking!');
                }

                // ================= JARAK =================
                $distance = calculateRouteDistance($schedule->routePoints);

                if ($distance <= 0) {
                    throw new \Exception('Jarak tidak valid!');
                }

                // ================= HARGA =================
                $pickupType = $request->pickup_type ?? 'meeting_point';

                $pricePerSeat =
                    (float) $tariff->base_price +
                    ($distance * (float) $tariff->price_per_km) +
                    ($pickupType === 'pickup_location' ? (float) $tariff->pickup_fee : 0);

                // MIN MAX
                if (!empty($tariff->min_price) && $pricePerSeat < $tariff->min_price) {
                    $pricePerSeat = $tariff->min_price;
                }

                if (!empty($tariff->max_price) && $pricePerSeat > $tariff->max_price) {
                    $pricePerSeat = $tariff->max_price;
                }

                $totalPrice = $pricePerSeat * count($validated['seat_id']);

                // ================= CREATE BOOKING =================
                $booking = Booking::create([
                    'user_id' => Auth::id(),
                    'schedule_id' => $validated['schedule_id'],
                    'departure_date' => now(),
                    'pickup_type' => $pickupType,
                    'meeting_point_id' => $request->meeting_point_id ?? null,
                    'pickup_maps' => $request->pickup_maps ?? null,
                    'distance_km' => $distance,
                    'price_estimation' => $totalPrice,
                    'status' => 'pending'
                ]);

                // ================= MULTI SEAT =================
                $seatData = collect($validated['seat_id'])->map(function ($seatId) use ($booking) {
                    return [
                        'booking_id' => $booking->id,
                        'seat_id' => $seatId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                })->toArray();

                BookingSeat::insert($seatData);

                // ================= DRIVER EARNING =================
                if ($schedule->vehicle->driver_id) {
                    DriverEarning::create([
                        'driver_id' => $schedule->vehicle->driver_id,
                        'booking_id' => $booking->id,
                        'amount' => $totalPrice,
                        'status' => 'unpaid'
                    ]);
                }

                logActivity('Booking', 'Booking ID: ' . $booking->id);

                return redirect()
                    ->route('booking.my')
                    ->with('success', 'Booking berhasil!');
            });
        } catch (\Exception $e) {
            return back()
                ->withErrors($e->getMessage())
                ->withInput();
        }
    }

    // ================= BOOKING SAYA =================
    public function myBooking()
    {
        $this->authorizeBookingAccess();

        $bookings = Booking::with([
            'schedule.origin',
            'schedule.destination',
            'seats'
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('booking.my', compact('bookings'));
    }
}
