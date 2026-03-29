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

        if (!$user) {
            abort(403);
        }

        $request->validate([
            'theme_color' => 'nullable|string',
            'custom_color' => 'nullable|string',
            'use_custom' => 'nullable'
        ]);

        // 🔥 CEK: user pakai custom atau tidak
        if ($request->has('use_custom')) {

            $user->update([
                'theme_color' => $request->custom_color
            ]);
        } else {

            $user->update([
                'theme_color' => $request->theme_color ?? 'blue'
            ]);
        }

        return back()->with('success', 'Tema berhasil diubah!');
    }
}
