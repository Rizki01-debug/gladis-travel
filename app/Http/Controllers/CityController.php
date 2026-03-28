<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CityController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $cities = City::all();
        return view('cities.index', compact('cities'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        return view('cities.create');
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255'
        ]);

        City::create($validated);

        return redirect()->route('cities.index')
            ->with('success', 'Kota berhasil ditambahkan!');
    }
}