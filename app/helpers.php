<?php

use App\Models\Feature;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

if (!function_exists('featureActive')) {
    function featureActive($name): bool
    {
        $user = Auth::user();

        if (!$user) {
            return false;
        }

        $roleName = optional($user->role)->name;

        if (!$roleName) {
            return false;
        }

        return Feature::where('name', $name)
            ->where('role', $roleName)
            ->where('is_active', true)
            ->exists();
    }
}

if (!function_exists('logActivity')) {
    function logActivity($action, $desc = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(), // 🔥 FIX DI SINI
                'action' => $action,
                'description' => $desc
            ]);

        } catch (\Throwable $e) {
            logger()->error('ActivityLog Error: ' . $e->getMessage());
        }
    }
}