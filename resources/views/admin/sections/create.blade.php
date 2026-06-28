@extends('layouts.app')

@section('content')

<div class="container-fluid fade-in">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">➕ Tambah Section</h4>
        <small class="text-muted">Tambahkan konten untuk landing page</small>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- SUCCESS --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="row g-3">

        {{-- ================= FORM ================= --}}
        <div class="col-md-6">

            <form id="sectionForm"
                  method="POST"
                  action="{{ route('admin.sections.store') }}"
                  enctype="multipart/form-data">

                @csrf

                <div class="card shadow-sm border-0">
                    <div class="card-body">

                        {{-- PAGE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Page</label>
                            <select name="page_id" class="form-control" required>
                                <option value="">-- Pilih Page --</option>
                                @foreach ($pages as $id => $title)
                                    <option value="{{ $id }}" {{ old('page_id') == $id ? 'selected' : '' }}>
                                        {{ $title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- KEY --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Key Section</label>
                            <select name="key" id="keySelect" class="form-control" required>
                                <option value="">-- Pilih Section --</option>
                                @foreach (['hero','destinations','schedule','features','testimonials','cta'] as $key)
                                    <option value="{{ $key }}" {{ old('key') == $key ? 'selected' : '' }}>
                                        {{ ucfirst($key) }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted">Pilih tipe section yang akan dibuat</small>
                        </div>

                        {{-- TITLE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}" placeholder="Masukkan judul section">
                            <small class="text-muted">Judul utama section (contoh: Destinasi Populer)</small>
                        </div>

                        {{-- CONTENT --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Content</label>
                            <textarea name="content" class="form-control" rows="3" placeholder="Masukkan konten deskripsi">{{ old('content') }}</textarea>
                            <small class="text-muted">Deskripsi atau subjudul section</small>
                        </div>

                        {{-- ================= IMAGE HERO - FIXED ================= --}}
                        <div class="mb-3 d-none" id="mainImage">
                            <label class="form-label fw-semibold">Image (Hero)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Upload gambar untuk hero section (max 2MB, format: jpg, jpeg, png, webp)</small>
                            <div id="imagePreview" class="mt-2 d-none">
                                <img id="heroImagePreview" src="" width="180" class="rounded border shadow-sm">
                                <br>
                                <button type="button" class="btn btn-sm btn-danger mt-2" onclick="removeHeroImage()">
                                    🗑 Hapus Gambar
                                </button>
                            </div>
                        </div>

                        {{-- ================= REPEATER - FIXED ================= --}}
                        <div class="mb-3 d-none" id="repeaterSection">
                            <label class="form-label fw-semibold">Items</label>
                            <small class="text-muted d-block mb-2">Tambahkan item untuk section ini</small>

                            <div id="items-wrapper">
                                <div class="text-muted p-3 border rounded" id="emptyState">
                                    Belum ada items. Klik "Tambah Item" untuk menambahkan.
                                </div>
                            </div>

                            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addItem()">
                                ➕ Tambah Item
                            </button>
                        </div>

                        {{-- ================= JSON MANUAL ================= --}}
                        <div class="mb-3 d-none" id="jsonManual">
                            <label class="form-label fw-semibold">Extra JSON</label>
                            <textarea name="extra" class="form-control" rows="4" placeholder='{"button_text": "Mulai Sekarang"}'>{{ old('extra') }}</textarea>
                            <small class="text-muted">Masukkan data JSON untuk section (contoh: {"button_text": "Mulai"})</small>
                        </div>

                        {{-- ORDER --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Order</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}" min="0">
                            <small class="text-muted">Urutan tampilan section (semakin kecil semakin atas)</small>
                        </div>

                        {{-- STATUS --}}
                        <div class="form-check mb-4">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1" 
                                   class="form-check-input"
                                   id="isActiveCheckbox"
                                   {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="isActiveCheckbox">
                                Aktifkan Section
                            </label>
                            <small class="text-muted d-block">Jika tidak aktif, section tidak akan tampil di landing page</small>
                        </div>

                        {{-- ACTION --}}
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary">
                                ← Kembali
                            </a>

                            <button type="submit" class="btn btn-success">
                                💾 Simpan
                            </button>
                        </div>

                        <button type="button" class="btn btn-info w-100 mt-3" onclick="previewData()">
                            👁 Preview
                        </button>

                    </div>
                </div>

            </form>

        </div>

        {{-- ================= PREVIEW ================= --}}
        <div class="col-md-6">

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">

                    <iframe id="previewFrame"
                            src="{{ route('landing.preview') }}"
                            width="100%"
                            height="700"
                            style="border:0;">
                    </iframe>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection

@push('scripts')
<script>
let index = 0;
let currentType = null;
let items = [];

// ================= FIELD TEMPLATE - FIXED =================
function getFields(type, i) {

    const map = {
        destinations: `
            <div class="mb-2">
                <label class="form-label fw-semibold">Nama Destinasi</label>
                <input name="items[${i}][title]" class="form-control" placeholder="Contoh: Bali Paradise">
            </div>
            <div class="mb-2">
                <label class="form-label">Deskripsi</label>
                <input name="items[${i}][desc]" class="form-control" placeholder="Contoh: Pantai eksotis dengan pemandangan indah">
            </div>
            <div class="mb-2">
                <label class="form-label">Harga</label>
                <input name="items[${i}][price]" class="form-control" placeholder="Contoh: 1500000">
            </div>
            <div class="mb-2">
                <label class="form-label">Gambar Destinasi</label>
                <input type="file" name="items[${i}][image]" class="form-control" accept="image/*">
                <small class="text-muted">Upload gambar destinasi (max 2MB)</small>
            </div>
            <div class="mt-2" id="preview_${i}"></div>
        `,
        schedule: `
            <div class="mb-2">
                <label class="form-label">Tujuan</label>
                <input name="items[${i}][destination]" class="form-control" placeholder="Contoh: Jakarta - Bandung">
            </div>
            <div class="mb-2">
                <label class="form-label">Tanggal / Jam</label>
                <input name="items[${i}][date]" class="form-control" placeholder="Contoh: 2026-07-01 08:00">
            </div>
            <div class="mb-2">
                <label class="form-label">Status</label>
                <input name="items[${i}][status]" class="form-control" placeholder="Contoh: Tersedia / Penuh">
            </div>
        `,
        features: `
            <div class="mb-2">
                <label class="form-label">Judul Fitur</label>
                <input name="items[${i}][title]" class="form-control" placeholder="Contoh: Nyaman">
            </div>
            <div class="mb-2">
                <label class="form-label">Deskripsi</label>
                <input name="items[${i}][desc]" class="form-control" placeholder="Contoh: Perjalanan yang nyaman dan aman">
            </div>
            <div class="mb-2">
                <label class="form-label">Icon</label>
                <input name="items[${i}][icon]" class="form-control" placeholder="Contoh: fa-car">
                <small class="text-muted">Gunakan icon dari FontAwesome (contoh: fa-car, fa-users)</small>
            </div>
        `,
        testimonials: `
            <div class="mb-2">
                <label class="form-label">Nama</label>
                <input name="items[${i}][name]" class="form-control" placeholder="Contoh: Budi Santoso">
            </div>
            <div class="mb-2">
                <label class="form-label">Testimoni</label>
                <textarea name="items[${i}][text]" class="form-control" rows="2" placeholder="Tulis testimoni..."></textarea>
            </div>
            <div class="mb-2">
                <label class="form-label">Foto Profil</label>
                <input type="file" name="items[${i}][image]" class="form-control" accept="image/*">
                <small class="text-muted">Upload foto profil (max 2MB)</small>
            </div>
            <div class="mt-2" id="preview_${i}"></div>
        `
    };

    return map[type] || '';
}

// ================= ADD ITEM - FIXED =================
function addItem() {
    if (!currentType) {
        alert('Pilih tipe section terlebih dahulu!');
        return;
    }

    const wrapper = document.getElementById('items-wrapper');
    const emptyState = document.getElementById('emptyState');
    
    // Hapus empty state jika ada
    if (emptyState) {
        emptyState.remove();
    }

    const card = document.createElement('div');
    card.className = 'card p-3 mb-3';
    card.id = `item-${index}`;

    card.innerHTML = `
        <div class="d-flex justify-content-between mb-2">
            <strong class="text-primary">Item #${index + 1}</strong>
            <button type="button" class="btn btn-danger btn-sm" onclick="removeItem(${index})">
                🗑 Hapus
            </button>
        </div>
        ${getFields(currentType, index)}
    `;

    wrapper.appendChild(card);
    items.push(index);
    index++;
}

// ================= REMOVE ITEM =================
function removeItem(id) {
    if (confirm('Yakin hapus item ini?')) {
        const element = document.getElementById(`item-${id}`);
        if (element) {
            element.remove();
            items = items.filter(i => i !== id);
            
            // Tampilkan empty state jika tidak ada items
            if (items.length === 0) {
                const wrapper = document.getElementById('items-wrapper');
                wrapper.innerHTML = `
                    <div class="text-muted p-3 border rounded" id="emptyState">
                        Belum ada items. Klik "Tambah Item" untuk menambahkan.
                    </div>
                `;
            }
        }
    }
}

// ================= TOGGLE MODE - FIXED =================
function toggleMode() {

    const val = document.getElementById('keySelect').value;
    currentType = val;

    const repeater = document.getElementById('repeaterSection');
    const jsonManual = document.getElementById('jsonManual');
    const mainImage = document.getElementById('mainImage');

    // Reset semua
    repeater.classList.add('d-none');
    jsonManual.classList.add('d-none');
    mainImage.classList.add('d-none');

    // Reset items
    const wrapper = document.getElementById('items-wrapper');
    wrapper.innerHTML = `
        <div class="text-muted p-3 border rounded" id="emptyState">
            Belum ada items. Klik "Tambah Item" untuk menambahkan.
        </div>
    `;
    items = [];
    index = 0;

    // Tampilkan sesuai tipe
    if (val === 'hero') {
        mainImage.classList.remove('d-none');
    }
    else if (val === 'cta') {
        jsonManual.classList.remove('d-none');
    }
    else if (['destinations','schedule','features','testimonials'].includes(val)) {
        repeater.classList.remove('d-none');
    }
}

// ================= PREVIEW IMAGE =================
function previewHeroImage(input) {
    const previewContainer = document.getElementById('imagePreview');
    const previewImg = document.getElementById('heroImagePreview');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewImg.src = e.target.result;
            previewContainer.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// ================= REMOVE HERO IMAGE =================
function removeHeroImage() {
    const input = document.querySelector('input[name="image"]');
    const previewContainer = document.getElementById('imagePreview');
    
    if (input) {
        input.value = '';
        previewContainer.classList.add('d-none');
    }
}

// ================= PREVIEW - FIXED =================
function previewData() {

    const form = document.getElementById('sectionForm');
    const formData = new FormData(form);

    // Tambahkan flag untuk preview
    formData.append('_preview', '1');

    // Tampilkan loading
    const previewBtn = document.querySelector('.btn-info');
    const originalText = previewBtn.innerHTML;
    previewBtn.innerHTML = '⏳ Loading...';
    previewBtn.disabled = true;

    fetch("{{ route('landing.preview.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('previewFrame').src =
                "{{ route('landing.preview') }}?t=" + Date.now();
            
            // Tampilkan notifikasi sukses
            showNotification('success', 'Preview berhasil dimuat');
        } else {
            showNotification('danger', data.message || 'Gagal membuat preview');
        }
    })
    .catch(error => {
        console.error('Preview error:', error);
        showNotification('danger', 'Terjadi kesalahan saat preview. Cek console.');
    })
    .finally(() => {
        // Reset button
        previewBtn.innerHTML = originalText;
        previewBtn.disabled = false;
    });
}

// ================= NOTIFICATION HELPER =================
function showNotification(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade-in`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    const container = document.querySelector('.container-fluid');
    container.insertBefore(alertDiv, container.firstChild);
    
    // Auto dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}

// ================= EVENT LISTENERS =================
document.addEventListener('DOMContentLoaded', function() {
    // Key select change
    document.getElementById('keySelect').addEventListener('change', toggleMode);
    toggleMode();
    
    // Hero image preview
    document.querySelector('input[name="image"]')?.addEventListener('change', function() {
        previewHeroImage(this);
    });
    
    // Form submit validation
    document.getElementById('sectionForm').addEventListener('submit', function(e) {
        const key = document.getElementById('keySelect').value;
        if (!key) {
            e.preventDefault();
            showNotification('danger', 'Silakan pilih tipe section terlebih dahulu!');
            return false;
        }
        
        // Validasi untuk hero
        if (key === 'hero') {
            const imageInput = document.querySelector('input[name="image"]');
            if (!imageInput || !imageInput.files || imageInput.files.length === 0) {
                e.preventDefault();
                showNotification('danger', 'Silakan upload gambar untuk hero section!');
                return false;
            }
        }
        
        // Validasi untuk repeater
        if (['destinations','schedule','features','testimonials'].includes(key)) {
            if (items.length === 0) {
                e.preventDefault();
                showNotification('danger', 'Silakan tambahkan minimal 1 item!');
                return false;
            }
        }
        
        return true;
    });
});

// Export ke global scope
window.addItem = addItem;
window.removeItem = removeItem;
window.previewData = previewData;
window.removeHeroImage = removeHeroImage;
window.previewHeroImage = previewHeroImage;

</script>
@endpush