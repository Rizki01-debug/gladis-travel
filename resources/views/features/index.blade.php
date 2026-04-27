@extends('layouts.app')

@section('content')

@php
    // 🔥 AMBIL DARI CONFIG (INI YANG BENAR)
    $featureRoles = config('features.roles', []);
@endphp

<div class="container-fluid fade-in">

    {{-- ================= HEADER ================= --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">⚙️ Pengaturan Fitur Sistem</h4>
        <small class="text-muted">Atur akses fitur berdasarkan role</small>
    </div>

    {{-- ================= ALERT ================= --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('features.bulkUpdate') }}">
        @csrf

        <div class="row">

            @forelse ($grouped as $featureName => $items)

                @php
                    // 🔥 AMBIL ROLE YANG DIIZINKAN DARI CONFIG
                    $allowedRoles = $featureRoles[$featureName] ?? [];

                    // 🔥 FILTER DATA SESUAI ROLE
                    $filteredItems = $items->filter(function($item) use ($allowedRoles) {
                        return in_array($item->role, $allowedRoles);
                    });
                @endphp

                {{-- 🔥 SKIP kalau tidak ada role --}}
                @if ($filteredItems->isEmpty())
                    @continue
                @endif

                <div class="col-lg-6 col-md-6 mb-4">

                    <div class="card card-premium border-0 shadow-sm h-100">

                        <div class="card-body">

                            {{-- ================= TITLE ================= --}}
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-semibold mb-0 text-uppercase">
                                    {{ str_replace('_', ' ', $featureName) }}
                                </h6>

                                <span class="badge bg-light text-dark">
                                    {{ $filteredItems->count() }} role
                                </span>
                            </div>

                            {{-- ================= ROLE LIST ================= --}}
                            @foreach ($filteredItems as $item)

                                <div class="d-flex justify-content-between align-items-center mb-2 px-2 py-1 rounded hover-bg">

                                    {{-- ROLE --}}
                                    <span class="text-capitalize">
                                        {{ str_replace('_', ' ', $item->role) }}
                                    </span>

                                    {{-- SWITCH --}}
                                    <div class="form-check form-switch m-0">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="features[{{ $item->name }}][{{ $item->role }}]"
                                            value="1"
                                            {{ $item->is_active ? 'checked' : '' }}
                                        >
                                    </div>

                                </div>

                            @endforeach

                        </div>

                    </div>

                </div>

            @empty

                <div class="col-12">
                    <div class="alert alert-warning text-center">
                        🚫 Tidak ada data fitur
                    </div>
                </div>

            @endforelse

        </div>

        {{-- ================= ACTION ================= --}}
        <div class="mt-4 d-flex justify-content-end gap-2">

            <button type="reset" class="btn btn-light border">
                Reset
            </button>

            <button class="btn btn-success btn-premium">
                💾 Simpan Perubahan
            </button>

        </div>

    </form>

</div>

@endsection


{{-- ================= STYLE ================= --}}
@push('styles')
<style>
.hover-bg:hover {
    background: rgba(0,0,0,0.04);
    transition: 0.2s;
}
</style>
@endpush