@extends('layouts.app')

@section('content')

<div class="container-fluid fade-in">

    {{-- ================= HEADER ================= --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h4 class="fw-bold mb-1">📦 Section CMS</h4>
            <small class="text-muted">Kelola konten landing page</small>
        </div>

        <a href="{{ route('admin.sections.create') }}" class="btn btn-primary btn-premium">
            ➕ Tambah Section
        </a>
    </div>

    {{-- ================= ALERT ================= --}}
    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    {{-- ================= TABLE ================= --}}
    <div class="card card-premium border-0">
        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">

                    <thead>
                        <tr class="text-center">
                            <th width="60">#</th>
                            <th class="text-start">Page</th>
                            <th>Key</th>
                            <th class="text-start">Title</th>
                            <th width="80">Order</th>
                            <th width="120">Status</th>
                            <th width="180">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($sections as $i => $s)
                            <tr>

                                {{-- NUMBER --}}
                                <td class="text-center">
                                    {{ $sections->firstItem() + $i }}
                                </td>

                                {{-- PAGE --}}
                                <td class="fw-semibold">
                                    {{ $s->page->title ?? '-' }}
                                </td>

                                {{-- KEY --}}
                                <td class="text-center">
                                    <span class="badge bg-info text-dark">
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

                                {{-- STATUS --}}
                                <td class="text-center">
                                    <span class="badge {{ $s->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $s->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>

                                {{-- ACTION --}}
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">

                                        {{-- EDIT --}}
                                        <a href="{{ route('admin.sections.edit', $s->id) }}"
                                            class="btn btn-warning btn-sm btn-premium">
                                            ✏️
                                        </a>

                                        {{-- DELETE --}}
                                        <form action="{{ route('admin.sections.destroy', $s->id) }}"
                                            method="POST"
                                            onsubmit="return confirm('Yakin hapus section ini?')">
                                            @csrf
                                            @method('DELETE')

                                            <button class="btn btn-danger btn-sm btn-premium">
                                                🗑️
                                            </button>
                                        </form>

                                        {{-- TOGGLE --}}
                                        <form action="{{ route('admin.sections.toggle', $s->id) }}"
                                            method="POST">
                                            @csrf
                                            @method('PATCH')

                                            <button class="btn btn-secondary btn-sm btn-premium">
                                                🔄
                                            </button>
                                        </form>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="empty-state">
                                        🚫 Belum ada section
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

                    <div class="text-muted small">
                        Menampilkan {{ $sections->firstItem() }} - {{ $sections->lastItem() }}
                        dari {{ $sections->total() }} data
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