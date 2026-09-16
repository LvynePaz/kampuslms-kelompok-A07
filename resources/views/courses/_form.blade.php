@php $course = $course ?? null; @endphp

<div>
    <label class="block text-sm font-medium">Kode</label>
    <input type="text" name="code" value="{{ old('code', $course?->code) }}" class="w-full border rounded p-2">
    @error('code') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Nama</label>
    <input type="text" name="name" value="{{ old('name', $course?->name) }}" class="w-full border rounded p-2">
    @error('name') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Deskripsi</label>
    <textarea name="description" class="w-full border rounded p-2">{{ old('description', $course?->description) }}</textarea>
</div>

<div>
    <label class="block text-sm font-medium">SKS</label>
    <input type="number" name="sks" value="{{ old('sks', $course?->sks) }}" class="w-full border rounded p-2">
    @error('sks') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Dosen Pengampu</label>
    <select name="lecturer_id" class="w-full border rounded p-2">
        @foreach ($lecturers as $lecturer)
            <option value="{{ $lecturer->id }}" @selected(old('lecturer_id', $course?->lecturer_id) == $lecturer->id)>
                {{ $lecturer->name }}
            </option>
        @endforeach
    </select>
    @error('lecturer_id') <p class="text-red-600 text-sm">{{ $message }}</p> @enderror
</div>

<div>
    <label class="block text-sm font-medium">Status</label>
    <select name="status" class="w-full border rounded p-2">
        @foreach (['draft', 'active', 'archived'] as $status)
            <option value="{{ $status }}" @selected(old('status', $course?->status) === $status)>{{ $status }}</option>
        @endforeach
    </select>
</div>
