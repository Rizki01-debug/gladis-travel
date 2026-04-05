<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogController extends Controller
{
    // ================= AUTH =================
    private function authorizeAdmin()
    {
        $user = Auth::user();

        if (!$user || !in_array($user->role_id, [1, 2])) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= LIST LOG =================
    public function index()
    {
        $this->authorizeAdmin();

        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(10);

        return view('activity.index', compact('logs'));
    }
}