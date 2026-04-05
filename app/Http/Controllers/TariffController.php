<?php

namespace App\Http\Controllers;

use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TariffController extends Controller
{
    // ================= PROTECT =================
    private function authorizeAdmin()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAdmin();

        $tariffs = Tariff::latest()->get();

        return view('tariffs.index', compact('tariffs'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAdmin();

        return view('tariffs.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'pickup_fee' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        // 🔥 VALIDASI CLEAN
        if (!empty($validated['min_price']) && !empty($validated['max_price'])) {
            if ($validated['min_price'] > $validated['max_price']) {
                return back()
                    ->withErrors(['min_price' => 'Min price tidak boleh lebih besar dari max price'])
                    ->withInput();
            }
        }

        $tariff = Tariff::create($validated);

        // 🔥 ACTIVITY LOG
        logActivity('Tambah Tarif', 'Menambahkan tarif: ' . $tariff->name);

        return redirect()
            ->route('tariffs.index')
            ->with('success', 'Tarif berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $this->authorizeAdmin();

        $tariff = Tariff::findOrFail($id);

        return view('tariffs.edit', compact('tariff'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $this->authorizeAdmin();

        $tariff = Tariff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'pickup_fee' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        // 🔥 VALIDASI CLEAN
        if (!empty($validated['min_price']) && !empty($validated['max_price'])) {
            if ($validated['min_price'] > $validated['max_price']) {
                return back()
                    ->withErrors(['min_price' => 'Min price tidak boleh lebih besar dari max price'])
                    ->withInput();
            }
        }

        $tariff->update($validated);

        // 🔥 ACTIVITY LOG
        logActivity('Update Tarif', 'Update tarif ID: ' . $tariff->id);

        return redirect()
            ->route('tariffs.index')
            ->with('success', 'Tarif berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAdmin();

        $tariff = Tariff::findOrFail($id);

        $name = $tariff->name;

        $tariff->delete();

        // 🔥 ACTIVITY LOG
        logActivity('Hapus Tarif', 'Menghapus tarif: ' . $name);

        return back()->with('success', 'Tarif berhasil dihapus!');
    }
}
