<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
use App\Models\City;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartureScheduleController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])->get();

        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $cities = City::all();
        $vehicles = Vehicle::all();

        return view('schedules.create', compact('cities', 'vehicles'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'origin_city_id' => 'required',
            'destination_city_id' => 'required',
            'vehicle_id' => 'required',
            'departure_time' => 'required'
        ]);

        DepartureSchedule::create($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dibuat!');
    }
}