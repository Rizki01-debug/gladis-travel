<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\Transaction;
use App\Models\DriverEarning;

class DashboardController extends Controller
{
    // ================= SUPER ADMIN =================
    public function superAdmin()
    {
        // ================= SUMMARY =================
        $totalBooking = Booking::count();
        $totalTrip = Trip::count();

        // 🔥 gunakan DriverEarning sebagai sumber utama uang
        $income = DriverEarning::sum('amount');

        $pendingIncome = DriverEarning::where('status', 'unpaid')
            ->sum('amount');

        // ================= CHART BOOKING (7 HARI TERAKHIR) =================
        $bookings = Booking::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->take(-7)
            ->values();

        // ================= CHART REVENUE =================
        $revenue = DriverEarning::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->take(-7)
            ->values();

        return view('dashboard.super_admin', compact(
            'totalBooking',
            'totalTrip',
            'income',
            'pendingIncome',
            'bookings',
            'revenue'
        ));
    }

    // ================= ADMIN =================
    public function admin()
    {
        $income = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        $pendingIncome = Transaction::where('type', 'income')
            ->where('status', 'unpaid')
            ->sum('amount');

        $totalTransaction = Transaction::count();

        // ================= CHART =================
        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get()
            ->take(-7)
            ->values();

        return view('dashboard.admin', compact(
            'income',
            'pendingIncome',
            'totalTransaction',
            'chartData'
        ));
    }

    // ================= DRIVER =================
    public function driver()
    {
        $driverId = Auth::id();

        if (!$driverId) {
            abort(403, 'Unauthorized');
        }

        // ================= TRIP =================
        $tripQuery = Trip::where('driver_id', $driverId);

        $totalTrip = (clone $tripQuery)->count();

        $completedTrip = (clone $tripQuery)
            ->where('trip_status', 'completed')
            ->count();

        // ================= EARNING =================
        $earningQuery = DriverEarning::where('driver_id', $driverId);

        $totalEarning = (clone $earningQuery)->sum('amount');

        $paidEarning = (clone $earningQuery)
            ->where('status', 'paid')
            ->sum('amount');

        $unpaidEarning = (clone $earningQuery)
            ->where('status', 'unpaid')
            ->sum('amount');

        return view('dashboard.driver', compact(
            'totalTrip',
            'completedTrip',
            'totalEarning',
            'paidEarning',
            'unpaidEarning'
        ));
    }

    // ================= USER =================
    public function user()
    {
        $userId = Auth::id();

        $total = Booking::where('user_id', $userId)->count();
        $pending = Booking::where('user_id', $userId)->where('status', 'pending')->count();
        $completed = Booking::where('user_id', $userId)->where('status', 'completed')->count();
        $cancelled = Booking::where('user_id', $userId)->where('status', 'cancelled')->count();

        $latestBookings = Booking::with(['schedule.origin', 'schedule.destination'])
            ->where('user_id', $userId)
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.user', compact(
            'total',
            'pending',
            'completed',
            'cancelled',
            'latestBookings'
        ));
    }
}
