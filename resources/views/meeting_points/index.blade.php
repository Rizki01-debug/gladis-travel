@extends('layouts.app')

@section('content')
    <h3 class="mb-3">📍 Data Meeting Points</h3>

    {{-- 🔥 ALERT --}}
    @if (session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="mb-3">
        <a href="{{ route('meeting-points.create') }}" class="btn btn-primary">
            ➕ Tambah Titik
        </a>
    </div>

    {{-- 🔥 MAP --}}
    <div id="map" style="height: 400px;" class="mb-4 rounded shadow"></div>

    {{-- 🔥 TABLE --}}
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="50">#</th>
                        <th>Nama</th>
                        <th>Kota</th>
                        <th>Koordinat</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse($points as $i => $point)
                        <tr>
                            <td>{{ $i + 1 }}</td>

                            <td>
                                <strong>{{ $point->name }}</strong>
                            </td>

                            <td>{{ $point->city->name ?? '-' }}</td>

                            <td>
                                <small>
                                    {{ $point->latitude }},
                                    {{ $point->longitude }}
                                </small>
                            </td>

                            <td>
                                <a href="{{ route('meeting-points.edit', $point->id) }}" class="btn btn-warning btn-sm">
                                    ✏️
                                </a>

                                <form action="{{ route('meeting-points.destroy', $point->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')

                                    <button class="btn btn-danger btn-sm" onclick="return confirm('Hapus titik ini?')">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Belum ada meeting point
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>
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

            let map = L.map('map').setView([-6.2, 106.8], 10);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap'
            }).addTo(map);

            let points = @json($points);

            let bounds = [];

            points.forEach(function(point) {

                if (point.latitude && point.longitude) {

                    let lat = parseFloat(point.latitude);
                    let lng = parseFloat(point.longitude);

                    let marker = L.marker([lat, lng]).addTo(map);

                    marker.bindPopup(`
                <b>${point.name}</b><br>
                Kota: ${point.city ? point.city.name : '-'}
            `);

                    bounds.push([lat, lng]);
                }
            });

            // 🔥 AUTO ZOOM
            if (bounds.length > 0) {
                map.fitBounds(bounds);
            }

        });
    </script>
@endpush
