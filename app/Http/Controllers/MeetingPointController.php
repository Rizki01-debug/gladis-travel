<?php

namespace App\Http\Controllers;

use App\Models\MeetingPoint;
use App\Models\City;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MeetingPointController extends Controller
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

        $points = MeetingPoint::with('city')->get();

        return view('meeting_points.index', compact('points'));
    }

    public function create()
    {
        $this->authorizeAdmin();

        $cities = City::all();

        return view('meeting_points.create', compact('cities'));
    }

    public function store(Request $request)
    {
        $this->authorizeAdmin();

        $validated = $request->validate([
            'city_id' => 'required',
            'name' => 'required|string|max:255'
        ]);

        MeetingPoint::create($validated);

        return redirect()->route('meeting_points.index')
            ->with('success', 'Meeting point berhasil ditambahkan!');
    }
}