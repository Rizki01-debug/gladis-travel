<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isSuperAdmin()) {
            abort(403, 'Akses ditolak');
        }

        $vehicles = Vehicle::all();

        return view('vehicles.index', compact('vehicles'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isSuperAdmin()) {
            abort(403);
        }

        return view('vehicles.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isSuperAdmin()) {
            abort(403);
        }

        // ✅ VALIDASI
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:50',
            'seat_capacity' => 'required|integer|min:1'
        ]);

        // ✅ SIMPAN VEHICLE
        $vehicle = Vehicle::create([
            'name' => $validated['name'],
            'plate_number' => $validated['plate_number'],
            'seat_capacity' => $validated['seat_capacity'],
            'status' => 'active'
        ]);

        // ✅ AUTO GENERATE SEAT
        for ($i = 1; $i <= $vehicle->seat_capacity; $i++) {
            Seat::create([
                'vehicle_id' => $vehicle->id,
                'seat_number' => $i
            ]);
        }

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan!');
    }
}