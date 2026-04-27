<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VehicleController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }

        if (!function_exists('featureActive') || !featureActive('vehicles')) {
            abort(403, 'Fitur kendaraan dinonaktifkan');
        }
    }

    // ================= INDEX =================
    public function index(Request $request)
    {
        $this->authorizeAccess();

        $query = Vehicle::query();

        // 🔍 SEARCH
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('plate_number', 'like', '%' . $request->search . '%');
            });
        }

        $vehicles = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('vehicles.index', compact('vehicles'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        return view('vehicles.create');
    }

    // ================= STORE =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number',
            'seat_capacity' => 'required|integer|min:1'
        ]);

        DB::transaction(function () use ($validated) {

            $vehicle = Vehicle::create([
                ...$validated,
                'status' => 'active'
            ]);

            // AUTO SEAT
            for ($i = 1; $i <= $vehicle->seat_capacity; $i++) {
                Seat::create([
                    'vehicle_id' => $vehicle->id,
                    'seat_number' => $i
                ]);
            }

            logActivity('Vehicle', 'Tambah kendaraan: ' . $vehicle->name);
        });

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil ditambahkan!');
    }

    // ================= EDIT =================
    public function edit($id)
    {
        $this->authorizeAccess();

        $vehicle = Vehicle::findOrFail($id);

        return view('vehicles.edit', compact('vehicle'));
    }

    // ================= UPDATE =================
    public function update(Request $request, $id)
    {
        $this->authorizeAccess();

        $vehicle = Vehicle::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'plate_number' => 'required|string|max:50|unique:vehicles,plate_number,' . $vehicle->id,
            'seat_capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive'
        ]);

        DB::transaction(function () use ($vehicle, $validated) {

            $oldCapacity = $vehicle->seat_capacity;
            $newCapacity = $validated['seat_capacity'];

            $vehicle->update($validated);

            // 🔥 HANDLE PERUBAHAN KURSI
            if ($newCapacity > $oldCapacity) {

                // tambah kursi
                for ($i = $oldCapacity + 1; $i <= $newCapacity; $i++) {
                    Seat::create([
                        'vehicle_id' => $vehicle->id,
                        'seat_number' => $i
                    ]);
                }
            } elseif ($newCapacity < $oldCapacity) {

                // hapus kursi terakhir
                Seat::where('vehicle_id', $vehicle->id)
                    ->where('seat_number', '>', $newCapacity)
                    ->delete();
            }

            logActivity('Vehicle', 'Update kendaraan: ' . $vehicle->name);
        });

        return redirect()->route('vehicles.index')
            ->with('success', 'Kendaraan berhasil diupdate!');
    }

    // ================= DELETE =================
    public function destroy($id)
    {
        $this->authorizeAccess();

        $vehicle = Vehicle::findOrFail($id);

        // 🔥 CEK RELASI
        if ($vehicle->schedules()->exists()) {
            return back()->withErrors('Kendaraan masih digunakan di schedule!');
        }

        DB::transaction(function () use ($vehicle) {

            Seat::where('vehicle_id', $vehicle->id)->delete();

            $name = $vehicle->name;

            $vehicle->delete();

            logActivity('Vehicle', 'Hapus kendaraan: ' . $name);
        });

        return back()->with('success', 'Kendaraan berhasil dihapus!');
    }
}
