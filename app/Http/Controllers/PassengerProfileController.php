<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class PassengerProfileController extends Controller
{
    // ================= AUTH =================
    private function authorizePassenger()
    {
        if (!Auth::check() || Auth::user()->role_id !== 4) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= EDIT =================
    public function edit()
    {
        $this->authorizePassenger();

        /** @var User $user */
        $user = Auth::user();

        return view('profile.passenger', compact('user'));
    }

    // ================= UPDATE =================
    public function update(Request $request)
    {
        $this->authorizePassenger();

        /** @var User $user */
        $user = Auth::user();

        // ================= VALIDATION =================
        $data = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed',
        ]);

        // ================= HANDLE PASSWORD =================
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']); // tidak update jika kosong
        }

        // ================= UPDATE =================
        $user->update($data);

        return back()->with('success', 'Profile berhasil diperbarui!');
    }
}