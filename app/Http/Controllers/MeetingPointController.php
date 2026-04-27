<?php

namespace App\Http\Controllers;

use App\Models\MeetingPoint;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MeetingPointController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        if (!function_exists('featureActive') || !featureActive('meeting_points')) {
            abort(403, 'Fitur meeting point dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $query = MeetingPoint::with('city');

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 🔍 FILTER CITY
        if ($request->filled('city_id')) {
            $query->where('city_id', $request->city_id);
        }

        $points = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $cities = City::orderBy('name')->get();

        return view('meeting_points.index', compact('points', 'cities'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        $cities = City::orderBy('name')->get();

        return view('meeting_points.create', compact('cities'));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',

            // 🔥 VALIDASI KOORDINAT
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            DB::transaction(function () use ($validated) {

                $point = MeetingPoint::create([
                    'city_id' => $validated['city_id'],
                    'name' => trim($validated['name']),
                    'address' => $validated['address'] ?? null,
                    'latitude' => (float) $validated['latitude'],
                    'longitude' => (float) $validated['longitude']
                ]);

                logActivity('Meeting Point', 'Tambah titik: ' . $point->name);
            });

            return redirect()
                ->route('meeting-points.index')
                ->with('success', 'Meeting point berhasil ditambahkan!');
        } catch (\Throwable $e) {

            logger()->error('MeetingPoint STORE ERROR: ' . $e->getMessage());

            return back()
                ->withErrors('Gagal menyimpan data!')
                ->withInput();
        }
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $this->authorizeAccess();

        $point = MeetingPoint::findOrFail($id);
        $cities = City::orderBy('name')->get();

        return view('meeting_points.edit', compact('point', 'cities'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $point = MeetingPoint::findOrFail($id);

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180'
        ]);

        try {
            DB::transaction(function () use ($point, $validated) {

                $point->update([
                    'city_id' => $validated['city_id'],
                    'name' => trim($validated['name']),
                    'address' => $validated['address'] ?? null,
                    'latitude' => (float) $validated['latitude'],
                    'longitude' => (float) $validated['longitude']
                ]);

                logActivity('Meeting Point', 'Update titik: ' . $point->name);
            });

            return redirect()
                ->route('meeting-points.index')
                ->with('success', 'Meeting point berhasil diperbarui!');
        } catch (\Throwable $e) {

            logger()->error('MeetingPoint UPDATE ERROR: ' . $e->getMessage());

            return back()
                ->withErrors('Gagal update data!')
                ->withInput();
        }
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAccess();

        try {
            $point = MeetingPoint::findOrFail($id);

            if ($point->routePoints()->exists()) {
                return back()->withErrors('Meeting point masih digunakan di rute!');
            }

            DB::transaction(function () use ($point) {
                $name = $point->name;

                $point->delete();

                logActivity('Meeting Point', 'Hapus titik: ' . $name);
            });

            return back()->with('success', 'Meeting point berhasil dihapus!');
        } catch (\Throwable $e) {

            logger()->error('MeetingPoint DELETE ERROR: ' . $e->getMessage());

            return back()->withErrors('Gagal menghapus data!');
        }
    }
}
