<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    // ================= HELPER =================
    private function isDriverRole($roleId)
    {
        return Role::where('id', $roleId)
            ->where('name', 'driver')
            ->exists();
    }

    // ================= INDEX =================
    public function index()
    {
        $users = User::with('role')
            ->whereHas('role', function ($q) {
                $q->whereIn('name', ['super_admin', 'admin', 'driver']);
            })
            ->latest()
            ->paginate(10);

        return view('users.index', compact('users'));
    }

    // ================= CREATE =================
    public function create()
    {
        $roles = Role::whereIn('name', ['super_admin', 'admin', 'driver'])->get();

        $vehicles = Vehicle::whereNull('driver_id')->get();

        return view('users.create', compact('roles', 'vehicles'));
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'role_id'    => 'required|exists:roles,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        try {
            DB::transaction(function () use ($validated) {

                // 🔥 CREATE USER
                $user = User::create([
                    'name'     => $validated['name'],
                    'email'    => $validated['email'],
                    'password' => Hash::make($validated['password']),
                    'role_id'  => $validated['role_id'],
                ]);

                // 🔥 ASSIGN VEHICLE (ONLY DRIVER)
                if ($this->isDriverRole($validated['role_id']) && !empty($validated['vehicle_id'])) {

                    $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$vehicle || $vehicle->driver_id) {
                        throw new \Exception('Kendaraan sudah digunakan!');
                    }

                    $vehicle->update([
                        'driver_id' => $user->id
                    ]);
                }
            });

            return redirect()->route('users.index')
                ->with('success', 'User berhasil ditambahkan!');

        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    // ================= EDIT =================
    public function edit(User $user)
    {
        $roles = Role::whereIn('name', ['super_admin', 'admin', 'driver'])->get();

        $vehicles = Vehicle::whereNull('driver_id')
            ->orWhere('driver_id', $user->id)
            ->get();

        return view('users.edit', compact('user', 'roles', 'vehicles'));
    }

    // ================= UPDATE =================
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => "required|email|unique:users,email,$user->id",
            'password'   => 'nullable|min:6',
            'role_id'    => 'required|exists:roles,id',
            'vehicle_id' => 'nullable|exists:vehicles,id',
        ]);

        try {
            DB::transaction(function () use ($validated, $user) {

                // 🔥 UPDATE BASIC
                $user->update([
                    'name'    => $validated['name'],
                    'email'   => $validated['email'],
                    'role_id' => $validated['role_id'],
                ]);

                // 🔥 PASSWORD
                if (!empty($validated['password'])) {
                    $user->update([
                        'password' => Hash::make($validated['password']),
                    ]);
                }

                // 🔥 LEPAS SEMUA VEHICLE LAMA
                Vehicle::where('driver_id', $user->id)
                    ->lockForUpdate()
                    ->update(['driver_id' => null]);

                // 🔥 ASSIGN BARU (ONLY DRIVER)
                if ($this->isDriverRole($validated['role_id']) && !empty($validated['vehicle_id'])) {

                    $vehicle = Vehicle::where('id', $validated['vehicle_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$vehicle || $vehicle->driver_id) {
                        throw new \Exception('Kendaraan sudah digunakan!');
                    }

                    $vehicle->update([
                        'driver_id' => $user->id
                    ]);
                }
            });

            return redirect()->route('users.index')
                ->with('success', 'User berhasil diupdate!');

        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage())->withInput();
        }
    }

    // ================= DELETE =================
    public function destroy(User $user)
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors('Tidak bisa menghapus akun sendiri!');
        }

        try {
            DB::transaction(function () use ($user) {

                // 🔥 LEPAS VEHICLE
                Vehicle::where('driver_id', $user->id)
                    ->lockForUpdate()
                    ->update(['driver_id' => null]);

                $user->delete();
            });

            return redirect()->route('users.index')
                ->with('success', 'User berhasil dihapus!');

        } catch (\Throwable $e) {
            return back()->withErrors($e->getMessage());
        }
    }
}