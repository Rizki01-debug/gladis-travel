<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Seat;
use Illuminate\Http\Request;

class VehicleController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::all();
        return view('vehicle.index', compact('vehicles'));
    }

    public function create()
    {
        return view('vehicle.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'plate_number' => 'required',
            'seat_capacity' => 'required|integer|min:1'
        ]);

        // simpan kendaraan
        $vehicle = Vehicle::create([
            'name' => $request->name,
            'plate_number' => $request->plate_number,
            'seat_capacity' => $request->seat_capacity,
            'status' => 'active'
        ]);

        // 🔥 AUTO GENERATE SEATS
        for ($i = 1; $i <= $vehicle->seat_capacity; $i++) {
            Seat::create([
                'vehicle_id' => $vehicle->id,
                'seat_number' => $i
            ]);
        }

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan!');
    }
}