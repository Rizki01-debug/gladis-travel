@extends('layouts.app')

@section('content')

<div class="container text-center mt-5">

    <h1 class="display-4 text-danger">403</h1>

    <h3 class="mb-3">🚫 Akses Ditolak</h3>

    <p class="text-muted">
        Fitur ini sedang tidak tersedia atau tidak diizinkan untuk Anda.
    </p>

    <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">
        🔙 Kembali
    </a>

    <a href="{{ route('settings.index') }}" class="btn btn-secondary mt-3">
        ⚙️ Pengaturan
    </a>

</div>

@endsection