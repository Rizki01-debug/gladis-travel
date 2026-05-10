@extends('layouts.app')

@section('content')

<div class="container-fluid fade-in">

    {{-- ================= HEADER ================= --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">⚙️ Web Settings</h4>
        <small class="text-muted">Atur identitas website (branding global)</small>
    </div>

    {{-- ================= ERROR ================= --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <b>Terjadi kesalahan:</b>
            <ul class="mb-0">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ================= FORM ================= --}}
    <form method="POST"
          action="{{ route('admin.settings.update') }}"
          enctype="multipart/form-data"> {{-- 🔥 WAJIB --}}
        @csrf

        <div class="card card-premium border-0">
            <div class="card-body">

                {{-- APP NAME --}}
                <div class="mb-3">
                    <label class="form-label">Nama Website</label>
                    <input type="text"
                        name="app_name"
                        class="form-control"
                        value="{{ old('app_name', $setting->app_name) }}">
                </div>

                {{-- 🔥 LOGO UPLOAD --}}
                <div class="mb-3">
                    <label class="form-label">Logo</label>

                    <input type="file"
                        name="logo"
                        class="form-control @error('logo') is-invalid @enderror"
                        accept="image/*">

                    @error('logo')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <small class="text-muted">
                        Kosongkan jika tidak ingin mengganti logo
                    </small>

                    {{-- PREVIEW --}}
                    @if($setting->logo)
                        <div class="mt-3">
                            <img src="{{ asset('storage/' . $setting->logo) }}"
                                 width="120"
                                 class="rounded shadow-sm border">
                        </div>
                    @endif
                </div>

                {{-- 🔥 FAVICON UPLOAD --}}
                <div class="mb-3">
                    <label class="form-label">Favicon</label>

                    <input type="file"
                        name="favicon"
                        class="form-control @error('favicon') is-invalid @enderror"
                        accept="image/*">

                    @error('favicon')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <small class="text-muted">
                        Format kecil (32x32 / 64x64 disarankan)
                    </small>

                    {{-- PREVIEW --}}
                    @if($setting->favicon)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $setting->favicon) }}"
                                 width="40"
                                 class="rounded border">
                        </div>
                    @endif
                </div>

                {{-- FOOTER --}}
                <div class="mb-3">
                    <label class="form-label">Footer Text</label>
                    <textarea name="footer_text"
                        class="form-control"
                        rows="2">{{ old('footer_text', $setting->footer_text) }}</textarea>
                </div>

                {{-- COPYRIGHT --}}
                <div class="mb-3">
                    <label class="form-label">Copyright</label>
                    <input type="text"
                        name="copyright"
                        class="form-control"
                        value="{{ old('copyright', $setting->copyright) }}">
                </div>

                {{-- EMAIL --}}
                <div class="mb-3">
                    <label class="form-label">Email</label>
                    <input type="email"
                        name="contact_email"
                        class="form-control"
                        value="{{ old('contact_email', $setting->contact_email) }}">
                </div>

                {{-- PHONE --}}
                <div class="mb-3">
                    <label class="form-label">No HP</label>
                    <input type="text"
                        name="contact_phone"
                        class="form-control"
                        value="{{ old('contact_phone', $setting->contact_phone) }}">
                </div>

                {{-- BUTTON --}}
                <div class="text-end">
                    <button class="btn btn-primary">
                        💾 Simpan
                    </button>
                </div>

            </div>
        </div>

    </form>

</div>

@endsection