<?php

use App\Models\Feature;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

// ================= FEATURE =================
if (!function_exists('featureActive')) {
    function featureActive($name): bool
    {
        $user = Auth::user();

        if (!$user) return false;

        $roleName = optional($user->role)->name;

        if (!$roleName) return false;

        return Feature::where('name', $name)
            ->where('role', $roleName)
            ->where('is_active', true)
            ->exists();
    }
}

// ================= LOG =================
if (!function_exists('logActivity')) {
    function logActivity($action, $desc = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $desc
            ]);
        } catch (\Throwable $e) {
            logger()->error('ActivityLog Error: ' . $e->getMessage());
        }
    }
}

// ================= DISTANCE =================
if (!function_exists('calculateDistance')) {
    function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

if (!function_exists('calculateRouteDistance')) {
    function calculateRouteDistance($routePoints)
    {
        $total = 0;

        for ($i = 0; $i < count($routePoints) - 1; $i++) {

            $p1 = $routePoints[$i]->meetingPoint;
            $p2 = $routePoints[$i + 1]->meetingPoint;

            if ($p1 && $p2) {
                $total += calculateDistance(
                    $p1->latitude,
                    $p1->longitude,
                    $p2->latitude,
                    $p2->longitude
                );
            }
        }

        return round($total, 2);
    }
}
