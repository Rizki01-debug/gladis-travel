<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
// use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        // tampilkan jadwal (sementara semua dulu)
        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])->get();

        return view('driver.index', compact('schedules'));
    }

    public function show($id)
    {
        $schedule = DepartureSchedule::with(['bookings.user'])->findOrFail($id);

        return view('driver.show', compact('schedule'));
    }
}