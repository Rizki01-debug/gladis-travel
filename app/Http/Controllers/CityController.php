<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    private function authorizeAdmin()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user || !($user->isSuperAdmin() || $user->isAdmin())) {
            abort(403, 'Akses ditolak');
        }
    }
    public function index()
    {
        $this->authorizeAdmin();

        $cities = City::all();
        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        return view('cities.create');
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        City::create($validated);

        return redirect()->route('cities.index')
            ->with('success', 'Kota berhasil ditambahkan!');
    }
}
