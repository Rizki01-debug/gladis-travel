<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        if (!featureActive('cities')) {
            abort(403, 'Fitur kota dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $cities = City::latest()->get();

        return view('cities.index', compact('cities'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        return view('cities.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        // 🔥 VALIDASI FINAL (WAJIB UNTUK MAP)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        // 🔥 SIMPAN
        $city = City::create($validated);

        // 🔥 ACTIVITY LOG
        logActivity('City', 'Tambah kota: ' . $city->name);

        return redirect()
            ->route('cities.index')
            ->with('success', 'Kota berhasil ditambahkan!');
    }
}
