@extends('layouts.app')

@section('content')

<div class="container-custom">

    <h3 class="mb-4">⚙ Pengaturan Tema</h3>

    <div class="card-custom">

        <form action="{{ route('theme.update') }}" method="POST">
            @csrf

            {{-- ================= PRESET THEME ================= --}}
            <label class="mb-2">Pilih Tema</label>

            <div class="d-flex gap-3 mb-4">

                @foreach (['blue','green','red','dark'] as $color)
                    <label>
                        <input type="radio" name="theme_color" value="{{ $color }}"
                            {{ auth()->user()->theme_color === $color ? 'checked' : '' }}>
                        {{ ucfirst($color) }}
                    </label>
                @endforeach

            </div>

            {{-- ================= CUSTOM COLOR ================= --}}
            <label class="mb-2">Custom Warna</label>

            <div class="mb-3">
                <input type="checkbox" id="use_custom" name="use_custom"
                    {{ str_starts_with(auth()->user()->theme_color, '#') ? 'checked' : '' }}>
                <label for="use_custom">Gunakan warna custom</label>
            </div>

            <input type="color"
                   name="custom_color"
                   id="custom_color"
                   class="form-control form-control-color mb-3"
                   value="{{ str_starts_with(auth()->user()->theme_color, '#') ? auth()->user()->theme_color : '#0d6efd' }}">

            <small class="text-muted">
                Jika custom aktif, preset akan diabaikan.
            </small>

            {{-- ================= BUTTON ================= --}}
            <div class="mt-4">
                <button class="btn btn-primary">
                    Simpan Tema
                </button>
            </div>

        </form>

    </div>

</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const checkbox = document.getElementById('use_custom');
    const color = document.getElementById('custom_color');

    // initial state
    color.disabled = !checkbox.checked;

    checkbox.addEventListener('change', function () {
        color.disabled = !this.checked;
    });

});
</script>
@endsection