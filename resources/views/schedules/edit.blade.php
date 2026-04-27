@extends('layouts.app')

@section('content')
    <div class="container">

        <h4 class="mb-4">✏️ Edit Jadwal</h4>

        {{-- ERROR --}}
        @if ($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('schedules.update', $schedule->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ORIGIN --}}
            <div class="mb-3">
                <label class="form-label">Kota Asal</label>
                <select name="origin_city_id" class="form-control" required>
                    @foreach ($cities as $c)
                        <option value="{{ $c->id }}" {{ $schedule->origin_city_id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- DESTINATION --}}
            <div class="mb-3">
                <label class="form-label">Kota Tujuan</label>
                <select name="destination_city_id" class="form-control" required>
                    @foreach ($cities as $c)
                        <option value="{{ $c->id }}"
                            {{ $schedule->destination_city_id == $c->id ? 'selected' : '' }}>
                            {{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- VEHICLE --}}
            <div class="mb-3">
                <label class="form-label">Kendaraan</label>
                <select name="vehicle_id" class="form-control" required>
                    @foreach ($vehicles as $v)
                        <option value="{{ $v->id }}" {{ $schedule->vehicle_id == $v->id ? 'selected' : '' }}>
                            {{ $v->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- TIME --}}
            <div class="mb-3">
                <label class="form-label">Jam Berangkat</label>
                <input type="time" name="departure_time"
                    value="{{ \Carbon\Carbon::parse($schedule->departure_time)->format('H:i') }}" class="form-control"
                    required>
            </div>

            {{-- ROUTE POINT --}}
            <div class="mb-3">
                <label class="form-label">Route (Meeting Points)</label>

                @php
                    $selectedPoints = $schedule->routePoints->pluck('meeting_point_id')->toArray();
                @endphp

                <select name="route_points[]" class="form-control" multiple required>
                    @foreach ($meetingPoints as $p)
                        <option value="{{ $p->id }}" {{ in_array($p->id, $selectedPoints) ? 'selected' : '' }}>
                            {{ $p->name }}
                        </option>
                    @endforeach
                </select>

                <small class="text-muted">Gunakan Ctrl / Cmd untuk pilih banyak</small>
            </div>

            <button class="btn btn-success">
                💾 Update Jadwal
            </button>

            <a href="{{ route('schedules.index') }}" class="btn btn-secondary">
                Kembali
            </a>

        </form>

    </div>
@endsection
