@php $user = $user ?? null; @endphp

<style>
    .user-form-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .user-form-field.full { grid-column: 1 / -1; }
    .user-form-label { color: #33493d; display: block; font-size: .78rem; font-weight: 700; margin-bottom: .4rem; }
    .user-form-control { background: #fff; border: 1px solid #cbd8ce; border-radius: 8px; color: #243c32; font: inherit; font-size: .86rem; padding: .7rem .75rem; width: 100%; }
    .user-form-control:focus { border-color: #416454; box-shadow: 0 0 0 3px rgba(65,100,84,.12); outline: none; }
    .user-form-help { color: #789083; font-size: .72rem; margin-top: .3rem; }
    .user-form-error { color: #a64b4b; font-size: .74rem; margin-top: .3rem; }
    @media (max-width: 650px) { .user-form-grid { grid-template-columns: 1fr; } .user-form-field.full { grid-column: auto; } }
</style>

<div class="user-form-grid">
    <div class="user-form-field">
        <label for="name" class="user-form-label">Nama lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name', $user?->name) }}" class="user-form-control" placeholder="Nama pengguna" required>
        @error('name') <p class="user-form-error">{{ $message }}</p> @enderror
    </div>

    <div class="user-form-field">
        <label for="email" class="user-form-label">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email', $user?->email) }}" class="user-form-control" placeholder="nama@kampus.ac.id" required>
        @error('email') <p class="user-form-error">{{ $message }}</p> @enderror
    </div>

    <div class="user-form-field">
        <label for="password" class="user-form-label">Password {{ $user ? '(opsional saat edit)' : '' }}</label>
        <input id="password" type="password" name="password" class="user-form-control" placeholder="Minimal 8 karakter" {{ $user ? '' : 'required' }}>
        <p class="user-form-help">Minimal 8 karakter.</p>
        @error('password') <p class="user-form-error">{{ $message }}</p> @enderror
    </div>

    <div class="user-form-field">
        <label for="role" class="user-form-label">Role akses</label>
        <select id="role" name="role" class="user-form-control" required>
            @foreach (['admin', 'dosen', 'mahasiswa'] as $role)
                <option value="{{ $role }}" @selected(old('role', $user?->role) === $role)>{{ ucfirst($role) }}</option>
            @endforeach
        </select>
        <p class="user-form-help">Role diatur eksplisit oleh controller.</p>
        @error('role') <p class="user-form-error">{{ $message }}</p> @enderror
    </div>

    <div class="user-form-field full">
        <label for="nim_nip" class="user-form-label">NIM / NIP <span style="color: #789083; font-weight: 500;">(opsional)</span></label>
        <input id="nim_nip" type="text" name="nim_nip" value="{{ old('nim_nip', $user?->nim_nip) }}" class="user-form-control" placeholder="Nomor identitas akademik">
        @error('nim_nip') <p class="user-form-error">{{ $message }}</p> @enderror
    </div>
</div>
