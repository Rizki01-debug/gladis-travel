@extends('layouts.app')

@section('content')

<div class="container">

    <h3 class="mb-4">⚙️ Pengaturan Fitur Sistem</h3>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">

        @foreach ($grouped as $featureName => $items)

        <div class="col-md-6 mb-4">

            <div class="card shadow-sm border-0 rounded-4 p-3">

                {{-- 🔥 TITLE --}}
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">
                        {{ strtoupper(str_replace('_', ' ', $featureName)) }}
                    </h5>
                </div>

                {{-- 🔥 ROLE LIST --}}
                @foreach ($items as $item)

                <form method="POST" action="{{ route('features.toggle') }}">
                    @csrf

                    <input type="hidden" name="name" value="{{ $item->name }}">
                    <input type="hidden" name="role" value="{{ $item->role }}">

                    <div class="d-flex justify-content-between align-items-center mb-2">

                        {{-- ROLE --}}
                        <span class="text-capitalize">
                            {{ str_replace('_', ' ', $item->role) }}
                        </span>

                        {{-- SWITCH --}}
                        <div class="form-check form-switch">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                onchange="this.form.submit()"
                                {{ $item->is_active ? 'checked' : '' }}
                            >
                        </div>

                    </div>

                </form>

                @endforeach

            </div>

        </div>

        @endforeach

    </div>

</div>

@endsection