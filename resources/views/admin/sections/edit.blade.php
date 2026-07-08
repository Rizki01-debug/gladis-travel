@extends('layouts.app')

@section('content')

    <div class="container-fluid fade-in">

        {{-- HEADER --}}
        <div class="mb-4">
            <h4 class="fw-bold mb-1">✏ Edit Section</h4>
            <small class="text-muted">Perbarui konten landing page</small>
        </div>

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                <strong>Terjadi kesalahan:</strong>
                <ul class="mb-0 mt-2">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
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

                <form id="sectionForm" method="POST" action="{{ route('admin.sections.update', $section->id) }}"
                    enctype="multipart/form-data">

                    @csrf
                    @method('PUT')

                    <div class="card shadow-sm border-0">
                        <div class="card-body">

                            {{-- PAGE --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Page</label>
                                <select name="page_id" class="form-control" required>
                                    @foreach ($pages as $id => $title)
                                        <option value="{{ $id }}"
                                            {{ old('page_id', $section->page_id) == $id ? 'selected' : '' }}>
                                            {{ $title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- KEY --}}
                            <input type="hidden" name="key" value="{{ $section->key }}">

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Key Section</label>
                                <input type="text" class="form-control" value="{{ ucfirst($section->key) }}" readonly>
                            </div>

                            {{-- TITLE --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Title</label>
                                <input type="text" name="title" class="form-control"
                                    value="{{ old('title', $section->title) }}">
                            </div>

                            {{-- CONTENT --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Content</label>
                                <textarea name="content" class="form-control" rows="3">{{ old('content', $section->content) }}</textarea>
                            </div>

                            {{-- ================= IMAGE HERO ================= --}}
                            <div class="mb-3" id="mainImage">
                                <label class="form-label fw-semibold">Ganti Image</label>

                                <input type="file" name="image" class="form-control" accept="image/*">

                                @if ($section->image)
                                    <div class="mt-2">
                                        <img src="{{ asset('storage/' . $section->image) }}" width="180"
                                            class="rounded border shadow-sm">
                                        <br>
                                        <small class="text-muted">Current: {{ $section->image }}</small>
                                    </div>
                                @endif
                            </div>

                            {{-- ================= REPEATER ================= --}}
                            <div id="repeaterSection" class="mb-3 d-none">
                                <label class="form-label fw-semibold">Items</label>

                                <div id="items-wrapper"></div>

                                <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addItem()">
                                    + Tambah Item
                                </button>
                            </div>

                            {{-- JSON --}}
                            <div id="jsonManual" class="mb-3 d-none">
                                <label class="form-label fw-semibold">Extra JSON</label>
                                <textarea name="extra" class="form-control" rows="4">{{ old('extra', $section->extra ? json_encode($section->extra, JSON_PRETTY_PRINT) : '') }}</textarea>
                            </div>

                            {{-- ORDER --}}
                            <div class="mb-3">
                                <label class="form-label">Order</label>
                                <input type="number" name="order" class="form-control"
                                    value="{{ old('order', $section->order ?? 0) }}">
                            </div>

                            {{-- ================= STATUS - FIXED ================= --}}
                            <div class="form-check mb-4">
                                {{-- 🔥 HAPUS hidden input --}}
                                {{-- <input type="hidden" name="is_active" value="0"> --}}

                                @php
                                    // Ambil nilai dengan benar
                                    $oldIsActive = old('is_active');
                                    $dbIsActive = $section->is_active;

                                    // Jika ada old value (setelah error), gunakan old
                                    if (!is_null($oldIsActive)) {
                                        $isChecked = (bool) $oldIsActive;
                                    } else {
                                        $isChecked = (bool) $dbIsActive;
                                    }
                                @endphp

                                <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                    id="isActiveCheckbox" {{ $isChecked ? 'checked' : '' }}>
                                <label class="form-check-label" for="isActiveCheckbox">
                                    Aktifkan Section
                                </label>
                                <small class="text-muted d-block">
                                    Status saat ini:
                                    <span class="badge {{ $isChecked ? 'bg-success' : 'bg-danger' }}">
                                        {{ $isChecked ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </small>
                            </div>

                            {{-- BUTTON --}}
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('admin.sections.index') }}" class="btn btn-secondary">
                                    ← Kembali
                                </a>

                                <button class="btn btn-primary" id="submitBtn">
                                    💾 Update
                                </button>
                            </div>

                            {{-- PREVIEW --}}
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

                        <iframe id="previewFrame" src="{{ route('landing.preview') }}" width="100%" height="700"
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
        let items = @json($section->extra['items'] ?? []);
        let currentType = "{{ $section->key }}";
        let index = items.length;

        // ================= TEMPLATE =================
        function getFields(type, i, item = {}) {

            const map = {
                destinations: `
            <input name="items[${i}][title]" class="form-control mb-2" value="${item.title || ''}" placeholder="Nama Destinasi">
            <input name="items[${i}][desc]" class="form-control mb-2" value="${item.desc || ''}" placeholder="Deskripsi">
            <input name="items[${i}][price]" class="form-control mb-2" value="${item.price || ''}" placeholder="Harga">
            ${item.image ? `<img src="{{ asset('storage/${item.image}') }}" width="120" class="mb-2 rounded">` : ''}
            <input type="file" name="items[${i}][image]" class="form-control mb-2">
            <small class="text-muted">Upload gambar baru untuk mengganti</small>
        `,
                schedule: `
            <input name="items[${i}][destination]" class="form-control mb-2" value="${item.destination || ''}" placeholder="Tujuan">
            <input name="items[${i}][date]" class="form-control mb-2" value="${item.date || ''}" placeholder="Tanggal">
            <input name="items[${i}][status]" class="form-control mb-2" value="${item.status || ''}" placeholder="Status">
        `,
                features: `
            <input name="items[${i}][title]" class="form-control mb-2" value="${item.title || ''}" placeholder="Judul">
            <input name="items[${i}][desc]" class="form-control mb-2" value="${item.desc || ''}" placeholder="Deskripsi">
            <input name="items[${i}][icon]" class="form-control mb-2" value="${item.icon || ''}" placeholder="Icon">
        `,
                testimonials: `
            <input name="items[${i}][name]" class="form-control mb-2" value="${item.name || ''}" placeholder="Nama">
            <textarea name="items[${i}][text]" class="form-control mb-2" rows="2">${item.text || ''}</textarea>
            ${item.image ? `<img src="{{ asset('storage/${item.image}') }}" width="100" class="mb-2 rounded">` : ''}
            <input type="file" name="items[${i}][image]" class="form-control mb-2">
            <small class="text-muted">Upload gambar baru untuk mengganti</small>
        `
            };

            return map[type] || '';
        }

        // ================= RENDER =================
        function renderItems() {

            const wrapper = document.getElementById('items-wrapper');
            wrapper.innerHTML = '';

            if (items.length === 0) {
                wrapper.innerHTML =
                    '<div class="text-muted p-3 border rounded">Belum ada items. Klik "Tambah Item" untuk menambahkan.</div>';
                return;
            }

            items.forEach((item, i) => {

                const card = document.createElement('div');
                card.className = 'card p-3 mb-3';

                card.innerHTML = `
            ${getFields(currentType, i, item)}
            <button type="button" class="btn btn-danger btn-sm mt-2">🗑 Hapus Item</button>
        `;

                card.querySelector('button').onclick = () => {
                    if (confirm('Yakin hapus item ini?')) {
                        items.splice(i, 1);
                        renderItems();
                    }
                };

                wrapper.appendChild(card);
            });
        }

        // ================= ADD =================
        function addItem() {
            items.push({});
            renderItems();
            const wrapper = document.getElementById('items-wrapper');
            const lastCard = wrapper.lastElementChild;
            if (lastCard) {
                lastCard.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        }

        // ================= INIT =================
        function init() {

            const repeater = document.getElementById('repeaterSection');
            const jsonManual = document.getElementById('jsonManual');
            const mainImage = document.getElementById('mainImage');

            repeater.classList.add('d-none');
            jsonManual.classList.add('d-none');
            mainImage.classList.add('d-none');

            if (currentType === 'hero') {
                mainImage.classList.remove('d-none');
            } else if (currentType === 'cta') {
                jsonManual.classList.remove('d-none');
            } else if (['destinations', 'schedule', 'features', 'testimonials'].includes(currentType)) {
                repeater.classList.remove('d-none');
                renderItems();
            } else {
                jsonManual.classList.remove('d-none');
            }
        }

        document.addEventListener('DOMContentLoaded', init);

        // ================= PREVIEW =================
        function previewData() {

            const form = document.getElementById('sectionForm');
            const formData = new FormData(form);

            if (items.length > 0) {
                items.forEach((item, i) => {
                    Object.keys(item).forEach(key => {
                        if (key !== 'image' && item[key]) {
                            formData.append(`items[${i}][${key}]`, item[key]);
                        }
                    });
                });
            }

            // 🔥 DEBUG: Log data yang dikirim
            console.log('Preview Data:');
            for (let pair of formData.entries()) {
                console.log(pair[0], pair[1]);
            }

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
                    } else {
                        alert('Gagal preview. Cek console untuk detail.');
                    }
                })
                .catch(error => {
                    console.error('Preview error:', error);
                    alert('Terjadi kesalahan saat preview.');
                })
                .finally(() => {
                    previewBtn.innerHTML = originalText;
                    previewBtn.disabled = false;
                });
        }

        // ================= FIX: Validasi Form Sebelum Submit =================
        document.getElementById('sectionForm').addEventListener('submit', function(e) {
            const checkbox = document.getElementById('isActiveCheckbox');
            const hiddenInput = document.querySelector('input[name="is_active"]');

            // 🔥 DEBUG: Log nilai yang akan dikirim
            console.log('Submit Data:');
            console.log('Checkbox checked:', checkbox.checked);
            console.log('Hidden value:', hiddenInput.value);
            console.log('is_active akan dikirim:', checkbox.checked ? '1' : '0');

            // Update hidden input sesuai checkbox
            hiddenInput.value = checkbox.checked ? '1' : '0';

            return true;
        });

        // ================= FIX: Toggle Status Visual =================
        document.getElementById('isActiveCheckbox')?.addEventListener('change', function() {
            const statusBadge = document.querySelector('.badge.bg-success, .badge.bg-danger');
            if (statusBadge) {
                if (this.checked) {
                    statusBadge.className = 'badge bg-success';
                    statusBadge.textContent = 'Aktif';
                } else {
                    statusBadge.className = 'badge bg-danger';
                    statusBadge.textContent = 'Nonaktif';
                }
            }
        });

        // Export ke global scope
        window.addItem = addItem;
        window.previewData = previewData;
    </script>
@endpush
