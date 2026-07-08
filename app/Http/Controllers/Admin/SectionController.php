<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SectionController extends Controller
{
    // ================= AUTH =================
    private function authorizeAccess()
    {
        $user = Auth::user();

        if (!$user || $user->role_id != 1) {
            abort(403, 'Akses ditolak');
        }
    }

    // ================= INDEX =================
    public function index()
    {
        $this->authorizeAccess();

        $sections = Section::with('page')
            ->orderBy('page_id')
            ->orderBy('order')
            ->paginate(10);

        return view('admin.sections.index', compact('sections'));
    }

    // ================= CREATE =================
    public function create()
    {
        $this->authorizeAccess();

        $pages = Page::pluck('title', 'id');
        return view('admin.sections.create', compact('pages'));
    }

    // ================= STORE - FIXED =================
    public function store(Request $request)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'key'     => 'required|string|max:100',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order'   => 'nullable|integer',
        ]);

        // 🔥 FIX: Eksplisit set is_active
        $data['is_active'] = $request->has('is_active') ? true : false;

        // Log untuk debug
        Log::info('STORE SECTION:', [
            'key' => $data['key'],
            'is_active' => $data['is_active'],
            'has_is_active' => $request->has('is_active')
        ]);

        // ================= HERO IMAGE =================
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        // ================= REPEATER =================
        if ($request->has('items')) {
            $items = $this->processRepeaterItems($request, null);
            if (!empty($items)) {
                $data['extra'] = ['items' => $items];
            }
        }

        // ================= JSON MANUAL =================
        elseif ($request->filled('extra')) {
            $decoded = json_decode($request->extra, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors('Format JSON tidak valid!')
                    ->withInput();
            }

            $data['extra'] = $decoded;
        }

        $section = Section::create($data);

        Log::info('SECTION CREATED:', [
            'id' => $section->id,
            'is_active' => $section->is_active
        ]);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section berhasil ditambahkan');
    }

    // ================= EDIT =================
    public function edit(Section $section)
    {
        $this->authorizeAccess();

        $pages = Page::pluck('title', 'id');
        return view('admin.sections.edit', compact('section', 'pages'));
    }

    // ================= UPDATE - FIXED =================
    public function update(Request $request, Section $section)
    {
        $this->authorizeAccess();

        // 🔥 DEBUG: Log semua data masuk
        Log::info('=== UPDATE SECTION REQUEST ===');
        Log::info('Request data:', $request->all());
        Log::info('Has is_active: ' . ($request->has('is_active') ? 'YES' : 'NO'));
        Log::info('is_active value: ' . $request->input('is_active'));
        Log::info('Current DB is_active: ' . $section->is_active);

        $data = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'key'     => 'required|string|max:100',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order'   => 'nullable|integer',
        ]);

        // 🔥 FIX: Eksplisit set is_active dengan nilai boolean
        // Jika checkbox di-check -> true (1), jika tidak -> false (0)
        $data['is_active'] = $request->has('is_active') ? true : false;

        Log::info('After set is_active:', [
            'is_active' => $data['is_active'],
            'is_active_type' => gettype($data['is_active'])
        ]);

        // ================= HERO IMAGE =================
        if ($request->hasFile('image')) {
            $this->deleteImage($section->image);
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        // ================= REPEATER =================
        if ($request->has('items')) {
            $oldItems = $section->extra['items'] ?? [];
            $items = $this->processRepeaterItems($request, $oldItems);
            
            if (!empty($items)) {
                $data['extra'] = ['items' => $items];
            } else {
                $data['extra'] = !empty($oldItems) ? ['items' => $oldItems] : null;
            }
        }

        // ================= JSON MANUAL =================
        elseif ($request->filled('extra')) {
            $decoded = json_decode($request->extra, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors('Format JSON tidak valid!')
                    ->withInput();
            }

            $data['extra'] = $decoded;
        }

        // ================= PERTAHANKAN EXTRA LAMA =================
        else {
            $data['extra'] = $section->extra;
        }

        // 🔥 DEBUG: Log data sebelum update
        Log::info('FINAL DATA TO UPDATE:', [
            'section_id' => $section->id,
            'key' => $section->key,
            'is_active' => $data['is_active'],
            'is_active_type' => gettype($data['is_active']),
            'extra' => $data['extra']
        ]);

        // 🔥 FIX: Update dengan data yang sudah diproses
        $section->update($data);

        // 🔥 DEBUG: Log setelah update
        $freshSection = $section->fresh();
        Log::info('AFTER UPDATE RESULT:', [
            'section_id' => $section->id,
            'is_active' => $freshSection->is_active,
            'is_active_type' => gettype($freshSection->is_active)
        ]);

        return redirect()->route('admin.sections.index')
            ->with('success', 'Section berhasil diupdate');
    }

    // ================= DELETE =================
    public function destroy(Section $section)
    {
        $this->authorizeAccess();

        // Hapus hero image
        $this->deleteImage($section->image);

        // Hapus repeater images
        $items = $section->extra['items'] ?? [];
        foreach ($items as $item) {
            if (!empty($item['image'])) {
                $this->deleteImage($item['image']);
            }
        }

        $section->delete();

        return back()->with('success', 'Section dihapus');
    }

    // ================= TOGGLE =================
    public function toggle(Section $section)
    {
        $this->authorizeAccess();

        $newStatus = !$section->is_active;
        
        $section->update([
            'is_active' => $newStatus
        ]);

        Log::info('TOGGLE SECTION:', [
            'section_id' => $section->id,
            'key' => $section->key,
            'old_status' => !$newStatus,
            'new_status' => $newStatus
        ]);

        return back()->with('success', 'Status section berhasil diubah menjadi ' . ($newStatus ? 'Aktif' : 'Nonaktif'));
    }

    // ================= HELPER: UPLOAD IMAGE =================
    private function uploadImage($file)
    {
        try {
            $filename = uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            $path = $file->storeAs('sections', $filename, 'public');
            
            if (!$path) {
                throw new \Exception('Gagal upload image');
            }
            
            Log::info('Image uploaded:', ['path' => $path]);
            
            return $path;
        } catch (\Exception $e) {
            Log::error('Upload image gagal: ' . $e->getMessage());
            throw new \Exception('Upload image gagal: ' . $e->getMessage());
        }
    }

    // ================= HELPER: DELETE IMAGE =================
    private function deleteImage($path)
    {
        if (empty($path)) {
            return false;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                Log::info('Image deleted: ' . $path);
                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Gagal hapus image: ' . $path . ' - ' . $e->getMessage());
        }

        return false;
    }

    // ================= HELPER: PROCESS REPEATER ITEMS =================
    private function processRepeaterItems($request, $oldItems = null)
    {
        $items = [];
        $oldItems = $oldItems ?? [];
        $oldItemsIndexed = array_values($oldItems);

        if (config('app.debug')) {
            Log::info('PROCESS REPEATER ITEMS:', [
                'request_items_count' => count($request->items ?? []),
                'old_items_count' => count($oldItems)
            ]);
        }

        foreach ($request->items as $i => $item) {
            // Better empty check
            $isEmpty = true;
            foreach ($item as $key => $value) {
                if ($key !== 'image' && !empty($value)) {
                    $isEmpty = false;
                    break;
                }
            }

            // Skip jika semua field kosong (kecuali image)
            if ($isEmpty && !$request->hasFile("items.$i.image")) {
                continue;
            }

            $newItem = [];

            // Copy semua field kecuali image
            foreach ($item as $key => $value) {
                if ($key !== 'image') {
                    $newItem[$key] = $value ?? null;
                }
            }

            // Handle image upload
            if ($request->hasFile("items.$i.image")) {
                $file = $request->file("items.$i.image");
                
                if ($file && $file->isValid()) {
                    $oldItem = $oldItemsIndexed[$i] ?? null;
                    if ($oldItem && !empty($oldItem['image'])) {
                        $this->deleteImage($oldItem['image']);
                    }

                    $newItem['image'] = $this->uploadImage($file);
                }
            } else {
                $oldItem = $oldItemsIndexed[$i] ?? null;
                $newItem['image'] = $oldItem['image'] ?? null;
            }

            $items[] = $newItem;
        }

        if (config('app.debug')) {
            Log::info('PROCESS REPEATER ITEMS RESULT:', [
                'items_count' => count($items)
            ]);
        }

        return $items;
    }

    // ================= HELPER: REORDER ITEMS =================
    public function reorder(Request $request)
    {
        $this->authorizeAccess();

        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer|exists:sections,id',
        ]);

        foreach ($request->orders as $index => $id) {
            Section::where('id', $id)->update(['order' => $index]);
        }

        return response()->json(['success' => true]);
    }

    // ================= HELPER: FIX CORRUPTED DATA =================
    public function fixTestimonialData()
    {
        $this->authorizeAccess();

        $testimonials = Section::where('key', 'testimonials')->get();

        if ($testimonials->isEmpty()) {
            return back()->with('warning', 'Tidak ada data testimonial ditemukan');
        }

        $fixed = 0;

        foreach ($testimonials as $section) {
            $extra = $section->extra;
            
            if (empty($extra) || empty($extra['items'])) {
                $defaultItems = [
                    [
                        'name' => 'Andi',
                        'text' => 'Pelayanan sangat cepat dan nyaman.',
                        'role' => 'Penumpang GLADIS',
                        'image' => null
                    ],
                    [
                        'name' => 'Siti',
                        'text' => 'Perjalanan sangat nyaman dan tepat waktu.',
                        'role' => 'Penumpang GLADIS',
                        'image' => null
                    ],
                    [
                        'name' => 'Rizki',
                        'text' => 'Booking online sangat mudah.',
                        'role' => 'Penumpang GLADIS',
                        'image' => null
                    ]
                ];
                
                $section->update([
                    'extra' => ['items' => $defaultItems]
                ]);
                
                $fixed++;
                Log::info('Fixed testimonial data for section ID: ' . $section->id);
            }
        }

        if ($fixed > 0) {
            return back()->with('success', $fixed . ' data testimonial berhasil diperbaiki');
        }

        return back()->with('info', 'Semua data testimonial sudah dalam keadaan baik');
    }

    // ================= HELPER: FIX DESTINATION DATA =================
    public function fixDestinationData()
    {
        $this->authorizeAccess();

        $destinations = Section::where('key', 'destinations')->get();

        if ($destinations->isEmpty()) {
            return back()->with('warning', 'Tidak ada data destinasi ditemukan');
        }

        $fixed = 0;

        foreach ($destinations as $section) {
            $extra = $section->extra;
            
            if (empty($extra) || empty($extra['items'])) {
                $defaultItems = [
                    [
                        'title' => 'Bandung',
                        'desc' => 'Perjalanan nyaman menuju Kota Bandung',
                        'price' => 150000,
                        'image' => null
                    ],
                    [
                        'title' => 'Jakarta',
                        'desc' => 'Travel harian menuju Jakarta',
                        'price' => 180000,
                        'image' => null
                    ],
                    [
                        'title' => 'Cirebon',
                        'desc' => 'Perjalanan cepat menuju Kota Cirebon',
                        'price' => 80000,
                        'image' => null
                    ]
                ];
                
                $section->update([
                    'extra' => ['items' => $defaultItems]
                ]);
                
                $fixed++;
                Log::info('Fixed destination data for section ID: ' . $section->id);
            }
        }

        if ($fixed > 0) {
            return back()->with('success', $fixed . ' data destinasi berhasil diperbaiki');
        }

        return back()->with('info', 'Semua data destinasi sudah dalam keadaan baik');
    }

    // ================= HELPER: FIX IS_ACTIVE DATA =================
    public function fixIsActiveData()
    {
        $this->authorizeAccess();

        $sections = Section::all();
        $fixed = 0;

        foreach ($sections as $section) {
            // Pastikan is_active adalah boolean yang benar
            $currentValue = $section->is_active;
            $correctValue = (bool) $currentValue;
            
            if ($currentValue !== $correctValue) {
                $section->update(['is_active' => $correctValue]);
                $fixed++;
                Log::info('Fixed is_active for section ID: ' . $section->id, [
                    'old' => $currentValue,
                    'new' => $correctValue
                ]);
            }
        }

        return back()->with('success', $fixed . ' data is_active berhasil diperbaiki');
    }
}