<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VehicleController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        // 🔥 role: super_admin (1) & admin (2)
        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        // 🔥 feature toggle
        if (!featureActive('vehicles')) {
            abort(403, 'Fitur kendaraan dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $vehicles = Vehicle::latest()->get();

        return view('vehicles.index', compact('vehicles'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        return view('vehicles.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

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

        // 🔥 ACTIVITY LOG
        logActivity('Vehicle', 'Tambah kendaraan: ' . $vehicle->name);

        return redirect()
            ->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan!');
    }
}