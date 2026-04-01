<?php

namespace App\Http\Controllers;

use App\Models\Tariff;
use Illuminate\Http\Request;

class TariffController extends Controller
{
    // ================= INDEX =================
    public function index()
    {
        $tariffs = Tariff::latest()->get();

        return view('tariffs.index', compact('tariffs'));
    }

    // ================= CREATE =================
    public function create()
    {
        return view('tariffs.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        Tariff::create($validated);

        return redirect()->route('tariffs.index')
            ->with('success', 'Tarif berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $tariff = Tariff::findOrFail($id);

        return view('tariffs.edit', compact('tariff'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $tariff = Tariff::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'base_price' => 'required|numeric|min:0',
            'price_per_km' => 'required|numeric|min:0',
            'pickup_fee' => 'required|numeric|min:0',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0'
        ]);

        $tariff->update($validated);

        return redirect()->route('tariffs.index')
            ->with('success', 'Tarif berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $tariff = Tariff::findOrFail($id);

        $tariff->delete();

        return back()->with('success', 'Tarif berhasil dihapus!');
    }
}
