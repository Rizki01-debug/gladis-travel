<?php

namespace App\Http\Controllers;

use App\Models\MeetingPoint;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingPointController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        if (!featureActive('meeting_points')) {
            abort(403, 'Fitur meeting point dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $points = MeetingPoint::with('city')
            ->latest()
            ->paginate(10);

        return view('meeting_points.index', compact('points'));
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

            // 🔥 FIX: pakai decimal validation lebih aman
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        try {
            $point = MeetingPoint::create([
                'city_id' => $validated['city_id'],
                'name' => trim($validated['name']),
                'address' => $validated['address'] ?? null,

                // 🔥 CAST KE FLOAT (PENTING UNTUK MAP)
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude']
            ]);

            logActivity('Meeting Point', 'Tambah titik: ' . $point->name);

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
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric'
        ]);

        try {
            $point->update([
                'city_id' => $validated['city_id'],
                'name' => trim($validated['name']),
                'address' => $validated['address'] ?? null,
                'latitude' => (float) $validated['latitude'],
                'longitude' => (float) $validated['longitude']
            ]);

            logActivity('Meeting Point', 'Update titik: ' . $point->name);

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

            // 🔥 OPTIONAL: CEK DIPAKAI DI ROUTE
            if ($point->routePoints()->exists()) {
                return back()->withErrors('Meeting point masih digunakan di rute!');
            }

            $name = $point->name;

            $point->delete();

            logActivity('Meeting Point', 'Hapus titik: ' . $name);

            return redirect()
                ->route('meeting-points.index')
                ->with('success', 'Meeting point berhasil dihapus!');
        } catch (\Throwable $e) {

            logger()->error('MeetingPoint DELETE ERROR: ' . $e->getMessage());

            return back()
                ->withErrors('Gagal menghapus data!');
        }
    }
}