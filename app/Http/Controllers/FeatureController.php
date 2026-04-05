<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feature;

class FeatureController extends Controller
{
    // ================= INDEX =================
    public function index()
    {
        $features = Feature::orderBy('name')->get();

        $grouped = $features->groupBy('name');

        return view('features.index', compact('grouped'));
    }

    // ================= TOGGLE =================
    public function toggle(Request $request)
    {
        $feature = Feature::where('name', $request->name)
            ->where('role', $request->role)
            ->first();

        if ($feature) {
            $feature->is_active = !$feature->is_active;
            $feature->save();
        }

        return back()->with('success', 'Fitur berhasil diupdate');
    }
}