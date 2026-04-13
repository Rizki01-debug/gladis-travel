<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\Transaction;
use App\Models\DriverEarning;

class DashboardController extends Controller
{
    // ================= SUPER ADMIN =================
    public function superAdmin()
    {
        $totalBooking = Booking::count();
        $totalTrip = Trip::count();

        $income = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        $pendingIncome = Transaction::where('type', 'income')
            ->where('status', 'unpaid')
            ->sum('amount');

        return view('dashboard.super_admin', compact(
            'totalBooking',
            'totalTrip',
            'income',
            'pendingIncome'
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

        // 🔥 GRAFIK (PER HARI)
        $chartData = Transaction::selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

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

        // 🔥 VALIDASI (biar aman kalau belum login)
        if (!$driverId) {
            abort(403, 'Unauthorized');
        }

        // ================= TRIP =================
        $tripQuery = Trip::where('driver_id', $driverId);

        $totalTrip = (clone $tripQuery)->count();

        $completedTrip = (clone $tripQuery)
            ->where('trip_status', 'completed')
            ->count();

        // ================= EARNINGS =================
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
}