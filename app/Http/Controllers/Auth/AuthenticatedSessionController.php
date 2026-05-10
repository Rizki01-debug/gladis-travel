<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display login page
     */
    public function create(): RedirectResponse|View
    {
        if (Auth::check()) {
            return redirect()->route($this->redirectTo(Auth::user()->role_id));
        }

        return view('auth.login');
    }

    /**
     * Handle login
     */
    public function store(LoginRequest $request): RedirectResponse
    {

        // 🔥 force logout user lama
        if (Auth::check()) {
            Auth::logout();
        }
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        // 🔐 safety check
        if (!$user || !$user->role_id) {
            Auth::logout();

            return redirect('/login')->withErrors([
                'email' => 'Role user tidak valid.',
            ]);
        }

        // 🔥 redirect berdasarkan role
        return redirect()->route($this->redirectTo($user->role_id));
    }

    /**
     * Mapping role → route
     */
    private function redirectTo($roleId): string
    {
        return match ($roleId) {
            1 => 'superadmin.dashboard',
            2 => 'admin.dashboard',
            3 => 'driver.dashboard',
            4 => 'booking.index', // 🔥 fix konsisten
            default => 'login'
        };
    }

    /**
     * Logout
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
