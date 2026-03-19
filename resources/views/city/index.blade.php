@extends('layouts.app')

@section('content')

<h3>Data Kota</h3>

<a href="{{ route('cities.create') }}" class="btn btn-primary mb-3">Tambah Kota</a>

<table class="table">
    <thead>
        <tr>
            <th>Nama Kota</th>
        </tr>
    </thead>
    <tbody>
        @foreach($cities as $c)
        <tr>
            <td>{{ $c->name }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

@endsection