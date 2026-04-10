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

        $schedules = DepartureSchedule::with([
            'origin',
            'destination',
            'vehicle',
            'routePoints.meetingPoint'
        ])->latest()->get();

        // 🔥 TAMBAH JARAK
        foreach ($schedules as $schedule) {
            $schedule->distance_km = calculateRouteDistance($schedule->routePoints);
        }

        return view('schedules.index', compact('schedules'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        $cities = City::latest()->get();
        $vehicles = Vehicle::latest()->get();

        // 🔥 TAMBAHKAN INI
        $meetingPoints = \App\Models\MeetingPoint::all();

        return view('schedules.create', compact(
            'cities',
            'vehicles',
            'meetingPoints' // 🔥 WAJIB DIKIRIM
        ));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id|different:origin_city_id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i',
            'route_points' => 'nullable|array'
        ]);

        // ✅ SIMPAN SCHEDULE
        $schedule = DepartureSchedule::create($validated);

        // 🔥 SIMPAN ROUTE POINTS
        if ($request->has('route_points')) {
            foreach ($request->route_points as $index => $pointId) {
                \App\Models\RoutePoint::create([
                    'schedule_id' => $schedule->id,
                    'meeting_point_id' => $pointId,
                    'order' => $index + 1
                ]);
            }
        }

        // 🔥 ACTIVITY LOG
        logActivity('Schedule', 'Tambah jadwal ID: ' . $schedule->id);

        return redirect()
            ->route('schedules.index')
            ->with('success', 'Jadwal berhasil dibuat!');
    }
}
