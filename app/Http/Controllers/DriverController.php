<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\DriverEarning;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

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

        // 🔥 MODIFIKASI: Tampilkan booking yang sudah lunas (confirmed, completed, cash_confirmed)
        // dan booking yang pending untuk dikonfirmasi cash
        $bookings = Booking::with([
            'user',
            'seats',
            'schedule.vehicle',
            'schedule.origin',
            'schedule.destination',
            'payment'
        ])
            ->where(function ($query) {
                // 🔥 Booking yang sudah lunas (online payment)
                $query->whereIn('status', ['confirmed', 'completed', 'cash_confirmed'])
                      // 🔥 Atau booking pending yang bisa dikonfirmasi cash oleh driver
                      ->orWhere('status', 'pending');
            })
            ->whereHas('schedule.vehicle', function ($q) {
                $q->where('driver_id', $this->driverId());
            })
            ->latest()
            ->paginate(10);

        // ================= STATISTIK =================
        $totalPaidBookings = Booking::whereHas('schedule.vehicle', function ($q) {
            $q->where('driver_id', $this->driverId());
        })
        ->whereIn('status', ['confirmed', 'completed', 'cash_confirmed'])
        ->count();

        $totalPendingBookings = Booking::whereHas('schedule.vehicle', function ($q) {
            $q->where('driver_id', $this->driverId());
        })
        ->where('status', 'pending')
        ->count();

        $totalEarnings = DriverEarning::where('driver_id', $this->driverId())
            ->where('status', 'paid')
            ->sum('amount');

        return view('driver.index', compact(
            'bookings',
            'totalPaidBookings',
            'totalPendingBookings',
            'totalEarnings'
        ));
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
            'schedule.destination',
            'payment'
        ])
            ->whereHas('schedule.vehicle', function ($q) {
                $q->where('driver_id', $this->driverId());
            })
            ->findOrFail($id);

        // 🔥 Cek status payment
        $isPaid = in_array($booking->status, ['confirmed', 'completed', 'cash_confirmed']);
        $payment = $booking->payment;

        return view('driver.show', compact('booking', 'isPaid', 'payment'));
    }

    // ================= TERIMA BOOKING (ONLINE PAYMENT) =================
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

                // ✅ UPDATE DRIVER EARNING (LANGSUNG PAID UNTUK ONLINE)
                DriverEarning::where('booking_id', $booking->id)
                    ->update([
                        'status' => 'paid'
                    ]);

                // ✅ CREATE TRIP
                $trip = Trip::create([
                    'booking_id' => $booking->id,
                    'driver_id' => $this->driverId(),
                    'vehicle_id' => $booking->schedule->vehicle_id,
                    'trip_status' => 'waiting'
                ]);

                logActivity('Driver Terima Booking (Online)', 'Trip ID: ' . $trip->id);

                return redirect()
                    ->route('driver.trips')
                    ->with('success', 'Booking online diterima!');
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // ================= KONFIRMASI BOOKING CASH =================
    public function confirmCash($id)
    {
        $this->authorizeDriver();

        try {
            return DB::transaction(function () use ($id) {

                $booking = Booking::with('schedule.vehicle')
                    ->where('status', 'pending')
                    ->where('payment_method', 'cash') // 🔥 HANYA CASH
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
                    'status' => 'cash_confirmed'
                ]);

                // ✅ DRIVER EARNING TETAP UNPAID (menunggu konfirmasi admin)
                // Tidak diupdate ke paid

                // ✅ CREATE TRIP
                $trip = Trip::create([
                    'booking_id' => $booking->id,
                    'driver_id' => $this->driverId(),
                    'vehicle_id' => $booking->schedule->vehicle_id,
                    'trip_status' => 'waiting'
                ]);

                logActivity('Driver Konfirmasi Cash', 'Trip ID: ' . $trip->id . ' - Menunggu konfirmasi admin');

                return redirect()
                    ->route('driver.trips')
                    ->with('success', 'Booking cash berhasil dikonfirmasi! Menunggu pembayaran dari penumpang.');
            });
        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    /* ================= TOLAK (TIDAK DIAKTIFKAN) ================= */
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
            'booking.schedule.destination',
            'booking.payment'
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
                $booking = $trip->booking;
                
                // 🔥 CEK METODE PEMBAYARAN
                if ($booking->payment_method === 'cash') {
                    // 🔥 Cash: status jadi 'completed' tapi earning tetap 'unpaid'
                    // (harus dikonfirmasi admin dulu)
                    $booking->update([
                        'status' => 'completed'
                    ]);
                    
                    // Earning tetap 'unpaid' sampai dikonfirmasi admin
                    // Tidak diupdate otomatis
                    
                    logActivity('Trip Selesai (Cash)', 'Trip ID: ' . $trip->id . ' - Menunggu konfirmasi admin');
                    
                    return back()->with('success', 'Trip selesai! Menunggu konfirmasi pembayaran dari admin.');
                    
                } else {
                    // 🔥 Online: status jadi 'completed' dan earning langsung 'paid'
                    $booking->update([
                        'status' => 'completed'
                    ]);
                    
                    // Update driver earning status jadi paid
                    DriverEarning::where('booking_id', $booking->id)
                        ->update([
                            'status' => 'paid'
                        ]);
                    
                    logActivity('Trip Selesai (Online)', 'Trip ID: ' . $trip->id);
                    
                    return back()->with('success', 'Trip selesai!');
                }
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
            ->with(['booking.user', 'booking.payment'])
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

        // 🔥 Tambahkan info cash vs online
        $cashEarnings = (clone $query)
            ->where('status', 'unpaid')
            ->whereHas('booking', function ($q) {
                $q->where('payment_method', 'cash');
            })
            ->sum('amount');

        $onlineEarnings = (clone $query)
            ->where('status', 'paid')
            ->whereHas('booking', function ($q) {
                $q->where('payment_method', 'online');
            })
            ->sum('amount');

        return view('driver.earnings.index', compact(
            'earnings',
            'total',
            'paid',
            'unpaid',
            'cashEarnings',
            'onlineEarnings'
        ));
    }

    // ================= 🔥 TAMBAH: CEK STATUS PAYMENT =================
    /**
     * Check payment status of a booking
     */
    public function checkPaymentStatus($bookingId)
    {
        $this->authorizeDriver();

        $booking = Booking::with(['payment'])
            ->whereHas('schedule.vehicle', function ($q) {
                $q->where('driver_id', $this->driverId());
            })
            ->findOrFail($bookingId);

        $isPaid = in_array($booking->status, ['confirmed', 'completed', 'cash_confirmed']);
        $payment = $booking->payment;

        return response()->json([
            'status' => 'success',
            'data' => [
                'booking_id' => $booking->id,
                'is_paid' => $isPaid,
                'status' => $booking->status,
                'payment_status' => $payment?->status,
                'payment_method' => $payment?->payment_method_label,
                'amount' => $payment?->formatted_amount,
                'paid_at' => $payment?->paid_at?->format('d M Y H:i')
            ]
        ]);
    }

    // ================= 🔥 TAMBAH: FILTER BOOKING BY PAYMENT =================
    /**
     * Get bookings filtered by payment status
     */
    public function filterByPayment(Request $request)
    {
        $this->authorizeDriver();

        $filter = $request->get('filter', 'all');

        $query = Booking::with([
            'user',
            'seats',
            'schedule.vehicle',
            'schedule.origin',
            'schedule.destination',
            'payment'
        ])
        ->whereHas('schedule.vehicle', function ($q) {
            $q->where('driver_id', $this->driverId());
        });

        // Filter berdasarkan status payment
        if ($filter === 'paid') {
            $query->whereIn('status', ['confirmed', 'completed', 'cash_confirmed']);
        } elseif ($filter === 'pending') {
            $query->where('status', 'pending');
        } elseif ($filter === 'online') {
            $query->where('status', 'confirmed');
        } elseif ($filter === 'cash') {
            $query->where('status', 'cash_confirmed');
        }

        $bookings = $query->latest()->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'status' => 'success',
                'data' => $bookings
            ]);
        }

        return view('driver.index', compact('bookings'));
    }
}