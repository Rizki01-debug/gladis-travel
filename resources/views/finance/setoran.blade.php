@extends('layouts.app')

@section('content')
    <h3>💰 Setoran Driver</h3>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>ID</th>
                <th>Driver</th>
                <th>Booking</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($transactions as $t)
                <tr>
                    <td>#{{ $t->id }}</td>

                    {{-- DRIVER --}}
                    <td>
                        {{ $t->booking->trip->driver->name ?? '-' }}
                    </td>

                    {{-- BOOKING --}}
                    <td>#{{ $t->booking_id }}</td>

                    {{-- JUMLAH --}}
                    <td>Rp {{ number_format($t->amount) }}</td>

                    {{-- STATUS --}}
                    <td>
                        @if ($t->status == 'paid')
                            <span class="badge bg-success">Sudah Setor</span>
                        @else
                            <span class="badge bg-warning">Belum Setor</span>
                        @endif
                    </td>

                    {{-- AKSI --}}
                    <td>
                        @if ($t->status == 'unpaid')
                            <form action="{{ route('finance.setoran.confirm', $t->id) }}" method="POST">
                                @csrf
                                <button class="btn btn-success btn-sm">
                                    ✔ Konfirmasi
                                </button>
                            </form>
                        @else
                            <span class="text-muted">✔ Done</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada setoran</td>
                </tr>
            @endforelse
        </tbody>
    </table>
@endsection
