@extends('layouts.app')

@section('content')
    <h3 class="mb-3">📋 Booking Masuk</h3>

    <div class="card shadow-sm p-3">

        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Penumpang</th>
                        <th>Rute</th>
                        <th>Status</th>
                        <th width="180">Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($bookings as $b)
                        <tr>
                            <td>#{{ $b->id }}</td>

                            <td>{{ $b->user->name ?? '-' }}</td>

                            <td>
                                {{ $b->schedule->origin->name ?? '-' }} →
                                {{ $b->schedule->destination->name ?? '-' }}
                            </td>

                            <td>
                                @if ($b->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif ($b->status == 'confirmed')
                                    <span class="badge bg-primary">Dikonfirmasi</span>
                                @elseif ($b->status == 'completed')
                                    <span class="badge bg-success">Selesai</span>
                                @elseif ($b->status == 'rejected')
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>

                            <td>
                                <div class="d-flex gap-2">

                                    {{-- DETAIL --}}
                                    <a href="{{ route('driver.show', $b->id) }}" class="btn btn-info btn-sm">
                                        Detail
                                    </a>

                                    {{-- OPTIONAL: AKSI CEPAT --}}
                                    @if ($b->status == 'pending')
                                    
                                        <form action="{{ route('driver.confirm', $b->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-success btn-sm">
                                                ✔
                                            </button>
                                        </form>

                                        <form action="{{ route('driver.reject', $b->id) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                ✖
                                            </button>
                                        </form>
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">
                                Tidak ada booking
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
@endsection
