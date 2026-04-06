<?php

namespace App\Http\Controllers;

use App\Models\Tariff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TariffController extends Controller
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
        if (!featureActive('tariffs')) {
            abort(403, 'Fitur tarif dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $tariffs = Tariff::latest()->get();

        return view('tariffs.index', compact('tariffs'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        return view('tariffs.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'pickup_fee' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        // 🔥 VALIDASI AMAN
        if (
            isset($validated['min_price'], $validated['max_price']) &&
            $validated['min_price'] > $validated['max_price']
        ) {
            return back()
                ->withErrors(['min_price' => 'Min price tidak boleh lebih besar dari max price'])
                ->withInput();
        }

        $tariff = Tariff::create($validated);

        // 🔥 ACTIVITY LOG
        logActivity('Tariff', 'Tambah tarif: ' . $tariff->name);

        return redirect()
            ->route('tariffs.index')
            ->with('success', 'Tarif berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $this->authorizeAccess();

        $tariff = Tariff::findOrFail($id);

        return view('tariffs.edit', compact('tariff'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $tariff = Tariff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'pickup_fee' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        // 🔥 VALIDASI AMAN
        if (
            isset($validated['min_price'], $validated['max_price']) &&
            $validated['min_price'] > $validated['max_price']
        ) {
            return back()
                ->withErrors(['min_price' => 'Min price tidak boleh lebih besar dari max price'])
                ->withInput();
        }

        $tariff->update($validated);

        // 🔥 ACTIVITY LOG
        logActivity('Tariff', 'Update tarif ID: ' . $tariff->id);

        return redirect()
            ->route('tariffs.index')
            ->with('success', 'Tarif berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAccess();

        $tariff = Tariff::findOrFail($id);

        $name = $tariff->name;

        $tariff->delete();

        // 🔥 ACTIVITY LOG
        logActivity('Tariff', 'Hapus tarif: ' . $name);

        return back()->with('success', 'Tarif berhasil dihapus!');
    }
}
