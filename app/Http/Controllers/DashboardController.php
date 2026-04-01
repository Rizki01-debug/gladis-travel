<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

use App\Models\Booking;
use App\Models\Trip;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
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

    public function admin()
    {
        $income = Transaction::where('type', 'income')
            ->where('status', 'paid')
            ->sum('amount');

        $pendingIncome = Transaction::where('type', 'income')
            ->where('status', 'unpaid')
            ->sum('amount');

        $totalTransaction = Transaction::count();

        // 🔥 DATA GRAFIK (PER HARI)
        $chartData = Transaction::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(amount) as total')
        )
            ->where('type', 'income')
            ->where('status', 'paid')
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'asc')
            ->get();

        return view('dashboard.admin', compact(
            'income',
            'pendingIncome',
            'totalTransaction',
            'chartData'
        ));
    }

    public function driver()
    {
        $userId = Auth::id();

        $totalTrip = Trip::where('driver_id', $userId)->count();

        $completedTrip = Trip::where('driver_id', $userId)
            ->where('trip_status', 'completed')
            ->count();

        return view('dashboard.driver', compact(
            'totalTrip',
            'completedTrip'
        ));
    }
}
