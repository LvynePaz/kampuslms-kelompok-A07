<x-layout title="Tambah Mata Kuliah">

    <style>
        .page-header {
            margin-bottom: 2rem;
        }

        .page-header h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a2f25;
            letter-spacing: -0.4px;
            margin-bottom: 4px;
        }

        .page-header p {
            color: #647a6e;
            font-size: 0.9rem;
        }

        .form-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            padding: 2.25rem;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #21332a;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid rgba(45, 74, 62, 0.2);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.92rem;
            background-color: #fbfcfa;
            color: #1a2f25;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2d4a3e;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(45, 74, 62, 0.12);
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .error-message {
            margin-top: 5px;
            color: #dc2626;
            font-size: 0.82rem;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(45, 74, 62, 0.08);
        }

        .btn-save,
        .btn-cancel {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-save {
            background: #2d4a3e;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);
        }

        .btn-save:hover {
            background: #22382f;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(45, 74, 62, 0.28);
        }

        .btn-cancel {
            background: #eef5f1;
            color: #2d4a3e;
            border: 1px solid rgba(45, 74, 62, 0.15);
        }

        .btn-cancel:hover {
            background: #e2ece6;
            color: #1a2f25;
        }
    </style>

    <div class="page-header">
        <h1>Tambah Mata Kuliah</h1>
    </div>

    <div class="form-card">

        @if ($errors->any())
            <div class="alert-error" style="margin-bottom: 1.5rem;">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="flex-shrink: 0; margin-top: 2px;">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div style="flex: 1;">
                    <strong style="display: block; margin-bottom: 4px;">Terdapat kesalahan pada isian form:</strong>
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach (array_unique($errors->all()) as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.courses.store') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="code">Kode Mata Kuliah</label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code') }}"
                    placeholder="Contoh: SI2514025"
                >

                @error('code')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="name">Nama Mata Kuliah</label>

                <input
                    type="text"
                    id="name"
                    name="name"
                    value="{{ old('name') }}"
                    placeholder="Contoh: Basis Data"
                >

                @error('name')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="sks">SKS</label>

                <input
                    type="number"
                    id="sks"
                    name="sks"
                    value="{{ old('sks') }}"
                    min="1"
                    max ="6"
                    placeholder="Contoh: 3"
                >

                @error('sks')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="lecturer_id">Dosen Pengampu</label>

                <select id="lecturer_id" name="lecturer_id">
                    <option value="">-- Pilih Dosen Pengampu --</option>
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id }}" {{ old('lecturer_id') == $lecturer->id ? 'selected' : '' }}>
                            {{ $lecturer->name }} ({{ $lecturer->email }})
                        </option>
                    @endforeach
                </select>

                @error('lecturer_id')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label>Daftarkan Mahasiswa ke Kelas Ini (Opsional)</label>
                <div style="max-height: 200px; overflow-y: auto; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; padding: 12px; background: #fbfcfa;">
                    @foreach ($students as $student)
                        <label style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px; font-weight: normal; cursor: pointer; font-size: 0.9rem;">
                            <input type="checkbox" name="student_ids[]" value="{{ $student->id }}" {{ in_array($student->id, old('student_ids', [])) ? 'checked' : '' }} style="width: auto;">
                            <span>{{ $student->name }} <small style="color: #647a6e;">({{ $student->nim_nip ?? $student->email }})</small></span>
                        </label>
                    @endforeach
                </div>
                <small style="color: #647a6e; display: block; margin-top: 4px;">Pilih mahasiswa yang ingin langsung didaftarkan ke mata kuliah ini.</small>

                @error('student_ids')
                    <div class="error-message">{{ $message }}</div>
                @enderror
                @error('student_ids.*')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>

                <textarea
                    id="description"
                    name="description"
                    placeholder="Deskripsi mata kuliah"
                >{{ old('description') }}</textarea>

                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="status">Status</label>

                <select id="status" name="status">
                    @foreach (['draft', 'active', 'archived'] as $s)
                        <option value="{{ $s }}" {{ old('status', 'active') === $s ? 'selected' : '' }}>
                            {{ ucfirst($s) }}
                        </option>
                    @endforeach
                </select>

                @error('status')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    Simpan
                </button>

                <a href="{{ route('admin.courses.index') }}" class="btn-cancel">
                    Batal
                </a>
            </div>

        </form>

    </div>

</x-layout>
