<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    // ================= PREVIEW (GET) - FIXED =================
    public function preview()
    {
        // Cek session preview
        $sections = session('preview_sections');
        $previewData = session('preview_data');

        // 🔥 Jika ada preview data dari POST
        if ($previewData && is_array($previewData)) {
            $section = $this->buildPreviewSection($previewData);
            $sections = collect([$section]);
        }

        // 🔥 Fallback ke database jika tidak ada preview
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

    // ================= STORE PREVIEW (POST) - FIXED =================
    public function storePreview(Request $request)
    {
        try {
            $previewData = [];
            $items = [];

            // 🔥 HANDLE BASIC FIELDS
            $previewData['key'] = $request->input('key');
            $previewData['title'] = $request->input('title');
            $previewData['content'] = $request->input('content');
            $previewData['order'] = $request->input('order', 0);
            $previewData['is_active'] = $request->has('is_active') ? 1 : 0;

            // 🔥 HANDLE HERO IMAGE
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                if ($file->isValid()) {
                    // Simpan temporary untuk preview
                    $previewData['image'] = $this->storeTemporaryImage($file);
                }
            } else {
                // Gunakan image dari database jika ada
                $previewData['image'] = $request->input('current_image');
            }

            // 🔥 HANDLE REPEATER ITEMS
            if ($request->has('items')) {
                $items = $this->processPreviewItems($request);
                $previewData['extra'] = ['items' => $items];
            }

            // 🔥 HANDLE JSON EXTRA
            elseif ($request->filled('extra')) {
                $decoded = json_decode($request->extra, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $previewData['extra'] = $decoded;
                }
            }

            // Simpan ke session
            session([
                'preview_data' => $previewData,
                'preview_sections' => collect([$this->buildPreviewSection($previewData)])
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Preview berhasil dimuat'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat preview: ' . $e->getMessage()
            ], 500);
        }
    }

    // ================= HELPER: BUILD PREVIEW SECTION =================
    private function buildPreviewSection($data)
    {
        return (object) [
            'key' => $data['key'] ?? null,
            'title' => $data['title'] ?? null,
            'content' => $data['content'] ?? null,
            'image' => $data['image'] ?? null,
            'extra' => $data['extra'] ?? [],
            'order' => $data['order'] ?? 0,
            'is_active' => $data['is_active'] ?? true,
        ];
    }

    // ================= HELPER: STORE TEMPORARY IMAGE =================
    private function storeTemporaryImage($file)
    {
        try {
            // Generate unique filename untuk temporary
            $filename = 'temp_' . uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            // Store di temporary folder
            $path = $file->storeAs('temp/preview', $filename, 'public');
            
            if (!$path) {
                throw new \Exception('Gagal menyimpan image temporary');
            }
            
            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Upload image preview gagal: ' . $e->getMessage());
        }
    }

    // ================= HELPER: PROCESS PREVIEW ITEMS =================
    private function processPreviewItems($request)
    {
        $items = [];

        foreach ($request->items as $i => $item) {
            // Skip empty items
            if (empty(array_filter($item, function($value) {
                return !is_null($value) && $value !== '';
            }))) {
                continue;
            }

            $newItem = $item;

            // Handle image upload untuk items
            if ($request->hasFile("items.$i.image")) {
                $file = $request->file("items.$i.image");
                if ($file->isValid()) {
                    $newItem['image'] = $this->storeTemporaryImage($file);
                }
            }
            // Jika tidak ada file baru, gunakan image yang sudah ada
            elseif (isset($item['image']) && !empty($item['image'])) {
                // Keep existing image path
                $newItem['image'] = $item['image'];
            } else {
                $newItem['image'] = null;
            }

            $items[] = $newItem;
        }

        return $items;
    }

    // ================= HELPER: CLEANUP TEMPORARY IMAGES =================
    public function cleanupTempImages()
    {
        try {
            $files = Storage::disk('public')->files('temp/preview');
            
            foreach ($files as $file) {
                // Hapus file temporary yang lebih dari 1 jam
                $lastModified = Storage::disk('public')->lastModified($file);
                if (time() - $lastModified > 3600) {
                    Storage::disk('public')->delete($file);
                }
            }
            
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ================= HELPER: GET PREVIEW DATA =================
    public function getPreviewData()
    {
        $previewData = session('preview_data');
        
        if (!$previewData) {
            return response()->json(['success' => false, 'message' => 'Tidak ada data preview'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $previewData
        ]);
    }

    // ================= HELPER: CLEAR PREVIEW =================
    public function clearPreview()
    {
        // Hapus temporary images
        $previewData = session('preview_data');
        if ($previewData && isset($previewData['image'])) {
            $this->deleteTempImage($previewData['image']);
        }
        
        // Hapus items images
        if ($previewData && isset($previewData['extra']['items'])) {
            foreach ($previewData['extra']['items'] as $item) {
                if (isset($item['image'])) {
                    $this->deleteTempImage($item['image']);
                }
            }
        }
        
        // Clear session
        session()->forget(['preview_data', 'preview_sections']);
        
        return redirect()->back()->with('success', 'Preview direset');
    }

    // ================= HELPER: DELETE TEMP IMAGE =================
    private function deleteTempImage($path)
    {
        if (empty($path)) return;
        
        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return true;
            }
        } catch (\Exception $e) {
            // \Log::warning('Gagal hapus temp image: ' . $path . ' - ' . $e->getMessage());
        }
        
        return false;
    }
}