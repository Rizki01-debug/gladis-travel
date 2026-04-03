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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !($user->isAdmin() || $user->isSuperAdmin())) {
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
            'pickup_fee' => 'required|numeric|min:0', // 🔥 FIX
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        // 🔥 VALIDASI LOGIC
        if ($validated['min_price'] && $validated['max_price']) {
            if ($validated['min_price'] > $validated['max_price']) {
                return back()->withErrors('Min price tidak boleh lebih besar dari max price');
            }
        }

        Tariff::create($validated);

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

        // 🔥 VALIDASI LOGIC
        if ($validated['min_price'] && $validated['max_price']) {
            if ($validated['min_price'] > $validated['max_price']) {
                return back()->withErrors('Min price tidak boleh lebih besar dari max price');
            }
        }

        $tariff->update($validated);

        return redirect()
            ->route('tariffs.index')
            ->with('success', 'Tarif berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAdmin();

        $tariff = Tariff::findOrFail($id);

        $tariff->delete();

        return back()->with('success', 'Tarif berhasil dihapus!');
    }
}
