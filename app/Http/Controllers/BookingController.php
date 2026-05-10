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

        // ================= VALIDASI DASAR =================
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

        // ================= SEATS =================
        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)
            ->orderBy('seat_number')
            ->get();

        // ================= MEETING POINT =================
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

        // ================= BOOKED SEATS =================
        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        return view('booking.create', [
            'schedule' => $schedule,
            'seats' => $seats,
            'meetingPoints' => $meetingPoints,
            'bookedSeatIds' => $bookedSeats,
            'tariff' => $tariff,
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
            'departure_date' => 'required|date',

            'seat_id' => 'required|array|min:1',
            'seat_id.*' => 'distinct|exists:seats,id',

            'pickup_type' => 'required|in:meeting_point,pickup_location',
            'phone' => 'required|regex:/^62[0-9]{8,13}$/',

            'meeting_point_id' => 'nullable|exists:meeting_points,id',
            'pickup_maps' => 'nullable|string',

            'distance_km' => 'required|numeric|min:1',
            'price_total' => 'required|numeric|min:1000'
        ]);

        $schedule = DepartureSchedule::with(['vehicle', 'routePoints.meetingPoint'])
            ->findOrFail($validated['schedule_id']);

        // ================= VALIDASI H-3 =================
        if (now()->diffInDays(Carbon::parse($validated['departure_date']), false) < 3) {
            return back()->withErrors('Booking minimal H-3 sebelum keberangkatan')->withInput();
        }

        $tariff = Tariff::latest()->first();
        if (!$tariff) {
            return back()->withErrors('Tarif belum tersedia!');
        }

        try {
            return DB::transaction(function () use ($validated, $tariff, $schedule) {

                // ================= VALIDASI PICKUP =================
                if ($validated['pickup_type'] === 'meeting_point') {

                    if (empty($validated['meeting_point_id'])) {
                        throw new \Exception('Meeting point wajib dipilih!');
                    }

                    $valid = $schedule->routePoints
                        ->pluck('meetingPoint.id')
                        ->contains($validated['meeting_point_id']);

                    if (!$valid) {
                        throw new \Exception('Meeting point tidak valid!');
                    }
                }

                if ($validated['pickup_type'] === 'pickup_location') {

                    if (empty($validated['pickup_maps'])) {
                        throw new \Exception('Lokasi penjemputan wajib diisi!');
                    }

                    if (!preg_match('/^-?\d+(\.\d+)?,-?\d+(\.\d+)?$/', $validated['pickup_maps'])) {
                        throw new \Exception('Format lokasi tidak valid!');
                    }
                }

                // ================= VALIDASI KURSI =================
                $seats = Seat::whereIn('id', $validated['seat_id'])
                    ->lockForUpdate()
                    ->get();

                foreach ($seats as $seat) {

                    if ($seat->is_driver_seat ?? false) {
                        throw new \Exception('Kursi driver tidak bisa dibooking!');
                    }

                    if ($seat->vehicle_id !== $schedule->vehicle_id) {
                        throw new \Exception('Kursi tidak sesuai kendaraan!');
                    }
                }

                // ================= DOUBLE BOOKING =================
                $conflict = BookingSeat::whereIn('seat_id', $validated['seat_id'])
                    ->lockForUpdate()
                    ->whereHas('booking', function ($q) use ($validated) {
                        $q->where('schedule_id', $validated['schedule_id'])
                            ->whereIn('status', ['pending', 'confirmed']);
                    })
                    ->exists();

                if ($conflict) {
                    throw new \Exception('Kursi sudah dibooking!');
                }

                // ================= HITUNG HARGA =================
                $distance = (float) $validated['distance_km'];
                $frontendPrice = (float) $validated['price_total'];

                $pricePerSeat =
                    $tariff->base_price +
                    ($distance * $tariff->price_per_km);

                if ($validated['pickup_type'] === 'pickup_location') {
                    $pricePerSeat += $tariff->pickup_fee;
                }

                // MIN MAX GUARD
                if ($tariff->min_price && $pricePerSeat < $tariff->min_price) {
                    $pricePerSeat = $tariff->min_price;
                }

                if ($tariff->max_price && $pricePerSeat > $tariff->max_price) {
                    $pricePerSeat = $tariff->max_price;
                }

                $totalRaw = $pricePerSeat * count($validated['seat_id']);
                $serverPrice = ceil($totalRaw / 1000) * 1000;

                // // ================= ANTI MANIPULASI =================
                // if (abs($serverPrice - $frontendPrice) > 10000) {
                //     throw new \Exception('Harga tidak valid (terdeteksi manipulasi)');
                // }

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
                    'price_estimation' => $serverPrice,
                    'status' => 'pending'
                ]);

                // ================= INSERT SEATS =================
                foreach ($validated['seat_id'] as $seatId) {
                    BookingSeat::create([
                        'booking_id' => $booking->id,
                        'seat_id' => $seatId
                    ]);
                }

                // ================= DRIVER EARNING =================
                if ($schedule->vehicle->driver_id) {
                    DriverEarning::firstOrCreate(
                        ['booking_id' => $booking->id],
                        [
                            'driver_id' => $schedule->vehicle->driver_id,
                            'amount' => $serverPrice,
                            'status' => 'unpaid'
                        ]
                    );
                }

                logActivity('Booking', 'Booking ID: ' . $booking->id);

                return redirect()->route('booking.my')
                    ->with('success', 'Booking berhasil!');
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    // ================= MY BOOKING =================
    public function myBooking(Request $request)
    {
        $this->authorizeBookingAccess();

        $query = Booking::with([
            'schedule.origin',
            'schedule.destination',
            'seats',
            'meetingPoint'
        ])
            ->where('user_id', Auth::id());

        // FILTER
        if (
            $request->filled('status') &&
            in_array($request->status, ['pending', 'confirmed', 'completed', 'cancelled'])
        ) {
            $query->where('status', $request->status);
        }

        // SEARCH
        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('phone', 'like', "%$search%")
                    ->orWhereHas('schedule.origin', fn($q2) => $q2->where('name', 'like', "%$search%"))
                    ->orWhereHas('schedule.destination', fn($q2) => $q2->where('name', 'like', "%$search%"));
            });
        }

        $bookings = $query->latest()->paginate(5)->withQueryString();

        return view('booking.my', compact('bookings'));
    }

    // ================= CANCEL =================
    public function cancel($id)
    {
        $this->authorizeBookingAccess();

        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        if (!$booking->canBeCancelled()) {
            return back()->withErrors('Booking tidak bisa dibatalkan');
        }

        $booking->update(['status' => 'cancelled']);

        logActivity('Cancel Booking', 'Booking ID: ' . $booking->id);

        return back()->with('success', 'Booking berhasil dibatalkan');
    }
}
