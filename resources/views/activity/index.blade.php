@extends('layouts.app')

@section('content')
<div class="container-fluid">

    {{-- TITLE --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">📜 Activity Log</h4>
    </div>

    <div class="card card-premium border-0">

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-premium align-middle mb-0">

                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Aksi</th>
                            <th>Deskripsi</th>
                            <th class="text-end">Waktu</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($logs as $log)
                            <tr>

                                <td class="fw-semibold">
                                    {{ $log->user->name ?? 'System' }}
                                </td>

                                <td>
                                    <span class="badge bg-primary">
                                        {{ $log->action }}
                                    </span>
                                </td>

                                <td class="text-muted">
                                    {{ $log->description }}
                                </td>

                                <td class="text-end">
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y') }}
                                    </small><br>
                                    <small>
                                        {{ \Carbon\Carbon::parse($log->created_at)->format('H:i') }}
                                    </small>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        📭 Belum ada aktivitas
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- 🔥 WRAPPER PAGINATION (PENTING) --}}
            <div class="pagination-wrapper mt-4">
                {{ $logs->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>

        </div>
    </div>

</div>
@endsection