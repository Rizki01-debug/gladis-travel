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

        // 🔥 FIX: hanya kursi booking aktif
        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

        // 🔥 ambil tarif terbaru
        $tariff = Tariff::latest()->first();

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

        // 🔥 ambil tarif terbaru
        $tariff = Tariff::latest()->first();

        if (!$tariff) {
            return back()->withErrors('Tarif belum tersedia!');
        }

        // 🔥 TRANSACTION (ANTI BUG)
        return DB::transaction(function () use ($request, $validated, $tariff) {

            // ❌ CEK BOOKING AKTIF
            $hasActiveBooking = Booking::where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($hasActiveBooking) {
                return back()->withErrors('Masih ada booking aktif!');
            }

            // ❌ CEK DOUBLE SEAT
            $alreadyBooked = BookingSeat::where('seat_id', $validated['seat_id'])
                ->whereHas('booking', function ($q) use ($validated) {
                    $q->where('schedule_id', $validated['schedule_id'])
                        ->whereIn('status', ['pending', 'confirmed']);
                })
                ->exists();

            if ($alreadyBooked) {
                return back()->withErrors([
                    'seat_id' => 'Kursi sudah dibooking!'
                ]);
            }

            $pickupType = $request->pickup_type ?? 'meeting_point';

            // ================= HITUNG HARGA REAL =================

            // 🔥 ambil jarak dari map
            $distance = (float) ($request->distance_km ?? 0);

            // 🔥 komponen harga
            $basePrice = (float) $tariff->base_price;
            $distancePrice = $distance * (float) $tariff->price_per_km;

            // 🔥 pickup fee hanya jika dijemput
            $pickupFee = ($pickupType === 'pickup_location')
                ? (float) $tariff->pickup_fee
                : 0;

            // 🔥 total harga
            $price = $basePrice + $distancePrice + $pickupFee;

            // 🔥 MIN PRICE (optional)
            if ($tariff->min_price && $price < $tariff->min_price) {
                $price = $tariff->min_price;
            }

            // 🔥 MAX PRICE (optional)
            if ($tariff->max_price && $price > $tariff->max_price) {
                $price = $tariff->max_price;
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
        });
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
