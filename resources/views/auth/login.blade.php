@extends('layouts.LoginRegis')

@section('content')

<div class="w-100">

    {{-- TITLE --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold">Login</h3>
        <small class="text-muted">Masuk ke akun kamu</small>
    </div>

    {{-- SESSION STATUS --}}
    @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
    @endif

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
    <form method="POST" action="{{ route('login') }}">
        @csrf

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
                       required autofocus>
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

        {{-- REMEMBER --}}
        <div class="form-check mb-3">
            <input class="form-check-input"
                   type="checkbox"
                   name="remember"
                   id="remember">
            <label class="form-check-label" for="remember">
                Ingat saya
            </label>
        </div>

        {{-- BUTTON LOGIN --}}
        <button type="submit" class="btn btn-primary w-100 mb-3">
            🔐 Login
        </button>

        {{-- REGISTER --}}
        <div class="text-center">
            <small class="text-muted">Belum punya akun?</small><br>

            <a href="{{ route('register') }}" class="btn btn-outline-secondary w-100 mt-2">
                Daftar Sekarang
            </a>
        </div>

    </form>

</div>

@endsection