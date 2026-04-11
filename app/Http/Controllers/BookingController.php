<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        $user = Auth::user();

        if (!$user || $user->role_id !== 4) {
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
            'destination'
        ])->findOrFail($id);

        // 🔥 VALIDASI DATA WAJIB
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

        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        $meetingPoints = MeetingPoint::where('city_id', $schedule->origin_city_id)
            ->select('id', 'name', 'latitude', 'longitude')
            ->get();

        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        $tariff = Tariff::latest()->first();

        if (!$tariff) {
            abort(500, 'Tarif belum tersedia!');
        }

        // 🔥 KOORDINAT UNTUK MAP
        $origin_lat = $schedule->origin->latitude;
        $origin_lng = $schedule->origin->longitude;

        $dest_lat = $schedule->destination->latitude;
        $dest_lng = $schedule->destination->longitude;

        return view('booking.create', compact(
            'schedule',
            'seats',
            'meetingPoints',
            'bookedSeats',
            'tariff',
            'origin_lat',
            'origin_lng',
            'dest_lat',
            'dest_lng'
        ));
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

                // ================= AMBIL SCHEDULE =================
                $schedule = DepartureSchedule::with('routePoints.meetingPoint')
                    ->findOrFail($validated['schedule_id']);

                if ($schedule->routePoints->isEmpty()) {
                    throw new \Exception('Rute belum diset!');
                }

                // ================= HITUNG JARAK =================
                $distance = calculateRouteDistance($schedule->routePoints);

                if ($distance <= 0) {
                    throw new \Exception('Jarak tidak valid!');
                }

                // ================= HITUNG HARGA =================
                $pickupType = $request->pickup_type ?? 'meeting_point';

                $basePrice = (float) $tariff->base_price;
                $distancePrice = $distance * (float) $tariff->price_per_km;

                $pickupFee = ($pickupType === 'pickup_location')
                    ? (float) $tariff->pickup_fee
                    : 0;

                $pricePerSeat = $basePrice + $distancePrice + $pickupFee;

                // 🔥 MIN / MAX
                if (!empty($tariff->min_price) && $pricePerSeat < $tariff->min_price) {
                    $pricePerSeat = $tariff->min_price;
                }

                if (!empty($tariff->max_price) && $pricePerSeat > $tariff->max_price) {
                    $pricePerSeat = $tariff->max_price;
                }

                // 🔥 TOTAL HARGA
                $seatCount = count($validated['seat_id']);
                $totalPrice = $pricePerSeat * $seatCount;

                // ================= SIMPAN BOOKING =================
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

                // ================= SIMPAN MULTI SEAT =================
                $seatData = [];

                foreach ($validated['seat_id'] as $seatId) {
                    $seatData[] = [
                        'booking_id' => $booking->id,
                        'seat_id' => $seatId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ];
                }

                BookingSeat::insert($seatData);

                // ================= LOG =================
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
