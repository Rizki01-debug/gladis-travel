@extends('layouts.app')

@section('content')

<h4>📜 Activity Log</h4>

<div class="card mt-3">
    <div class="card-body">

        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Deskripsi</th>
                    <th>Waktu</th>
                </tr>
            </thead>

            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td>{{ $log->user->name ?? 'System' }}</td>
                    <td>{{ $log->action }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->created_at }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Belum ada aktivitas</td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{ $logs->links() }}

    </div>
</div>

@endsection