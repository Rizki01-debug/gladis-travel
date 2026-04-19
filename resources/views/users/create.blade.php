@extends('layouts.app')

@section('content')
<div class="container">

    <h3 class="mb-4">➕ Tambah User</h3>

    <form action="{{ route('users.store') }}" method="POST">
        @csrf

        @include('users._form')

    </form>

</div>
@endsection