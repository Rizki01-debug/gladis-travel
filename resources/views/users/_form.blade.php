<div class="card shadow-sm border-0">
    <div class="card-body">

        @php
            $isEdit = isset($user);
            $selectedVehicleId = old('vehicle_id', optional($user->vehicle)->id ?? '');
        @endphp

        {{-- ================= NAME ================= --}}
        <div class="mb-3">
            <label class="form-label">Nama</label>
            <input type="text"
                   name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name', $user->name ?? '') }}"
                   required>

            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ================= EMAIL ================= --}}
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email"
                   name="email"
                   class="form-control @error('email') is-invalid @enderror"
                   value="{{ old('email', $user->email ?? '') }}"
                   required>

            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ================= PASSWORD ================= --}}
        <div class="mb-3">
            <label class="form-label">
                Password
                @if($isEdit)
                    <small class="text-muted">(Kosongkan jika tidak diubah)</small>
                @endif
            </label>

            <input type="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror"
                   {{ $isEdit ? '' : 'required' }}>

            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ================= ROLE ================= --}}
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role_id"
                    id="roleSelect"
                    class="form-control @error('role_id') is-invalid @enderror"
                    required>

                <option value="">-- Pilih Role --</option>

                @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                        data-role="{{ strtolower($role->name) }}"
                        {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                        {{ ucfirst($role->name) }}
                    </option>
                @endforeach

            </select>

            @error('role_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ================= VEHICLE ================= --}}
        <div class="mb-3 d-none" id="vehicleField">
            <label class="form-label">Vehicle (Opsional)</label>

            <select name="vehicle_id"
                    class="form-control @error('vehicle_id') is-invalid @enderror">

                <option value="">-- Tanpa Kendaraan --</option>

                @foreach ($vehicles as $vehicle)
                    <option value="{{ $vehicle->id }}"
                        {{ $selectedVehicleId == $vehicle->id ? 'selected' : '' }}>
                        {{ $vehicle->name ?? 'Kendaraan #' . $vehicle->id }}
                    </option>
                @endforeach

            </select>

            @error('vehicle_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ================= BUTTON ================= --}}
        <div class="d-flex justify-content-between mt-4">
            <a href="{{ route('users.index') }}" class="btn btn-secondary">
                ← Kembali
            </a>

            <button class="btn btn-success">
                💾 Simpan
            </button>
        </div>

    </div>
</div>

<script>
    const roleSelect = document.getElementById('roleSelect');
    const vehicleField = document.getElementById('vehicleField');

    function toggleVehicleField() {
        const selectedOption = roleSelect.options[roleSelect.selectedIndex];
        const roleName = selectedOption.dataset.role;

        if (roleName === 'driver') {
            vehicleField.classList.remove('d-none');
        } else {
            vehicleField.classList.add('d-none');
        }
    }

    roleSelect.addEventListener('change', toggleVehicleField);

    // 🔥 AUTO TRIGGER SAAT EDIT / OLD VALUE
    window.addEventListener('load', toggleVehicleField);
</script>