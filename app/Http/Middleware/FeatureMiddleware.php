<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Feature;

class FeatureMiddleware
{
    public function handle(Request $request, Closure $next, $feature)
    {
        $user = Auth::user();

        // 🔒 HARUS LOGIN
        if (!$user) {
            abort(403, 'Unauthorized');
        }

        // 🔥 BYPASS SUPER ADMIN (role_id = 1)
        if ($user->role_id == 1) {
            return $next($request);
        }

        // 🔥 AMBIL ROLE (AMAN)
        $roleName = $user->role->name ?? $this->mapRole($user->role_id);

        // 🔥 CEK FEATURE AKTIF
        $isActive = Feature::where([
            ['name', $feature],
            ['role', $roleName],
            ['is_active', 1],
        ])->exists();

        // 🔥 JIKA FEATURE TIDAK ADA ATAU OFF
        if (!$isActive) {
            abort(403, 'Fitur ini tidak tersedia untuk role Anda');
        }

        return $next($request);
    }

    // ================= HELPER =================
    private function mapRole($roleId)
    {
        return match ($roleId) {
            1 => 'super_admin',
            2 => 'admin',
            3 => 'driver',
            default => 'passenger',
        };
    }
}
