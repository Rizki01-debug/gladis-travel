<?php

namespace App\Http\Controllers;

use App\Models\DepartureSchedule;
use App\Models\City;
use App\Models\Vehicle;
use App\Models\MeetingPoint;
use App\Models\RoutePoint;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DepartureScheduleController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        // 🔥 sementara disable kalau lagi debug
        if (function_exists('featureActive') && !featureActive('schedules')) {
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

        // 🔥 SAFE DISTANCE
        foreach ($schedules as $schedule) {
            try {
                $schedule->distance_km = calculateRouteDistance($schedule->routePoints);
            } catch (\Throwable $e) {
                $schedule->distance_km = 0;
            }
        }

        return view('schedules.index', compact('schedules'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        return view('schedules.create', [
            'cities' => City::orderBy('name')->get(),
            'vehicles' => Vehicle::latest()->get(),
            'meetingPoints' => MeetingPoint::orderBy('name')->get()
        ]);
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        // ================= VALIDATION =================
        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|exists:cities,id|different:origin_city_id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i',

            'route_points' => 'required|array|min:1',
            'route_points.*' => 'exists:meeting_points,id'
        ]);

        try {
            DB::beginTransaction();

            // ================= CREATE SCHEDULE =================
            $schedule = DepartureSchedule::create([
                'origin_city_id' => $validated['origin_city_id'],
                'destination_city_id' => $validated['destination_city_id'],
                'vehicle_id' => $validated['vehicle_id'],
                'departure_time' => $validated['departure_time'],
            ]);

            // 🔥 VALIDASI HARD (kalau gagal, rollback)
            if (!$schedule) {
                throw new \Exception('Gagal menyimpan schedule');
            }

            // ================= ROUTE POINTS =================
            foreach ($validated['route_points'] as $index => $pointId) {
                RoutePoint::create([
                    'schedule_id' => $schedule->id,
                    'meeting_point_id' => $pointId,
                    'order' => $index + 1
                ]);
            }

            DB::commit();

            logActivity('Schedule', 'Tambah jadwal ID: ' . $schedule->id);

            return redirect()
                ->route('schedules.index')
                ->with('success', 'Jadwal berhasil dibuat!');
        } catch (\Throwable $e) {

            DB::rollBack();

            return back()
                ->withErrors('ERROR: ' . $e->getMessage())
                ->withInput();
        }
    }
}