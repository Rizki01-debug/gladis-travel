@extends('layouts.app')

@section('content')
    <div class="container-fluid fade-in">

        {{-- ================= HEADER ================= --}}
        <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
            <div>
                <h4 class="fw-bold mb-1">👤 User Management</h4>
                <small class="text-muted">Kelola akun pengguna sistem</small>
            </div>

            <a href="{{ route('users.create') }}" class="btn btn-primary btn-premium">
                ➕ Tambah User
            </a>
        </div>

        {{-- ================= ALERT ================= --}}
        @if (session('success'))
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
                                <th class="text-start">Nama</th>
                                <th>Email</th>
                                <th>Role</th>
                                <th width="180">Aksi</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($users as $i => $user)
                                <tr>

                                    {{-- NUMBER --}}
                                    <td class="text-center">
                                        {{ $users->firstItem() + $i }}
                                    </td>

                                    {{-- NAME --}}
                                    <td class="fw-semibold">
                                        {{ $user->name }}
                                    </td>

                                    {{-- EMAIL --}}
                                    <td class="text-muted text-center">
                                        {{ $user->email }}
                                    </td>

                                    {{-- ROLE --}}
                                    <td class="text-center">
                                        @if ($user->isSuperAdmin())
                                            <span class="badge bg-dark">Super Admin</span>
                                        @elseif ($user->isAdmin())
                                            <span class="badge bg-primary">Admin</span>
                                        @elseif ($user->isDriver())
                                            <span class="badge bg-warning text-dark">Driver</span>
                                        @else
                                            <span class="badge bg-secondary">Passenger</span>
                                        @endif
                                    </td>

                                    {{-- ACTION --}}
                                    <td>
                                        <div class="d-flex gap-1 justify-content-center">

                                            <a href="{{ route('users.edit', $user->id) }}"
                                                class="btn btn-warning btn-sm btn-premium">
                                                ✏️
                                            </a>

                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST"
                                                onsubmit="return confirm('Yakin hapus user ini?')">
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
                                            🚫 Belum ada user
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                    </table>
                </div>

                {{-- ================= PAGINATION ================= --}}
                @if ($users->hasPages())
                    <div class="mt-4 d-flex justify-content-between align-items-center flex-wrap gap-2">

                        <div class="text-muted small">
                            Menampilkan {{ $users->firstItem() }} - {{ $users->lastItem() }}
                            dari {{ $users->total() }} data
                        </div>

                        <div>
                            {{ $users->links('pagination::bootstrap-5') }}
                        </div>

                    </div>
                @endif

            </div>
        </div>

    </div>
@endsection
