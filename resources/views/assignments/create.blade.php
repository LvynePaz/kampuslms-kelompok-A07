<x-layout title="Buat Tugas Kuliah">
    <div style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
        <a href="{{ url()->previous() }}" style="display: inline-flex; align-items: center; gap: 6px; color: #416454; text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-bottom: 1.5rem;">
            ← Kembali ke Detail Mata Kuliah
        </a>

        <div style="background: #ffffff; border-radius: 18px; padding: 2.25rem; border: 1px solid rgba(45, 74, 62, 0.08); box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);">
            <h1 style="font-size: 1.6rem; font-weight: 700; color: #1a2f25; margin-bottom: 6px;">
                Buat Tugas Perkuliahan Baru
            </h1>
            <p style="color: #5e7166; font-size: 0.9rem; margin-bottom: 1.75rem;">
                Mata Kuliah: <strong>{{ $course->name }}</strong> ({{ $course->code }})
            </p>

            @if ($errors->any())
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; padding: 12px 16px; border-radius: 10px; margin-bottom: 1.5rem; font-size: 0.9rem;">
                    <ul style="margin: 0; padding-left: 20px;">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('dosen.courses.assignments.store', $course) }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label for="title" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Judul Tugas
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Tugas 1 - Pemodelan ERD dan Migrasi Database" style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.25rem;">
                    <div>
                        <label for="due_at" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                            Batas Waktu (Deadline)
                        </label>
                        <input type="datetime-local" id="due_at" name="due_at" value="{{ old('due_at') }}" required style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                    </div>

                    <div>
                        <label for="max_score" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                            Nilai Maksimal
                        </label>
                        <input type="number" id="max_score" name="max_score" value="{{ old('max_score', 100) }}" min="1" max="100" required style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                    </div>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="status" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Status Tugas
                    </label>
                    <select id="status" name="status" required style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                        <option value="published" {{ old('status', 'published') === 'published' ? 'selected' : '' }}>Dipublikasikan (Bisa Dilihat Mahasiswa)</option>
                        <option value="draft" {{ old('status') === 'draft' ? 'selected' : '' }}>Draft (Disimpan Sementara)</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label for="instructions" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Instruksi Pengerjaan Tugas
                    </label>
                    <textarea id="instructions" name="instructions" rows="5" placeholder="Tuliskan petunjuk pengerjaan dan format pengumpulan tugas..." style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa; resize: vertical;">{{ old('instructions') }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; border-top: 1px solid rgba(45, 74, 62, 0.08); padding-top: 1.25rem;">
                    <button type="submit" style="background: #2d4a3e; color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);">
                        Simpan & Publikasikan Tugas
                    </button>
                    <a href="{{ route('dosen.courses.show', $course) }}" style="background: #eef5f1; color: #2d4a3e; padding: 10px 20px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.15);">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
