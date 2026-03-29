<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
use App\Models\City;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DepartureScheduleController extends Controller
{
    private function authorizeAdminOrSuperAdmin()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Akses ditolak');
        }
    }

    public function index()
    {
        $this->authorizeAdminOrSuperAdmin();

        $schedules = DepartureSchedule::with(['origin', 'destination', 'vehicle'])->get();

        return view('schedules.index', compact('schedules'));
    }

    public function create()
    {
        $this->authorizeAdminOrSuperAdmin();

        $cities = City::all();
        $vehicles = Vehicle::all();

        return view('schedules.create', compact('cities', 'vehicles'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdminOrSuperAdmin();

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required'
        ]);

        DepartureSchedule::create($validated);

        return redirect()->route('schedules.index')
            ->with('success', 'Jadwal berhasil dibuat!');
    }
}