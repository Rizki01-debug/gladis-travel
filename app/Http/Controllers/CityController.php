<?php

namespace App\Http\Controllers;

use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::all();
        return view('city.index', compact('cities'));
    }

    public function create()
    {
        return view('city.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required'
        ]);

        City::create([
            'name' => $request->name
        ]);

        return redirect()->route('cities.index')
            ->with('success', 'Kota berhasil ditambahkan');
    }
}