<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Request;

class DriverController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isDriver()) {
            abort(403);
        }

        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])->get();

        return view('driver.index', compact('schedules'));
    }

    public function show($id)
    {
        $schedule = DepartureSchedule::with(['bookings.user'])->findOrFail($id);

        return view('driver.show', compact('schedule'));
    }
}
