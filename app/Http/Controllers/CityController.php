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

        if (!function_exists('featureActive') || !featureActive('cities')) {
            abort(403, 'Fitur kota dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $query = City::query();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // 📄 PAGINATION (WAJIB untuk view premium)
        $cities = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

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

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $city = City::create($validated);

        logActivity('City', 'Tambah kota: ' . $city->name);

        return redirect()
            ->route('cities.index')
            ->with('success', 'Kota berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $this->authorizeAccess();

        $city = City::findOrFail($id);

        return view('cities.edit', compact('city'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $city = City::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:cities,name,' . $city->id,
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $city->update($validated);

        logActivity('City', 'Update kota: ' . $city->name);

        return redirect()
            ->route('cities.index')
            ->with('success', 'Kota berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAccess();

        $city = City::findOrFail($id);

        // 🔥 OPTIONAL: CEK RELASI (BIAR AMAN)
        if (
            $city->meetingPoints()->exists() ||
            $city->originSchedules()->exists() ||
            $city->destinationSchedules()->exists()
        ) {

            return back()->withErrors('Kota tidak bisa dihapus karena masih digunakan!');
        }

        $name = $city->name;

        $city->delete();

        logActivity('City', 'Hapus kota: ' . $name);

        return back()->with('success', 'Kota berhasil dihapus!');
    }
}
