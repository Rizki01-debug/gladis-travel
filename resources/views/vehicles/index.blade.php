@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">🚐 Data Kendaraan</h4>
                <small class="text-muted">Kelola armada kendaraan travel</small>
            </div>

            <a href="{{ route('vehicles.create') }}" class="btn btn-primary btn-premium">
                ➕ Tambah Kendaraan
            </a>
        </div>

        {{-- ================= SEARCH ================= --}}
        <div class="card card-premium p-3 border-0 mb-3">
            <form method="GET">
                <div class="row g-2">

                    <div class="col-md-10">
                        <input type="text" name="search" class="form-control" placeholder="Cari nama / plat nomor..."
                            value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <button class="btn btn-primary w-100">🔍 Cari</button>
                    </div>

                </div>
            </form>
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
                                <th>Plat Nomor</th>
                                <th>Kapasitas</th>
                                <th>Status</th>
                                <th width="140">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($vehicles as $v)
                                <tr>

                                    {{-- NUMBER --}}
                                    <td class="text-center">
                                        {{ $vehicles->firstItem() + $loop->index }}
                                    </td>

                                    {{-- NAME --}}
                                    <td class="fw-semibold">
                                        {{ $v->name }}
                                    </td>

                                    {{-- PLATE --}}
                                    <td class="text-center">
                                        <span class="badge-soft bg-dark text-white">
                                            {{ $v->plate_number }}
                                        </span>
                                    </td>

                                    {{-- CAPACITY --}}
                                    <td class="text-center">
                                        🚶 {{ $v->seat_capacity }} Kursi
                                    </td>

                                    {{-- STATUS --}}
                                    <td class="text-center">
                                        @if ($v->status === 'active')
                                            <span class="badge-soft bg-success text-white">
                                                Aktif
                                            </span>
                                        @else
                                            <span class="badge-soft bg-secondary text-white">
                                                Nonaktif
                                            </span>
                                        @endif
                                    </td>

                                    {{-- ACTION --}}
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-1">

                                            {{-- EDIT --}}
                                            <a href="{{ route('vehicles.edit', $v->id) }}"
                                                class="btn btn-warning btn-sm btn-premium">
                                                ✏️
                                            </a>

                                            {{-- DELETE --}}
                                            <form action="{{ route('vehicles.destroy', $v->id) }}" method="POST"
                                                onsubmit="return confirm('Hapus kendaraan ini?')">
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
                                    <td colspan="6">
                                        <div class="empty-state">
                                            🚫 Belum ada kendaraan
                                            <br>
                                            <small>Silakan tambahkan kendaraan terlebih dahulu</small>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($vehicles->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        {{-- INFO --}}
                        <div class="text-muted small">
                            Menampilkan {{ $vehicles->firstItem() }} - {{ $vehicles->lastItem() }}
                            dari {{ $vehicles->total() }} data
                        </div>

                        {{-- PAGINATION --}}
                        <div>
                            {{ $vehicles->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection
