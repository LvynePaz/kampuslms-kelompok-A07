<x-layout title="Tambah Materi Perkuliahan">
    <div style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
        <a href="{{ url()->previous() }}" style="display: inline-flex; align-items: center; gap: 6px; color: #416454; text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-bottom: 1.5rem;">
            ← Kembali ke Detail Mata Kuliah
        </a>

        <div style="background: #ffffff; border-radius: 18px; padding: 2.25rem; border: 1px solid rgba(45, 74, 62, 0.08); box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);">
            <h1 style="font-size: 1.6rem; font-weight: 700; color: #1a2f25; margin-bottom: 6px;">
                Tambah Materi Perkuliahan
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

            <form action="{{ route('dosen.courses.materials.store', $course) }}" method="POST">
                @csrf

                <div style="margin-bottom: 1.25rem;">
                    <label for="title" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Judul Materi
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required placeholder="Contoh: Pertemuan 1 - Pengenalan Arsitektur MVC" style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="type" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Tipe Materi
                    </label>
                    <select id="type" name="type" required style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                        <option value="link" {{ old('type') === 'link' ? 'selected' : '' }}>Link Referensi / Video / Web</option>
                        <option value="file" {{ old('type') === 'file' ? 'selected' : '' }}>Dokumen / Berkas Slide</option>
                    </select>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="external_url" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Tautan / URL Materi (Opsional)
                    </label>
                    <input type="url" id="external_url" name="external_url" value="{{ old('external_url') }}" placeholder="https://example.com/slide-materi.pdf" style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label for="description" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Deskripsi / Petunjuk Bacaan
                    </label>
                    <textarea id="description" name="description" rows="4" placeholder="Penjelasan singkat mengenai materi ini..." style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa; resize: vertical;">{{ old('description') }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; border-top: 1px solid rgba(45, 74, 62, 0.08); padding-top: 1.25rem;">
                    <button type="submit" style="background: #2d4a3e; color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);">
                        Simpan Materi
                    </button>
                    <a href="{{ route('dosen.courses.show', $course) }}" style="background: #eef5f1; color: #2d4a3e; padding: 10px 20px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.15);">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
