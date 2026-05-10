@extends('layouts.app')

@section('content')

<div class="container-fluid fade-in">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">👤 Profile Saya</h4>
        <small class="text-muted">Kelola informasi akun Anda</small>
    </div>

    {{-- ERROR --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Terjadi kesalahan:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row">

        {{-- ================= FORM ================= --}}
        <div class="col-md-6">

            <div class="card shadow-sm border-0">
                <div class="card-body">

                    <form method="POST" action="{{ route('passenger.profile.update') }}">
                        @csrf

                        {{-- NAME --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama</label>
                            <input type="text"
                                   name="name"
                                   class="form-control"
                                   value="{{ old('name', $user->name) }}"
                                   required>
                        </div>

                        {{-- EMAIL --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   value="{{ old('email', $user->email) }}"
                                   required>
                        </div>

                        {{-- PASSWORD --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Password Baru</label>
                            <input type="password"
                                   name="password"
                                   class="form-control"
                                   placeholder="Kosongkan jika tidak ingin mengganti">
                        </div>

                        {{-- CONFIRM PASSWORD --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Konfirmasi Password</label>
                            <input type="password"
                                   name="password_confirmation"
                                   class="form-control">
                        </div>

                        {{-- BUTTON --}}
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('dashboard.user') }}" class="btn btn-secondary">
                                ← Kembali
                            </a>

                            <button class="btn btn-primary">
                                💾 Simpan Perubahan
                            </button>
                        </div>

                    </form>

                </div>
            </div>

        </div>

        {{-- ================= INFO CARD ================= --}}
        <div class="col-md-6">

            <div class="card shadow-sm border-0">
                <div class="card-body text-center">

                    <h5 class="fw-bold mb-3">Informasi Akun</h5>

                    <p class="mb-1">
                        <strong>Nama:</strong><br>
                        {{ $user->name }}
                    </p>

                    <p class="mb-1">
                        <strong>Email:</strong><br>
                        {{ $user->email }}
                    </p>

                    <p class="mb-0">
                        <strong>Role:</strong><br>
                        Penumpang
                    </p>

                </div>
            </div>

        </div>

    </div>

</div>

@endsection