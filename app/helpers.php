<?php

use App\Models\Feature;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

// ================= FEATURE =================
if (!function_exists('featureActive')) {
    function featureActive(string $name): bool
    {
        try {
            $user = Auth::user();

            if (!$user) return false;

            // 🔥 NORMALISASI ROLE (WAJIB)
            $roleName = strtolower(optional($user->role)->name ?? '');

            if (!$roleName) {
                return false;
            }

            // 🔥 CACHE PER REQUEST + USER
            static $cache = [];

            $cacheKey = $user->id . '_' . $roleName . '_' . $name;

            if (isset($cache[$cacheKey])) {
                return $cache[$cacheKey];
            }

            $isActive = Feature::query()
                ->where('name', $name)
                ->where('role', $roleName)
                ->where('is_active', 1)
                ->exists();

            $cache[$cacheKey] = $isActive;

            return $isActive;
        } catch (\Throwable $e) {
            Log::error('featureActive Error: ' . $e->getMessage());
            return false;
        }
    }
}

// ================= LOG =================
if (!function_exists('logActivity')) {
    function logActivity(string $action, ?string $desc = null): void
    {
        try {
            ActivityLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'description' => $desc
            ]);
        } catch (\Throwable $e) {
            Log::error('ActivityLog Error: ' . $e->getMessage());
        }
    }
}

// ================= DISTANCE =================
if (!function_exists('calculateDistance')) {
    function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        if (!$lat1 || !$lon1 || !$lat2 || !$lon2) return 0;

        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}

// ================= ROUTE DISTANCE =================
if (!function_exists('calculateRouteDistance')) {
    function calculateRouteDistance($routePoints): float
    {
        if (!$routePoints) return 0;

        if (!$routePoints instanceof Collection) {
            $routePoints = collect($routePoints);
        }

        if ($routePoints->count() < 2) return 0;

        // 🔥 SORT BY ORDER (PENTING)
        $points = $routePoints->sortBy('order')->values();

        $total = 0;

        for ($i = 0; $i < $points->count() - 1; $i++) {

            $p1 = optional($points[$i])->meetingPoint;
            $p2 = optional($points[$i + 1])->meetingPoint;

            if (
                $p1 && $p2 &&
                $p1->latitude && $p1->longitude &&
                $p2->latitude && $p2->longitude
            ) {
                $total += calculateDistance(
                    (float) $p1->latitude,
                    (float) $p1->longitude,
                    (float) $p2->latitude,
                    (float) $p2->longitude
                );
            }
        }

        return round($total, 2);
    }
}

// ================= WEB SETTINGS =================
if (!function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        try {
            static $settings = null;

            // 🔥 LOAD SEKALI PER REQUEST
            if ($settings === null) {
                $settings = \App\Models\WebSetting::query()->first();
            }

            // 🔥 JIKA TIDAK ADA DATA
            if (!$settings) {
                return $default;
            }

            // 🔥 CEK FIELD ADA / TIDAK
            if (!isset($settings->$key)) {
                return $default;
            }

            return $settings->$key;

        } catch (\Throwable $e) {

            // 🔥 LOG ERROR (AMAN DI HELPER)
            Log::error('setting() Error: ' . $e->getMessage());

            return $default;
        }
    }
}

// ================= SECTION =================
if (!function_exists('section')) {
    function section(string $key, string $pageSlug = 'home')
    {
        try {
            static $cache = [];

            $cacheKey = $pageSlug . '_' . $key;

            if (isset($cache[$cacheKey])) {
                return $cache[$cacheKey];
            }

            $page = \App\Models\Page::where('slug', $pageSlug)
                ->where('is_active', 1)
                ->first();

            if (!$page) {
                return null;
            }

            $section = $page->sections()
                ->where('key', $key)
                ->first();

            $cache[$cacheKey] = $section;

            return $section;

        } catch (\Throwable $e) {
            Log::error('section() Error: ' . $e->getMessage());
            return null;
        }
    }
}