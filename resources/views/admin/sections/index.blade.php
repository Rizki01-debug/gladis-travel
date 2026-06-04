@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">

            <div>
                <h3 class="fw-bold mb-1">
                    🎨 Content Management System
                </h3>
                <p class="text-muted mb-0">
                    Kelola seluruh konten Landing Page GLADIS Travel
                </p>
            </div>

            <a href="{{ route('admin.sections.create') }}" class="btn btn-primary btn-premium">
                <i class="bi bi-plus-circle"></i>
                Tambah Section
            </a>

        </div>

        {{-- ================= SUMMARY CARD ================= --}}
        <div class="row g-3 mb-4">

            <div class="col-md-6">
                <div class="card border-0 shadow-sm card-premium">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <small class="text-muted">
                                    Total Section
                                </small>

                                <h3 class="fw-bold mb-0">
                                    {{ $sections->total() }}
                                </h3>
                            </div>

                            <div class="fs-1 text-primary">
                                <i class="bi bi-layout-text-window"></i>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card border-0 shadow-sm card-premium">
                    <div class="card-body">

                        <div class="d-flex justify-content-between align-items-center">

                            <div>
                                <small class="text-muted">
                                    Landing Page
                                </small>

                                <h3 class="fw-bold mb-0">
                                    {{ $sections->count() }}
                                </h3>
                            </div>

                            <div class="fs-1 text-success">
                                <i class="bi bi-globe"></i>
                            </div>

                        </div>

                    </div>
                </div>
            </div>

        </div>

        {{-- ================= TABLE ================= --}}
        <div class="card border-0 shadow-sm card-premium">

            <div class="card-body">

                <div class="table-responsive">

                    <table class="table align-middle table-hover mb-0">

                        <thead class="table-light">
                            <tr>
                                <th width="60">#</th>
                                <th>Page</th>
                                <th width="180">Key</th>
                                <th>Title</th>
                                <th width="100">Order</th>
                                <th width="170" class="text-center">
                                    Aksi
                                </th>
                            </tr>
                        </thead>

                        <tbody>

                            @forelse($sections as $i => $s)
                                <tr>

                                    {{-- NUMBER --}}
                                    <td>
                                        {{ $sections->firstItem() + $i }}
                                    </td>

                                    {{-- PAGE --}}
                                    <td class="fw-semibold">
                                        {{ $s->page->title ?? '-' }}
                                    </td>

                                    {{-- KEY --}}
                                    <td>

                                        @php
                                            $colors = [
                                                'hero' => 'primary',
                                                'destinations' => 'success',
                                                'schedule' => 'warning',
                                                'features' => 'info',
                                                'testimonials' => 'secondary',
                                                'cta' => 'danger',
                                            ];

                                            $badge = $colors[$s->key] ?? 'dark';
                                        @endphp

                                        <span class="badge bg-{{ $badge }}">
                                            {{ $s->key }}
                                        </span>

                                    </td>

                                    {{-- TITLE --}}
                                    <td>
                                        {{ $s->title ?? '-' }}
                                    </td>

                                    {{-- ORDER --}}
                                    <td class="text-center">
                                        {{ $s->order }}
                                    </td>

                                    {{-- ACTION --}}
                                    <td>

                                        <div class="d-flex justify-content-center gap-2">

                                            {{-- EDIT --}}
                                            <a href="{{ route('admin.sections.edit', $s->id) }}"
                                                class="btn btn-warning btn-sm">

                                                <i class="bi bi-pencil-square"></i>

                                            </a>

                                            {{-- DELETE --}}
                                            <form action="{{ route('admin.sections.destroy', $s->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus section ini?')">

                                                @csrf
                                                @method('DELETE')

                                                <button class="btn btn-danger btn-sm">

                                                    <i class="bi bi-trash"></i>

                                                </button>

                                            </form>

                                        </div>

                                    </td>

                                </tr>

                            @empty

                                <tr>

                                    <td colspan="6">

                                        <div class="text-center py-5">

                                            <div class="fs-1 mb-3">
                                                📭
                                            </div>

                                            <h5 class="fw-bold">
                                                Belum Ada Section
                                            </h5>

                                            <p class="text-muted mb-0">
                                                Tambahkan section pertama untuk Landing Page GLADIS Travel.
                                            </p>

                                        </div>

                                    </td>

                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($sections->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="small text-muted">

                            Menampilkan
                            {{ $sections->firstItem() }}
                            -
                            {{ $sections->lastItem() }}

                            dari

                            {{ $sections->total() }}

                            data

                        </div>

                        <div>
                            {{ $sections->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>

        </div>

    </div>
@endsection
