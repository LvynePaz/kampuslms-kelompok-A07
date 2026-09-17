```blade
<x-layout title="Edit Mata Kuliah">

    <style>
        .page-header {
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
        }

        .form-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
            padding: 2rem;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            color: #334155;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-family: inherit;
            font-size: 0.9rem;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3b82f6;
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        .error-message {
            margin-top: 5px;
            color: #dc2626;
            font-size: 0.8rem;
        }

        .form-actions {
            display: flex;
            gap: 8px;
            margin-top: 1.5rem;
        }

        .btn-save,
        .btn-cancel {
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
        }

        .btn-save {
            background: #1d4ed8;
            color: white;
        }

        .btn-save:hover {
            background: #1e40af;
        }

        .btn-cancel {
            background: #f1f5f9;
            color: #475569;
        }

        .btn-cancel:hover {
            background: #e2e8f0;
        }
    </style>

    <div class="page-header">
        <h1>Edit Mata Kuliah</h1>
    </div>

    <div class="form-card">

        <form action="{{ route('courses.update', $course['id']) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="form-group">
                <label for="code">Kode Mata Kuliah</label>

                <input
                    type="text"
                    id="code"
                    name="code"
                    value="{{ old('code', $course['code']) }}"
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
                    value="{{ old('name', $course['name']) }}"
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
                    value="{{ old('sks', $course['sks']) }}"
                    min="1"
                >

                @error('sks')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="lecturer">Dosen Pengampu</label>

                <input
                    type="text"
                    id="lecturer"
                    name="lecturer"
                    value="{{ old('lecturer', $course['lecturer']) }}"
                >

                @error('lecturer')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-group">
                <label for="description">Deskripsi</label>

                <textarea
                    id="description"
                    name="description"
                >{{ old('description', $course['description']) }}</textarea>

                @error('description')
                    <div class="error-message">{{ $message }}</div>
                @enderror
            </div>

            <div class="form-actions">
                <button type="submit" class="btn-save">
                    Update
                </button>

                <a href="{{ route('courses.index') }}" class="btn-cancel">
                    Batal
                </a>
            </div>

        </form>

    </div>

</x-layout>
