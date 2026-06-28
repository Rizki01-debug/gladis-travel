<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
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

    // ================= STORE =================
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

        $data['is_active'] = $request->has('is_active');

        // ================= HERO IMAGE =================
        if ($request->hasFile('image')) {
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        // ================= REPEATER =================
        if ($request->has('items')) {
            $items = $this->processRepeaterItems($request, null);
            $data['extra'] = ['items' => $items];
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

        Section::create($data);

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

        $data = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'key'     => 'required|string|max:100',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order'   => 'nullable|integer',
        ]);

        $data['is_active'] = $request->has('is_active');

        // ================= HERO IMAGE - FIXED =================
        if ($request->hasFile('image')) {
            // Hapus image lama
            $this->deleteImage($section->image);
            
            // Upload image baru
            $data['image'] = $this->uploadImage($request->file('image'));
        }

        // ================= REPEATER - FIXED =================
        if ($request->has('items')) {
            $oldItems = $section->extra['items'] ?? [];
            $items = $this->processRepeaterItems($request, $oldItems);
            $data['extra'] = ['items' => $items];
        }

        // ================= JSON =================
        elseif ($request->filled('extra')) {
            $decoded = json_decode($request->extra, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return back()
                    ->withErrors('Format JSON tidak valid!')
                    ->withInput();
            }

            $data['extra'] = $decoded;
        }

        else {
            $data['extra'] = null;
        }

        $section->update($data);

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

        $section->update([
            'is_active' => !$section->is_active
        ]);

        return back()->with('success', 'Status section berhasil diubah');
    }

    // ================= HELPER: UPLOAD IMAGE =================
    private function uploadImage($file)
    {
        try {
            // Generate unique filename
            $filename = uniqid() . '_' . Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();
            
            // Store file
            $path = $file->storeAs('sections', $filename, 'public');
            
            if (!$path) {
                throw new \Exception('Gagal upload image');
            }
            
            return $path;
        } catch (\Exception $e) {
            throw new \Exception('Upload image gagal: ' . $e->getMessage());
        }
    }

    // ================= HELPER: DELETE IMAGE =================
    private function deleteImage($path)
    {
        if (empty($path)) {
            return;
        }

        try {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
                return true;
            }
        } catch (\Exception $e) {
            // Log error tapi jangan stop proses
            // \Log::warning('Gagal hapus image: ' . $path . ' - ' . $e->getMessage());
        }

        return false;
    }

    // ================= HELPER: PROCESS REPEATER ITEMS =================
    private function processRepeaterItems($request, $oldItems = null)
    {
        $items = [];
        $oldItems = $oldItems ?? [];
        $oldItemsIndexed = array_values($oldItems); // Re-index untuk keamanan

        foreach ($request->items as $i => $item) {
            // Skip empty items
            if (empty(array_filter($item, function($value) {
                return !is_null($value) && $value !== '';
            }))) {
                continue;
            }

            $newItem = $item;

            // 🔥 Handle image upload
            if ($request->hasFile("items.$i.image")) {
                $file = $request->file("items.$i.image");
                
                // Cek validasi file
                if ($file->isValid()) {
                    // Hapus old image jika ada (cari berdasarkan index yang benar)
                    $oldItem = $oldItemsIndexed[$i] ?? null;
                    if ($oldItem && !empty($oldItem['image'])) {
                        $this->deleteImage($oldItem['image']);
                    }

                    // Upload new image
                    $newItem['image'] = $this->uploadImage($file);
                }
            } 
            // 🔥 Gunakan image lama jika ada
            else {
                $oldItem = $oldItemsIndexed[$i] ?? null;
                $newItem['image'] = $oldItem['image'] ?? null;
            }

            $items[] = $newItem;
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
}