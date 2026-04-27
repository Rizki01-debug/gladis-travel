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

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|different:origin_city_id|exists:cities,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i',

            'route_points' => 'required|array|min:2',
            'route_points.*' => 'distinct|exists:meeting_points,id'
        ]);

        try {
            DB::beginTransaction();

            $schedule = DepartureSchedule::create($validated);

            $this->syncRoutePoints($schedule->id, $validated['route_points']);

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

        $validated = $request->validate([
            'origin_city_id' => 'required|exists:cities,id',
            'destination_city_id' => 'required|different:origin_city_id|exists:cities,id',
            'vehicle_id' => 'required|exists:vehicles,id',
            'departure_time' => 'required|date_format:H:i',

            'route_points' => 'required|array|min:2',
            'route_points.*' => 'distinct|exists:meeting_points,id'
        ]);

        try {
            DB::beginTransaction();

            $schedule->update($validated);

            // 🔥 RESET ROUTE POINT
            RoutePoint::where('schedule_id', $schedule->id)->delete();

            $this->syncRoutePoints($schedule->id, $validated['route_points']);

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
