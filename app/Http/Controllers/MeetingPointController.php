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

        // 🔥 role: super_admin & admin
        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        // 🔥 feature toggle
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
            ->get();

        return view('meeting_points.index', compact('points'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        $cities = City::latest()->get();

        return view('meeting_points.create', compact('cities'));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'city_id' => 'required|exists:cities,id', // 🔥 FIX VALIDASI
            'name' => 'required|string|max:255'
        ]);

        $point = MeetingPoint::create($validated);

        // 🔥 ACTIVITY LOG
        logActivity('Meeting Point', 'Tambah titik: ' . $point->name);

        return redirect()
            ->route('meeting-points.index')
            ->with('success', 'Meeting point berhasil ditambahkan!');
    }
}