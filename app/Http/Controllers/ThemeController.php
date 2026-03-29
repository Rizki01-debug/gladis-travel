<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ThemeController extends Controller
{
    public function update(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!Auth::check()) {
            abort(403);
        }

        $validated = $request->validate([
            'theme_color' => 'required|string'
        ]);

        $user->update([
            'theme_color' => $validated['theme_color']
        ]);

        return back()->with('success', 'Tema berhasil diubah!');
    }
}