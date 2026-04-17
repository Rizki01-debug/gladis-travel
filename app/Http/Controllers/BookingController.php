<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

    // ================= FORM =================
    public function create($id)
    {
        $this->authorizeBookingAccess();

        $schedule = DepartureSchedule::with([
            'vehicle',
            'origin',
            'destination',
            'routePoints.meetingPoint'
        ])->findOrFail($id);

        // VALIDASI DATA
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

        if (!$schedule->vehicle) {
            abort(500, 'Kendaraan belum diset!');
        }

        $tariff = Tariff::latest()->first();
        if (!$tariff) {
            abort(500, 'Tarif belum tersedia!');
        }

        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        $meetingPoints = $schedule->routePoints
            ->pluck('meetingPoint')
            ->filter()
            ->map(fn($mp) => [
                'id' => $mp->id,
                'name' => $mp->name,
                'latitude' => (float) $mp->latitude,
                'longitude' => (float) $mp->longitude
            ])
            ->values();

        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        return view('booking.create', compact(
            'schedule',
            'seats',
            'meetingPoints',
            'bookedSeats',
            'tariff'
        ) + [
            'origin_lat' => (float) $schedule->origin->latitude,
            'origin_lng' => (float) $schedule->origin->longitude,
            'dest_lat' => (float) $schedule->destination->latitude,
            'dest_lng' => (float) $schedule->destination->longitude
        ]);
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeBookingAccess();

        $validated = $request->validate([
            'schedule_id' => 'required|exists:departure_schedules,id',
            'departure_date' => 'required|date|after_or_equal:today',

            'seat_id' => 'required|array|min:1',
            'seat_id.*' => 'exists:seats,id',

            'pickup_type' => 'required|in:meeting_point,pickup_location',
            'phone' => 'required|string|max:15',

            'meeting_point_id' => 'nullable|exists:meeting_points,id',
            'pickup_maps' => 'nullable|string'
        ]);

        $schedule = DepartureSchedule::findOrFail($validated['schedule_id']);

        // ================= VALIDASI H-3 =================
        $departure = Carbon::parse($validated['departure_date']);

        if (now()->diffInDays($departure, false) < 3) {
            return back()->withErrors('Booking minimal H-3 sebelum keberangkatan');
        }

        $tariff = Tariff::latest()->first();
        if (!$tariff) {
            return back()->withErrors('Tarif belum tersedia!');
        }

        try {
            return DB::transaction(function () use ($validated, $tariff, $schedule) {

                $schedule->load(['routePoints.meetingPoint', 'vehicle']);

                if ($schedule->routePoints->isEmpty()) {
                    throw new \Exception('Rute belum diset!');
                }

                if (!$schedule->vehicle) {
                    throw new \Exception('Kendaraan belum diset!');
                }

                // ================= VALIDASI PICKUP =================
                if ($validated['pickup_type'] === 'meeting_point' && empty($validated['meeting_point_id'])) {
                    throw new \Exception('Meeting point wajib dipilih!');
                }

                if ($validated['pickup_type'] === 'pickup_location' && empty($validated['pickup_maps'])) {
                    throw new \Exception('Lokasi penjemputan wajib diisi!');
                }

                // ================= VALIDASI KURSI =================
                $seats = Seat::whereIn('id', $validated['seat_id'])
                    ->lockForUpdate() // 🔥 penting untuk race condition
                    ->get();

                // ❌ VALIDASI: jumlah harus sama
                if ($seats->count() !== count($validated['seat_id'])) {
                    throw new \Exception('Data kursi tidak valid!');
                }

                foreach ($seats as $seat) {

                    // ❌ DRIVER SEAT
                    if ($seat->is_driver_seat) {
                        throw new \Exception('Kursi driver tidak bisa dibooking!');
                    }

                    // ❌ VALIDASI VEHICLE (ANTI INJECT ID)
                    if ($seat->vehicle_id !== $schedule->vehicle_id) {
                        throw new \Exception('Kursi tidak sesuai dengan kendaraan!');
                    }
                }


                // ================= CEK DOUBLE BOOKING =================
                $conflict = BookingSeat::whereIn('seat_id', $validated['seat_id'])
                    ->lockForUpdate()
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
                $pricePerSeat =
                    $tariff->base_price +
                    ($distance * $tariff->price_per_km) +
                    ($validated['pickup_type'] === 'pickup_location' ? $tariff->pickup_fee : 0);

                if ($tariff->min_price && $pricePerSeat < $tariff->min_price) {
                    $pricePerSeat = $tariff->min_price;
                }

                if ($tariff->max_price && $pricePerSeat > $tariff->max_price) {
                    $pricePerSeat = $tariff->max_price;
                }

                $totalRaw = $pricePerSeat * count($validated['seat_id']);

                // 🔥 PEMBULATAN KE RIBUAN
                $totalPrice = ceil($totalRaw / 1000) * 1000;

                // ================= CREATE BOOKING =================
                $booking = Booking::create([
                    'user_id' => Auth::id(),
                    'schedule_id' => $validated['schedule_id'],
                    'departure_date' => $validated['departure_date'],
                    'pickup_type' => $validated['pickup_type'],
                    'meeting_point_id' => $validated['meeting_point_id'] ?? null,
                    'pickup_maps' => $validated['pickup_maps'] ?? null,
                    'phone' => $validated['phone'],
                    'distance_km' => $distance,
                    'price_estimation' => $totalPrice,
                    'status' => 'pending'
                ]);

                // ================= INSERT SEATS =================
                BookingSeat::insert(
                    collect($validated['seat_id'])->map(fn($seatId) => [
                        'booking_id' => $booking->id,
                        'seat_id' => $seatId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ])->toArray()
                );

                // ================= DRIVER EARNING =================
                if ($schedule->vehicle->driver_id) {
                    DriverEarning::firstOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'driver_id' => $schedule->vehicle->driver_id,
                            'amount' => $totalPrice,
                            'status' => 'unpaid'
                        ]
                    );
                }

                logActivity('Booking', 'Booking ID: ' . $booking->id);

                return redirect()->route('booking.my')
                    ->with('success', 'Booking berhasil!');
            });
        } catch (\Throwable $e) {
            return back()->withErrors('ERROR: ' . $e->getMessage())->withInput();
        }
    }

    // ================= MY BOOKING =================
    public function myBooking()
    {
        $this->authorizeBookingAccess();

        $bookings = Booking::with([
            'schedule.origin',
            'schedule.destination',
            'seats',
            'meetingPoint'
        ])
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('booking.my', compact('bookings'));
    }

    // ================= CANCEL =================
    public function cancel($id)
    {
        $this->authorizeBookingAccess();

        $booking = Booking::where('user_id', Auth::id())
            ->findOrFail($id);

        if (!$booking->canBeCancelled()) {
            return back()->withErrors('Booking tidak bisa dibatalkan');
        }

        $booking->update([
            'status' => 'cancelled'
        ]);

        logActivity('Cancel Booking', 'Booking ID: ' . $booking->id);

        return back()->with('success', 'Booking berhasil dibatalkan');
    }
}
