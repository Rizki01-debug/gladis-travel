<?php

namespace App\Http\Controllers;

use App\Models\MeetingPoint;
use App\Models\City;
use Illuminate\Http\Request;

class MeetingPointController extends Controller
{
    public function index()
    {
        $points = MeetingPoint::with('city')->get();
        return view('meeting_point.index', compact('points'));
    }

    public function create()
    {
        $cities = City::all();
        return view('meeting_point.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'city_id' => 'required',
            'name' => 'required'
        ]);

        MeetingPoint::create([
            'city_id' => $request->city_id,
            'name' => $request->name,
            'address' => $request->address,
            'google_maps_link' => $request->google_maps_link
        ]);

        return redirect()->route('meeting_points.index')
            ->with('success', 'Meeting point berhasil ditambahkan');
    }
}