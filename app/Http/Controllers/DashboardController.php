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

        // Hanya hitung earning yang masih valid
        $income = DriverEarning::whereIn('status', ['paid', 'unpaid'])
            ->sum('amount');

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
        $revenue = DriverEarning::selectRaw(
            'DATE(created_at) as date, SUM(amount) as total'
        )
            ->whereIn('status', ['paid', 'unpaid'])
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

        // ================= BASE QUERY =================
        $tripQuery = Trip::where('driver_id', $driverId);

        $earningQuery = DriverEarning::where('driver_id', $driverId);

        // ================= SUMMARY =================
        $totalTrip = (clone $tripQuery)->count();

        $completedTrip = (clone $tripQuery)
            ->where('trip_status', 'completed')
            ->count();

        // Hanya hitung earning yang valid
        $totalEarning = (clone $earningQuery)
            ->whereIn('status', ['paid', 'unpaid'])
            ->sum('amount');

        $paidEarning = (clone $earningQuery)
            ->where('status', 'paid')
            ->sum('amount');

        $unpaidEarning = (clone $earningQuery)
            ->where('status', 'unpaid')
            ->sum('amount');

        // ================= CHART (7 HARI TERAKHIR) =================
        $startDate = now()->subDays(6)->startOfDay();

        // ================= TRIP CHART =================
        $tripChart = Trip::selectRaw('DATE(created_at) as date, COUNT(*) as total')
            ->where('driver_id', $driverId)
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ================= EARNING CHART =================
        $earningChart = DriverEarning::selectRaw(
            'DATE(created_at) as date, SUM(amount) as total'
        )
            ->where('driver_id', $driverId)
            ->whereIn('status', ['paid', 'unpaid'])
            ->whereDate('created_at', '>=', $startDate)
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // ================= RETURN =================
        return view('dashboard.driver', compact(
            'totalTrip',
            'completedTrip',
            'totalEarning',
            'paidEarning',
            'unpaidEarning',
            'tripChart',
            'earningChart'
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
