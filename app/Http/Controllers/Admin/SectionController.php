<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Section;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

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
            $data['image'] = $request->file('image')
                ->store('sections', 'public');
        }

        // ================= REPEATER =================
        if ($request->has('items')) {

            $items = [];

            foreach ($request->items as $i => $item) {

                if (empty(array_filter($item))) continue;

                $newItem = $item;

                if ($request->hasFile("items.$i.image")) {
                    $newItem['image'] = $request->file("items.$i.image")
                        ->store('sections', 'public');
                }

                $items[] = $newItem;
            }

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

    // ================= UPDATE =================
    public function update(Request $request, Section $section)
    {
        $this->authorizeAccess();

        $data = $request->validate([
            'page_id' => 'required|exists:pages,id',
            'title'   => 'nullable|string|max:255',
            'content' => 'nullable|string',
            'image'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'order'   => 'nullable|integer',
        ]);

        $data['is_active'] = $request->has('is_active');

        // ================= HERO IMAGE =================
        if ($request->hasFile('image')) {

            if ($section->image && Storage::disk('public')->exists($section->image)) {
                Storage::disk('public')->delete($section->image);
            }

            $data['image'] = $request->file('image')
                ->store('sections', 'public');
        }

        // ================= REPEATER =================
        if ($request->has('items')) {

            $items = [];
            $oldItems = $section->extra['items'] ?? [];

            foreach ($request->items as $i => $item) {

                if (empty(array_filter($item))) continue;

                $newItem = $item;

                // 🔥 IMAGE BARU
                if ($request->hasFile("items.$i.image")) {

                    if (!empty($oldItems[$i]['image']) &&
                        Storage::disk('public')->exists($oldItems[$i]['image'])) {
                        Storage::disk('public')->delete($oldItems[$i]['image']);
                    }

                    $newItem['image'] = $request->file("items.$i.image")
                        ->store('sections', 'public');
                }
                // 🔥 PAKAI LAMA
                else {
                    $newItem['image'] = $oldItems[$i]['image'] ?? null;
                }

                $items[] = $newItem;
            }

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

        // 🔥 HERO IMAGE
        if ($section->image && Storage::disk('public')->exists($section->image)) {
            Storage::disk('public')->delete($section->image);
        }

        // 🔥 REPEATER IMAGE
        $items = $section->extra['items'] ?? [];

        foreach ($items as $item) {
            if (!empty($item['image']) &&
                Storage::disk('public')->exists($item['image'])) {
                Storage::disk('public')->delete($item['image']);
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
}