@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-semibold">👤 User Management</h4>

        <a href="{{ route('users.create') }}" class="btn btn-primary btn-premium">
            + Tambah User
        </a>
    </div>

    <div class="card card-premium">
        <div class="table-responsive">

            <table class="table table-premium align-middle mb-0">

                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>

                            <td class="fw-semibold">{{ $user->name }}</td>

                            <td class="text-muted">{{ $user->email }}</td>

                            <td>
                                @if ($user->isSuperAdmin())
                                    <span class="badge bg-dark badge-soft">Super Admin</span>
                                @elseif ($user->isAdmin())
                                    <span class="badge bg-primary badge-soft">Admin</span>
                                @elseif ($user->isDriver())
                                    <span class="badge bg-warning text-dark badge-soft">Driver</span>
                                @else
                                    <span class="badge bg-secondary badge-soft">Passenger</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    <a href="{{ route('users.edit', $user->id) }}"
                                        class="btn btn-warning btn-sm btn-premium">
                                        Edit
                                    </a>

                                    <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')

                                        <button class="btn btn-danger btn-sm btn-premium"
                                            onclick="return confirm('Hapus user ini?')">
                                            Hapus
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state">
                                    🚫 Belum ada user
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>

            </table>

        </div>
    </div>

    {{-- PAGINATION --}}
    <div class="mt-3">
        {{ $users->links() }}
    </div>
@endsection
