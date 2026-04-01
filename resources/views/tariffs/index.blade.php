@extends('layouts.app')

@section('content')

<h3>💰 Data Tarif</h3>

<a href="{{ route('tariffs.create') }}" class="btn btn-primary mb-3">
    + Tambah Tarif
</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Base</th>
            <th>Per KM</th>
            <th>Aksi</th>
        </tr>
    </thead>

    <tbody>
        @foreach ($tariffs as $t)
        <tr>
            <td>{{ $t->name }}</td>
            <td>Rp {{ number_format($t->base_price) }}</td>
            <td>Rp {{ number_format($t->price_per_km) }}</td>

            <td>
                <a href="{{ route('tariffs.edit', $t->id) }}" class="btn btn-warning btn-sm">Edit</a>

                <form action="{{ route('tariffs.destroy', $t->id) }}" method="POST" style="display:inline;">
                    @csrf
                    @method('DELETE')

                    <button class="btn btn-danger btn-sm">Hapus</button>
                </form>
            </td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection