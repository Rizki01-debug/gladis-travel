@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">📍 Data Kota</h4>
                <small class="text-muted">Kelola data kota & koordinat lokasi</small>
            </div>

            <a href="{{ route('cities.create') }}" class="btn btn-primary btn-premium">
                ➕ Tambah Kota
            </a>
        </div>

        {{-- ================= CARD ================= --}}
        <div class="card card-premium border-0">

            <div class="card-body">

                {{-- ================= SEARCH ================= --}}
                <form method="GET" class="mb-3">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control"
                                placeholder="🔍 Cari nama kota...">
                        </div>

                        <div class="col-md-2">
                            <button class="btn btn-primary w-100">
                                Filter
                            </button>
                        </div>

                        <div class="col-md-2">
                            <a href="{{ route('cities.index') }}" class="btn btn-secondary w-100">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                {{-- ================= TABLE ================= --}}
                <div class="table-responsive">
                    <table class="table table-premium align-middle">

                        <thead>
                            <tr class="text-center">
                                <th width="60">#</th>
                                <th class="text-start">Nama Kota</th>
                                <th>Latitude</th>
                                <th>Longitude</th>
                                <th>Status</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($cities as $city)
                                <tr>

                                    {{-- NUMBER --}}
                                    <td class="text-center fw-semibold">
                                        {{ $cities->firstItem() + $loop->index }}
                                    </td>

                                    {{-- NAME --}}
                                    <td>
                                        <div class="fw-semibold">
                                            {{ $city->name }}
                                        </div>
                                    </td>

                                    {{-- LAT --}}
                                    <td class="text-center">
                                        {{ $city->latitude ?? '-' }}
                                    </td>

                                    {{-- LNG --}}
                                    <td class="text-center">
                                        {{ $city->longitude ?? '-' }}
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @if ($city->latitude && $city->longitude)
                                            <span class="badge-soft bg-success text-white">
                                                ✔ Valid
                                            </span>
                                        @else
                                            <span class="badge-soft bg-danger text-white">
                                                ⚠ Belum lengkap
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ACTION --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">

                                            <a href="{{ route('cities.edit', $city->id) }}"
                                                class="btn btn-warning btn-sm btn-premium">
                                                ✏️
                                            </a>

                                            <form action="{{ route('cities.destroy', $city->id) }}" method="POST"
                                                onsubmit="return confirmDelete(this)">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm btn-premium btn-delete">
                                                    🗑️
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            🚫 Belum ada data kota <br>
                                            <small>Tambahkan kota terlebih dahulu</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($cities->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $cities->firstItem() }} - {{ $cities->lastItem() }}
                            dari {{ $cities->total() }} data
                        </div>

                        <div>
                            {{ $cities->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

        {{-- ================= MAP ================= --}}
        <div class="card card-premium border-0 mt-3">
            <div class="card-body">

                <h6 class="mb-3">🗺 Preview Lokasi Kota</h6>

                <div id="map" style="height:400px;border-radius:12px;"></div>

            </div>
        </div>

    </div>
@endsection

{{-- ================= LEAFLET ================= --}}
@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            const cities = @json($cities->items());

            let map = L.map('map').setView([-2.5, 118], 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);

            let bounds = [];

            cities.forEach(c => {

                if (c.latitude && c.longitude) {

                    let lat = parseFloat(c.latitude);
                    let lng = parseFloat(c.longitude);

                    let marker = L.marker([lat, lng]).addTo(map)
                        .bindPopup(`<b>${c.name}</b>`);

                    bounds.push([lat, lng]);
                }
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds);
            }

            setTimeout(() => map.invalidateSize(), 200);
        });

        // 🔥 DELETE SAFE
        function confirmDelete(form) {
            if (!confirm('Yakin ingin menghapus kota ini?')) return false;

            const btn = form.querySelector('.btn-delete');
            if (btn) {
                btn.disabled = true;
                btn.innerText = '...';
            }

            return true;
        }
    </script>
@endpush
