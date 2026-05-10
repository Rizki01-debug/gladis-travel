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

        if (function_exists('featureActive') && !featureActive('schedules')) {
            abort(403, 'Fitur jadwal dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $schedules = DepartureSchedule::with([
            'origin:id,name',
            'destination:id,name',
            'vehicle:id,name',
            'routePoints.meetingPoint:id,name,latitude,longitude'
        ])
            ->latest()
            ->paginate(10);

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

        // 🔥 decode JSON route_points
        $points = json_decode($request->route_points, true);

        if (!is_array($points) || count($points) < 2) {
            return back()
                ->withErrors('Minimal pilih 2 titik rute!')
                ->withInput();
        }

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|different:origin_city_id|exists:cities,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i',
        ]);

        // 🔥 VALIDASI MEETING POINT EXIST
        foreach ($points as $p) {
            if (!MeetingPoint::where('id', $p)->exists()) {
                return back()->withErrors('Meeting point tidak valid!')->withInput();
            }
        }

        // 🔥 VALIDASI DUPLICATE
        $exists = DepartureSchedule::where('vehicle_id', $validated['vehicle_id'])
            ->where('departure_time', $validated['departure_time'])
            ->exists();

        if ($exists) {
            return back()
                ->withErrors('Jadwal untuk kendaraan dan jam tersebut sudah ada!')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $schedule = DepartureSchedule::create($validated);

            $this->syncRoutePoints($schedule->id, $points);

            DB::commit();

            logActivity('Schedule', 'Tambah jadwal ID: ' . $schedule->id);

            return redirect()->route('schedules.index')
                ->with('success', 'Jadwal berhasil dibuat!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors('Gagal menyimpan jadwal')
                ->withInput();
        }
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $this->authorizeAccess();

        $schedule = DepartureSchedule::with('routePoints')->findOrFail($id);

        return view('schedules.edit', [
            'schedule' => $schedule,
            'cities' => City::orderBy('name')->get(),
            'vehicles' => Vehicle::latest()->get(),
            'meetingPoints' => MeetingPoint::orderBy('name')->get()
        ]);
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $schedule = DepartureSchedule::findOrFail($id);

        // 🔥 decode JSON
        $points = json_decode($request->route_points, true);

        if (!is_array($points) || count($points) < 2) {
            return back()
                ->withErrors('Minimal pilih 2 titik rute!')
                ->withInput();
        }

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|different:origin_city_id|exists:cities,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i',
        ]);

        // 🔥 VALIDASI DUPLICATE (exclude diri sendiri)
        $exists = DepartureSchedule::where('vehicle_id', $validated['vehicle_id'])
            ->where('departure_time', $validated['departure_time'])
            ->where('id', '!=', $schedule->id)
            ->exists();

        if ($exists) {
            return back()
                ->withErrors('Jadwal dengan kendaraan dan jam tersebut sudah digunakan!')
                ->withInput();
        }

        try {
            DB::beginTransaction();

            $schedule->update($validated);

            // reset route
            RoutePoint::where('schedule_id', $schedule->id)->delete();

            $this->syncRoutePoints($schedule->id, $points);

            DB::commit();

            logActivity('Schedule', 'Update jadwal ID: ' . $schedule->id);

            return redirect()->route('schedules.index')
                ->with('success', 'Jadwal berhasil diupdate!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()
                ->withErrors('Gagal update jadwal')
                ->withInput();
        }
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAccess();

        try {
            $schedule = DepartureSchedule::findOrFail($id);

            DB::beginTransaction();

            RoutePoint::where('schedule_id', $schedule->id)->delete();
            $schedule->delete();

            DB::commit();

            logActivity('Schedule', 'Hapus jadwal ID: ' . $id);

            return back()->with('success', 'Jadwal berhasil dihapus!');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withErrors('Gagal menghapus jadwal');
        }
    }

    // ================= HELPER =================
    private function syncRoutePoints($scheduleId, $points)
    {
        $insert = [];

        foreach ($points as $i => $pointId) {
            $insert[] = [
                'schedule_id' => $scheduleId,
                'meeting_point_id' => $pointId,
                'order' => $i + 1,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        RoutePoint::insert($insert);
    }
}