<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
use App\Models\City;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartureScheduleController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        // 🔥 role: super_admin & admin
        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        // 🔥 feature toggle
        if (!featureActive('schedules')) {
            abort(403, 'Fitur jadwal dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])
            ->latest()
            ->get();

        return view('schedules.index', compact('schedules'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        $cities = City::latest()->get();
        $vehicles = Vehicle::latest()->get();

        return view('schedules.create', compact('cities', 'vehicles'));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id|different:origin_city_id', // 🔥 FIX
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i'
        ]);

        $schedule = DepartureSchedule::create($validated);

        // 🔥 ACTIVITY LOG
        logActivity('Schedule', 'Tambah jadwal ID: ' . $schedule->id);

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal berhasil dibuat!');
    }
}