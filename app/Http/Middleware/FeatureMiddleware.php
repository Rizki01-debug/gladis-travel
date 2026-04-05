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

        if (!$user) {
            abort(403, 'Unauthorized');
        }

        $isActive = Feature::where('name', $feature)
            ->where('role', $user->role->name ?? null) // 🔥 FIX penting
            ->where('is_active', true)
            ->exists();

        if (!$isActive) {
            abort(403, 'Fitur dinonaktifkan oleh sistem');
        }

        return $next($request);
    }
}
