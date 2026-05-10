@extends('layouts.LoginRegis')

@section('content')

<div class="w-100">

    {{-- TITLE --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold">Register</h3>
        <small class="text-muted">Buat akun baru</small>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- FORM --}}
    <form method="POST" action="{{ route('register') }}">
        @csrf

        {{-- NAME --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Nama</label>
            <div class="input-group">
                <span class="input-group-text">👤</span>
                <input type="text"
                       name="name"
                       value="{{ old('name') }}"
                       class="form-control"
                       placeholder="Masukkan nama"
                       required autofocus>
            </div>
        </div>

        {{-- EMAIL --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <div class="input-group">
                <span class="input-group-text">📧</span>
                <input type="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="form-control"
                       placeholder="Masukkan email"
                       required>
            </div>
        </div>

        {{-- PASSWORD --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <div class="input-group">
                <span class="input-group-text">🔒</span>
                <input type="password"
                       name="password"
                       class="form-control"
                       placeholder="Masukkan password"
                       required>
            </div>
        </div>

        {{-- CONFIRM PASSWORD --}}
        <div class="mb-3">
            <label class="form-label fw-semibold">Konfirmasi Password</label>
            <div class="input-group">
                <span class="input-group-text">🔐</span>
                <input type="password"
                       name="password_confirmation"
                       class="form-control"
                       placeholder="Ulangi password"
                       required>
            </div>
        </div>

        {{-- BUTTON --}}
        <button type="submit" class="btn btn-primary w-100 mb-3">
            🚀 Register
        </button>

        {{-- LOGIN --}}
        <div class="text-center">
            <small class="text-muted">Sudah punya akun?</small><br>

            <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 mt-2">
                Login Sekarang
            </a>
        </div>

    </form>

</div>

@endsection