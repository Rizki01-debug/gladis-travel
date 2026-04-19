<div class="card shadow-sm border-0">
    <div class="card-body">

        {{-- NAME --}}
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

        {{-- EMAIL --}}
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

        {{-- PASSWORD --}}
        <div class="mb-3">
            <label class="form-label">
                Password
                @isset($user)
                    <small class="text-muted">(Kosongkan jika tidak diubah)</small>
                @endisset
            </label>

            <input type="password"
                   name="password"
                   class="form-control @error('password') is-invalid @enderror">

            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- ROLE --}}
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role_id"
                    class="form-control @error('role_id') is-invalid @enderror"
                    required>

                <option value="">-- Pilih Role --</option>

                @foreach ($roles as $role)
                    <option value="{{ $role->id }}"
                        {{ old('role_id', $user->role_id ?? '') == $role->id ? 'selected' : '' }}>
                        {{ $role->name }}
                    </option>
                @endforeach

            </select>

            @error('role_id')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        {{-- BUTTON --}}
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