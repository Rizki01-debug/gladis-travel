<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class LandingController extends Controller
{
    // ================= LANDING PAGE =================
    public function index()
    {
        $page = Page::with(['sections' => function ($q) {
            $q->where('is_active', 1)
              ->orderBy('order');
        }])
        ->where('slug', 'home')
        ->where('is_active', 1)
        ->first();

        if (!$page) {
            abort(404, 'Landing page belum tersedia');
        }

        $sections = $page->sections ?? collect();

        return view('landing.index', compact('page', 'sections'));
    }

    // ================= PREVIEW (GET) =================
    public function preview()
    {
        $sections = session('preview_sections');

        // 🔥 fallback ke database jika belum ada preview
        if (!$sections || !($sections instanceof Collection) || $sections->isEmpty()) {

            $page = Page::with(['sections' => function ($q) {
                $q->where('is_active', 1)
                  ->orderBy('order');
            }])
            ->where('slug', 'home')
            ->where('is_active', 1)
            ->first();

            $sections = $page?->sections ?? collect();
        }

        return view('landing.index', compact('sections'));
    }

    // ================= STORE PREVIEW (POST) =================
    public function storePreview(Request $request)
    {
        $extra = [];

        // 🔥 HANDLE JSON EXTRA (fallback manual)
        if ($request->filled('extra')) {
            $decoded = json_decode($request->extra, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $extra = $decoded;
            }
        }

        // 🔥 HANDLE REPEATER ITEMS
        if ($request->has('items')) {
            $items = [];

            foreach ($request->items as $item) {

                $items[] = [
                    'title'       => $item['title'] ?? null,
                    'desc'        => $item['desc'] ?? null,
                    'price'       => $item['price'] ?? null,
                    'destination' => $item['destination'] ?? null,
                    'date'        => $item['date'] ?? null,
                    'status'      => $item['status'] ?? null,
                    'name'        => $item['name'] ?? null,
                    'text'        => $item['text'] ?? null,
                    'icon'        => $item['icon'] ?? null,
                    'image'       => null, // preview belum handle file
                ];
            }

            $extra['items'] = $items;
        }

        // 🔥 HANDLE IMAGE (preview hanya sementara)
        $image = null;
        if ($request->hasFile('image')) {
            $image = $request->file('image')->getClientOriginalName();
        }

        $section = (object) [
            'key'       => $request->input('key'),
            'title'     => $request->input('title'),
            'content'   => $request->input('content'),
            'image'     => $image,
            'extra'     => $extra,
            'is_active' => true,
        ];

        // 🔥 simpan sebagai collection biar konsisten
        session([
            'preview_sections' => collect([$section])
        ]);

        return response()->json([
            'success' => true
        ]);
    }
}