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
                        </div>

                        {{-- TITLE --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title</label>
                            <input type="text" name="title" class="form-control" value="{{ old('title') }}">
                        </div>

                        {{-- CONTENT --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Content</label>
                            <textarea name="content" class="form-control" rows="3">{{ old('content') }}</textarea>
                        </div>

                        {{-- IMAGE --}}
                        <div class="mb-3 d-none" id="mainImage">
                            <label class="form-label fw-semibold">Image (Hero)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                        </div>

                        {{-- REPEATER --}}
                        <div class="mb-3 d-none" id="repeaterSection">
                            <label class="form-label fw-semibold">Items</label>

                            <div id="items-wrapper"></div>

                            <button type="button" class="btn btn-sm btn-primary mt-2" onclick="addItem()">
                                + Tambah Item
                            </button>
                        </div>

                        {{-- JSON --}}
                        <div class="mb-3 d-none" id="jsonManual">
                            <label class="form-label fw-semibold">Extra JSON</label>
                            <textarea name="extra" class="form-control" rows="4">{{ old('extra') }}</textarea>
                        </div>

                        {{-- ORDER --}}
                        <div class="mb-3">
                            <label class="form-label">Order</label>
                            <input type="number" name="order" class="form-control" value="{{ old('order', 0) }}">
                        </div>

                        {{-- STATUS --}}
                        <div class="form-check mb-4">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                {{ old('is_active', true) ? 'checked' : '' }}>
                            <label class="form-check-label">Aktifkan Section</label>
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

// ================= FIELD TEMPLATE =================
function getFields(type, i) {

    const map = {
        destinations: `
            <input name="items[${i}][title]" class="form-control mb-2" placeholder="Nama Destinasi">
            <input name="items[${i}][desc]" class="form-control mb-2" placeholder="Deskripsi">
            <input name="items[${i}][price]" class="form-control mb-2" placeholder="Harga">
            <input type="file" name="items[${i}][image]" class="form-control mb-2">
        `,
        schedule: `
            <input name="items[${i}][destination]" class="form-control mb-2" placeholder="Tujuan">
            <input name="items[${i}][date]" class="form-control mb-2" placeholder="Tanggal / Jam">
            <input name="items[${i}][status]" class="form-control mb-2" placeholder="Status">
        `,
        features: `
            <input name="items[${i}][title]" class="form-control mb-2" placeholder="Judul">
            <input name="items[${i}][desc]" class="form-control mb-2" placeholder="Deskripsi">
            <input name="items[${i}][icon]" class="form-control mb-2" placeholder="Icon (fa-car)">
        `,
        testimonials: `
            <input name="items[${i}][name]" class="form-control mb-2" placeholder="Nama">
            <textarea name="items[${i}][text]" class="form-control mb-2" placeholder="Testimoni"></textarea>
            <input type="file" name="items[${i}][image]" class="form-control mb-2">
        `
    };

    return map[type] || '';
}

// ================= ADD ITEM =================
function addItem() {
    if (!currentType) return;

    const wrapper = document.getElementById('items-wrapper');

    const card = document.createElement('div');
    card.className = 'card p-3 mb-3';

    card.innerHTML = `
        ${getFields(currentType, index)}
        <button type="button" class="btn btn-danger btn-sm mt-2">Hapus</button>
    `;

    card.querySelector('button').onclick = () => card.remove();

    wrapper.appendChild(card);
    index++;
}

// ================= TOGGLE =================
function toggleMode() {

    const val = document.getElementById('keySelect').value;
    currentType = val;

    const repeater = document.getElementById('repeaterSection');
    const jsonManual = document.getElementById('jsonManual');
    const mainImage = document.getElementById('mainImage');

    repeater.classList.add('d-none');
    jsonManual.classList.add('d-none');
    mainImage.classList.add('d-none');

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

document.getElementById('keySelect').addEventListener('change', toggleMode);
toggleMode();

// ================= PREVIEW =================
function previewData() {

    const form = document.getElementById('sectionForm');
    const formData = new FormData(form);

    fetch("{{ route('landing.preview.store') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': "{{ csrf_token() }}"
        },
        body: formData
    })
    .then(res => res.json())
    .then(() => {
        document.getElementById('previewFrame').src =
            "{{ route('landing.preview') }}?t=" + Date.now();
    });
}
</script>
@endpush