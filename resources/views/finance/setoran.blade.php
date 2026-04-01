@extends('layouts.app')

@section('content')

<h3>💰 Setoran Driver</h3>

<table class="table table-bordered mt-3">
    <thead>
        <tr>
            <th>ID</th>
            <th>Booking</th>
            <th>Jumlah</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($transactions as $t)
            <tr>
                <td>#{{ $t->id }}</td>
                <td>#{{ $t->booking_id }}</td>
                <td>Rp {{ number_format($t->amount) }}</td>

                <td>
                    <span class="badge bg-warning">Belum Setor</span>
                </td>

                <td>
                    <form action="{{ route('finance.setoran.confirm', $t->id) }}" method="POST">
                        @csrf
                        <button class="btn btn-success btn-sm">
                            ✔ Konfirmasi
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Tidak ada setoran</td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection