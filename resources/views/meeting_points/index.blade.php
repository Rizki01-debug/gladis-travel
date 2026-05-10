@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">📍 Data Meeting Point</h4>
                <small class="text-muted">Kelola titik penjemputan berdasarkan kota</small>
            </div>

            <a href="{{ route('meeting-points.create') }}" class="btn btn-primary btn-premium">
                ➕ Tambah Titik
            </a>
        </div>

        {{-- ================= FILTER ================= --}}
        <div class="card card-premium p-3 border-0 mb-3">
            <form method="GET">
                <div class="row g-2">

                    {{-- SEARCH --}}
                    <div class="col-md-6">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama meeting point..."
                            value="{{ request('search') }}">
                    </div>

                    {{-- FILTER CITY --}}
                    <div class="col-md-4">
                        <select name="city_id" class="form-control">
                            <option value="">Semua Kota</option>
                            @foreach ($cities as $city)
                                <option value="{{ $city->id }}" {{ request('city_id') == $city->id ? 'selected' : '' }}>
                                    {{ $city->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">🔍 Filter</button>
                    </div>

                </div>
            </form>
        </div>

        {{-- ================= MAP ================= --}}
        <div class="card card-premium border-0 mb-3">
            <div class="card-body">
                <div id="map" style="height: 400px; border-radius:12px;"></div>
            </div>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead>
                            <tr class="text-center">
                                <th width="50">#</th>
                                <th class="text-start">Nama</th>
                                <th>Kota</th>
                                <th>Koordinat</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($points as $point)
                                <tr>

                                    {{-- NUMBER --}}
                                    <td class="text-center">
                                        {{ $points->firstItem() + $loop->index }}
                                    </td>

                                    {{-- NAME --}}
                                    <td class="fw-semibold">
                                        {{ $point->name }}
                                    </td>

                                    {{-- CITY --}}
                                    <td class="text-center">
                                        {{ $point->city->name ?? '-' }}
                                    </td>

                                    {{-- COORD --}}
                                    <td class="text-center small text-muted">
                                        {{ $point->coordinate }}
                                    </td>

                                    {{-- ACTION --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">

                                            <a href="{{ route('meeting-points.edit', $point->id) }}"
                                                class="btn btn-warning btn-sm btn-premium">
                                                ✏️
                                            </a>

                                            <form action="{{ route('meeting-points.destroy', $point->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus titik ini?')">
                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm btn-premium">
                                                    🗑️
                                                </button>
                                            </form>

                                        </div>
                                    </td>

                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5">
                                        <div class="empty-state">
                                            📭 Belum ada meeting point
                                            <br>
                                            <small>Tambahkan titik untuk mulai membuat rute</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($points->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $points->firstItem() }} - {{ $points->lastItem() }}
                            dari {{ $points->total() }} data
                        </div>

                        <div>
                            {{ $points->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
@endpush

@push('scripts')
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {

            let map = L.map('map');

            let defaultView = [-2.5, 118]; // Indonesia center
            map.setView(defaultView, 5);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let points = @json($points->items()); // 🔥 FIX pagination
            let bounds = [];

            points.forEach(point => {

                if (!point.latitude || !point.longitude) return;

                let lat = parseFloat(point.latitude);
                let lng = parseFloat(point.longitude);

                if (isNaN(lat) || isNaN(lng)) return;

                let marker = L.marker([lat, lng]).addTo(map);

                marker.bindPopup(`
            <b>${point.name}</b><br>
            Kota: ${point.city ? point.city.name : '-'}
        `);

                bounds.push([lat, lng]);
            });

            if (bounds.length > 0) {
                map.fitBounds(bounds, {
                    padding: [40, 40]
                });
            }

            // 🔥 FIX BUG MAP BLANK
            setTimeout(() => {
                map.invalidateSize();
            }, 300);

        });
    </script>
@endpush
