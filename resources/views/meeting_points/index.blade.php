@extends('layouts.app')

@section('content')

<h3>Data Meeting Point</h3>

<a href="{{ route('meeting-points.create') }}" class="btn btn-primary mb-3">
    + Tambah Meeting Point
</a>

<table class="table table-bordered">
    <thead>
        <tr>
            <th>Nama</th>
            <th>Kota</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($points as $p)
            <tr>
                <td>{{ $p->name }}</td>
                <td>{{ $p->city->name ?? '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="2" class="text-center">Belum ada data</td>
            </tr>
        @endforelse
    </tbody>
</table>

@endsection