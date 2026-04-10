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

        $schedule = DepartureSchedule::with('vehicle')->findOrFail($id);

        $seats = Seat::where('vehicle_id', $schedule->vehicle_id)->get();

        $meetingPoints = MeetingPoint::where('city_id', $schedule->origin_city_id)
            ->select('id', 'name', 'latitude', 'longitude')
            ->get();

        // 🔥 kursi yang sudah diambil
        $bookedSeats = BookingSeat::whereHas('booking', function ($q) use ($schedule) {
            $q->where('schedule_id', $schedule->id)
                ->whereIn('status', ['pending', 'confirmed']);
        })->pluck('seat_id')->toArray();

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

        $tariff = Tariff::latest()->first();

        if (!$tariff) {
            return back()->withErrors('Tarif belum tersedia!');
        }

        return DB::transaction(function () use ($request, $validated, $tariff) {

            // ================= CEK DOUBLE SEAT =================
            $alreadyBooked = BookingSeat::where('seat_id', $validated['seat_id'])
                ->whereHas('booking', function ($q) use ($validated) {
                    $q->where('schedule_id', $validated['schedule_id'])
                        ->whereIn('status', ['pending', 'confirmed']);
                })
                ->exists();

            if ($alreadyBooked) {
                return back()->withErrors(['seat_id' => 'Kursi sudah dibooking!']);
            }

            // ================= AMBIL ROUTE =================
            $schedule = DepartureSchedule::with('routePoints.meetingPoint')
                ->findOrFail($validated['schedule_id']);

            if ($schedule->routePoints->isEmpty()) {
                return back()->withErrors('Rute belum diset!');
            }

            // ================= HITUNG JARAK =================
            $distance = calculateRouteDistance($schedule->routePoints);

            // ================= HITUNG HARGA =================
            $pickupType = $request->pickup_type ?? 'meeting_point';

            $price = (float) $tariff->base_price +
                ($distance * (float) $tariff->price_per_km);

            if ($pickupType === 'pickup_location') {
                $price += (float) $tariff->pickup_fee;
            }

            // ================= SIMPAN BOOKING =================
            $booking = Booking::create([
                'user_id' => Auth::id(),
                'schedule_id' => $validated['schedule_id'],
                'departure_date' => now(),
                'pickup_type' => $pickupType,
                'meeting_point_id' => $request->meeting_point_id,
                'pickup_maps' => $request->pickup_maps,
                'distance_km' => $distance,
                'price_estimation' => $price,
                'status' => 'pending'
            ]);

            // ================= SIMPAN SEAT =================
            BookingSeat::create([
                'booking_id' => $booking->id,
                'seat_id' => $validated['seat_id']
            ]);

            logActivity('Booking', 'Booking ID: ' . $booking->id);

            return redirect()->route('booking.my')
                ->with('success', 'Booking berhasil!');
        });
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
