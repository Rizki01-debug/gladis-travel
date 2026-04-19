<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ================= INDEX =================
    public function index()
    {
        $users = User::with('role')->latest()->paginate(10);

        return view('users.index', compact('users'));
    }

    // ================= CREATE =================
    public function create()
    {
        $roles = Role::all();

        return view('users.create', compact('roles'));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('users.index')
            ->with('success', 'User berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function edit(User $user)
    {
        $roles = Role::all();

        return view('users.edit', compact('user', 'roles'));
    }

    // ================= UPDATE =================
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => "required|email|unique:users,email,$user->id",
            'password' => 'nullable|min:6',
            'role_id' => 'required|exists:roles,id',
        ]);

        // 🔥 update basic
        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role_id' => $validated['role_id'],
        ]);

        // 🔥 kalau isi password
        if ($request->filled('password')) {
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        return redirect()->route('users.index')
            ->with('success', 'User berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy(User $user)
    {
        // 🔥 PROTECT: tidak bisa hapus diri sendiri
        if ($user->id === Auth::id()) {
            return back()->withErrors('Tidak bisa menghapus akun sendiri!');
        }

        $user->delete();

        return redirect()->route('users.index')
            ->with('success', 'User berhasil dihapus!');
    }
}
