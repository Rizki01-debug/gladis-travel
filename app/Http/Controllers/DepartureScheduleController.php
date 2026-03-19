<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
use App\Models\City;
use App\Models\Vehicle;
use Illuminate\Http\Request;

class DepartureScheduleController extends Controller
{
    public function index()
    {
        $schedules = DepartureSchedule::with(['vehicle', 'origin', 'destination'])->get();
        return view('schedule.index', compact('schedules'));
    }

    public function create()
    {
        $cities = City::all();
        $vehicles = Vehicle::all();

        return view('schedule.create', compact('cities', 'vehicles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'vehicle_id' => 'required',
            'origin_city_id' => 'required',
            'destination_city_id' => 'required',
            'departure_time' => 'required'
        ]);

        DepartureSchedule::create([
            'vehicle_id' => $request->vehicle_id,
            'origin_city_id' => $request->origin_city_id,
            'destination_city_id' => $request->destination_city_id,
            'departure_time' => $request->departure_time
        ]);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dibuat');
    }
}