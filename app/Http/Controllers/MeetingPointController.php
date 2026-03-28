<?php

namespace App\Http\Controllers;

use App\Models\MeetingPoint;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingPointController extends Controller
{
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $points = MeetingPoint::with('city')->get();
        return view('meeting_points.index', compact('points'));
    }

    public function create()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $cities = City::all();

        return view('meeting_points.create', compact('cities'));
    }

    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check() || !$user->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'city_id' => 'required',
            'name' => 'required|string|max:255'
        ]);

        MeetingPoint::create($validated);

        return redirect()->route('meeting_points.index')
            ->with('success', 'Meeting point berhasil ditambahkan!');
    }
}