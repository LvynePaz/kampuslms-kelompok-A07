@php $user = $user ?? null; @endphp

<div>
    <label class="block text-sm font-medium">Nama</label>
    <input type="text" name="name" value="{{ old('name', $user?->name) }}" class="w-full border rounded p-2">
    @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Email</label>
    <input type="email" name="email" value="{{ old('email', $user?->email) }}" class="w-full border rounded p-2">
    @error('email') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Password {{ $user ? '(kosongkan jika tidak ganti)' : '' }}</label>
    <input type="password" name="password" class="w-full border rounded p-2">
    @error('password') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Role</label>
    <select name="role" class="w-full border rounded p-2">
        @foreach (['admin', 'dosen', 'mahasiswa'] as $role)
            <option value="{{ $role }}" @selected(old('role', $user?->role) === $role)>{{ $role }}</option>
        @endforeach
    </select>
    @error('role') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">NIM/NIP</label>
    <input type="text" name="nim_nip" value="{{ old('nim_nip', $user?->nim_nip) }}" class="w-full border rounded p-2">
    @error('nim_nip') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>
