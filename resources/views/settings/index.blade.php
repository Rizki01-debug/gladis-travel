@extends('layouts.app')

@section('content')

<div class="container-custom fade-in">

    {{-- HEADER --}}
    <div class="mb-4">
        <h4 class="fw-bold mb-1">⚙ Pengaturan Tema</h4>
        <small class="text-muted">Sesuaikan tampilan dashboard sesuai preferensi kamu</small>
    </div>

    <div class="card-premium p-4">

        <form action="{{ route('theme.update') }}" method="POST">
            @csrf

            {{-- ================= PRESET THEME ================= --}}
            <div class="mb-4">
                <label class="fw-semibold mb-3">🎨 Pilih Tema</label>

                <div class="d-flex gap-3 flex-wrap">

                    @foreach ([
                        'blue' => '#3b82f6',
                        'green' => '#22c55e',
                        'red' => '#ef4444',
                        'dark' => '#1e293b'
                    ] as $name => $color)

                        <label class="theme-option">
                            <input type="radio" name="theme_color" value="{{ $name }}"
                                {{ auth()->user()->theme_color === $name ? 'checked' : '' }}>

                            <div class="theme-box" style="background: {{ $color }}"></div>

                            <small>{{ ucfirst($name) }}</small>
                        </label>

                    @endforeach

                </div>
            </div>

            {{-- ================= CUSTOM COLOR ================= --}}
            <div class="mb-4">
                <label class="fw-semibold mb-2">✨ Custom Warna</label>

                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" id="use_custom" name="use_custom"
                        {{ str_starts_with(auth()->user()->theme_color, '#') ? 'checked' : '' }}>

                    <label class="form-check-label" for="use_custom">
                        Gunakan warna custom
                    </label>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <input type="color"
                        name="custom_color"
                        id="custom_color"
                        class="form-control form-control-color"
                        value="{{ str_starts_with(auth()->user()->theme_color, '#') ? auth()->user()->theme_color : '#3b82f6' }}">

                    <span class="text-muted small">
                        Pilih warna sesuai selera
                    </span>
                </div>

                <small class="text-muted d-block mt-2">
                    Jika custom aktif, tema preset akan diabaikan
                </small>
            </div>

            {{-- ================= BUTTON ================= --}}
            <div class="d-flex justify-content-end">
                <button class="btn btn-primary-custom btn-premium">
                    💾 Simpan Tema
                </button>
            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const checkbox = document.getElementById('use_custom');
    const color = document.getElementById('custom_color');

    function toggleColor() {
        color.disabled = !checkbox.checked;
    }

    toggleColor();

    checkbox.addEventListener('change', toggleColor);

});
</script>
@endpush