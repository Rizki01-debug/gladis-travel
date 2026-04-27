<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WebSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class WebSettingController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 1) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= GET / CREATE SETTING =================
    private function getSetting()
    {
        return WebSetting::first() ?? WebSetting::create([]);
    }

    // ================= EDIT =================
    public function edit()
    {
        $this->authorizeAccess();

        $setting = $this->getSetting();

        return view('admin.Settings.edit', compact('setting'));
    }

    // ================= UPDATE =================
    public function update(Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'app_name'       => 'nullable|string|max:255',
            'logo'           => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'favicon'        => 'nullable|image|mimes:jpg,jpeg,png,ico|max:1024',
            'footer_text'    => 'nullable|string',
            'copyright'      => 'nullable|string|max:255',
            'contact_email'  => 'nullable|email',
            'contact_phone'  => 'nullable|string|max:20',
        ]);

        $setting = $this->getSetting();

        // ================= LOGO =================
        if ($request->hasFile('logo')) {

            if ($setting->logo && Storage::disk('public')->exists($setting->logo)) {
                Storage::disk('public')->delete($setting->logo);
            }

            $data['logo'] = $request->file('logo')
                ->store('settings', 'public');
        }

        // ================= FAVICON =================
        if ($request->hasFile('favicon')) {

            if ($setting->favicon && Storage::disk('public')->exists($setting->favicon)) {
                Storage::disk('public')->delete($setting->favicon);
            }

            $data['favicon'] = $request->file('favicon')
                ->store('settings', 'public');
        }

        // ================= UPDATE =================
        $setting->update($data);

        return back()->with('success', 'Web setting berhasil disimpan!');
    }
}