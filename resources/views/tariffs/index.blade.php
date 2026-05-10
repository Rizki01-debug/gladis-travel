@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">💰 Data Tarif</h4>
                <small class="text-muted">Kelola harga perjalanan</small>
            </div>

            <a href="{{ route('tariffs.create') }}" class="btn btn-primary btn-premium">
                ➕ Tambah Tarif
            </a>
        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card card-premium border-0">
            <div class="card-body">

                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">

                        <thead class="text-center">
                            <tr>
                                <th>Nama Tarif</th>
                                <th>Harga Dasar</th>
                                <th>Harga / KM</th>
                                <th width="150">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($tariffs as $t)
                                <tr>

                                    {{-- NAMA --}}
                                    <td class="fw-semibold">
                                        {{ $t->name }}
                                    </td>

                                    {{-- BASE PRICE --}}
                                    <td class="text-center">
                                        Rp {{ number_format($t->base_price, 0, ',', '.') }}
                                    </td>

                                    {{-- PRICE PER KM --}}
                                    <td class="text-center">
                                        Rp {{ number_format($t->price_per_km, 0, ',', '.') }}
                                    </td>

                                    {{-- AKSI --}}
                                    <td class="text-center">

                                        {{-- EDIT --}}
                                        <a href="{{ route('tariffs.edit', $t->id) }}" class="btn btn-warning btn-sm">
                                            ✏️
                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('tariffs.destroy', $t->id) }}" method="POST"
                                            class="d-inline" onsubmit="return confirm('Yakin hapus tarif ini?')">

                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm">
                                                🗑️
                                            </button>
                                        </form>

                                    </td>
                                </tr>

                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="text-center text-muted py-4">
                                            🚫 Belum ada data tarif
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

            </div>
        </div>

    </div>
@endsection
