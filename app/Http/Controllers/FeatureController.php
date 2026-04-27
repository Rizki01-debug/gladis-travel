<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feature;
use Illuminate\Support\Facades\Auth;

class FeatureController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 1) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        // 🔥 AMBIL SEMUA FEATURE + GROUP
        $features = Feature::orderBy('name')
            ->orderBy('role')
            ->get()
            ->groupBy('name');

        return view('features.index', [
            'grouped' => $features
        ]);
    }

    // ================= BULK UPDATE (SAFE VERSION) =================
    public function bulkUpdate(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'features' => 'nullable|array'
        ]);

        $submitted = $request->input('features', []);

        // 🔥 AMBIL SEMUA DATA EXISTING
        $allFeatures = Feature::all();

        foreach ($allFeatures as $feature) {

            // cek apakah checkbox dikirim
            $isChecked = isset($submitted[$feature->name][$feature->role]);

            $feature->update([
                'is_active' => $isChecked ? 1 : 0
            ]);
        }

        return back()->with('success', 'Fitur berhasil diperbarui!');
    }
}