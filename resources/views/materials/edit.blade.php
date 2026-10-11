<x-layout title="Edit Materi Perkuliahan">
    <div style="max-width: 800px; margin: 0 auto; padding: 2rem 0;">
        <a href="{{ route('dosen.courses.show', $course) }}" style="display: inline-flex; align-items: center; gap: 6px; color: #416454; text-decoration: none; font-size: 0.9rem; font-weight: 600; margin-bottom: 1.5rem;">
            ← Kembali ke Detail Mata Kuliah: {{ $course->name }}
        </a>

        <div style="background: #ffffff; border-radius: 18px; padding: 2.25rem; border: 1px solid rgba(45, 74, 62, 0.08); box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
                <h1 style="font-size: 1.6rem; font-weight: 700; color: #1a2f25; margin: 0;">
                    Edit Materi Perkuliahan
                </h1>
                <span style="font-size: 0.8rem; background: #e8f1ec; color: #2d4a3e; padding: 4px 10px; border-radius: 6px; font-weight: 600;">
                    {{ $course->code }}
                </span>
            </div>
            <p style="color: #5e7166; font-size: 0.9rem; margin-bottom: 1.75rem;">
                Mata Kuliah: <strong>{{ $course->name }}</strong>
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

            <form action="{{ route('dosen.materials.update', $material) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div style="margin-bottom: 1.25rem;">
                    <label for="title" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Judul Materi
                    </label>
                    <input type="text" id="title" name="title" value="{{ old('title', $material->title) }}" required placeholder="Contoh: Pertemuan 1 - Pengenalan Arsitektur MVC" style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="type" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Tipe Materi
                    </label>
                    <select id="type" name="type" required style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                        <option value="file" {{ old('type', $material->type) === 'file' ? 'selected' : '' }}>Dokumen / Berkas Slide (File)</option>
                        <option value="link" {{ old('type', $material->type) === 'link' ? 'selected' : '' }}>Link Referensi / Google Drive / Video / Web</option>
                    </select>
                </div>

                @if ($material->file_path || $material->original_name)
                    <div style="margin-bottom: 1.25rem; background: #f7faf8; border: 1px solid rgba(45, 74, 62, 0.12); border-radius: 10px; padding: 12px 16px;">
                        <div style="font-size: 0.82rem; font-weight: 700; color: #416454; margin-bottom: 4px; text-transform: uppercase; letter-spacing: 0.5px;">
                            Berkas Materi Saat Ini
                        </div>
                        <div style="display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;">
                            <div style="display: flex; align-items: center; gap: 8px; font-size: 0.92rem; color: #1a2f25;">
                                <span style="font-weight: 600;">{{ $material->original_name ?: basename($material->file_path) }}</span>
                                @if ($material->file_size)
                                    <span style="color: #647a6e; font-size: 0.82rem;">({{ number_format($material->file_size / 1024, 1) }} KB)</span>
                                @endif
                            </div>
                            <a href="{{ route('materials.file', $material) }}" target="_blank" rel="noopener noreferrer" style="color: #2d4a3e; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.25); padding: 4px 10px; border-radius: 6px; background: #ffffff;">
                                Buka Berkas
                            </a>
                        </div>
                    </div>
                @endif

                <div style="margin-bottom: 1.25rem;">
                    <label for="material_file" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Unggah Berkas Baru (Opsional - Maks. 25MB)
                    </label>
                    <input type="file" id="material_file" name="material_file" style="width: 100%; padding: 9px 12px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                    <small style="color: #647a6e; display: block; margin-top: 4px;">Pilih berkas baru jika ingin mengganti berkas yang sudah ada. Biarkan kosong jika tidak ingin mengubah berkas.</small>
                </div>

                <div style="margin-bottom: 1.25rem;">
                    <label for="external_url" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Tautan / URL Materi (Opsional)
                    </label>
                    <input type="text" id="external_url" name="external_url" value="{{ old('external_url', $material->external_url) }}" placeholder="https://example.com/slide atau link Google Drive / YouTube" style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa;">
                    <small style="color: #647a6e; display: block; margin-top: 4px;">Tuliskan link jika materi berupa website, video YouTube, atau tautan Google Drive.</small>
                </div>

                <div style="margin-bottom: 1.75rem;">
                    <label for="description" style="display: block; font-weight: 600; font-size: 0.88rem; margin-bottom: 6px; color: #21332a;">
                        Deskripsi / Petunjuk Bacaan
                    </label>
                    <textarea id="description" name="description" rows="4" placeholder="Penjelasan singkat mengenai materi ini..." style="width: 100%; padding: 11px 14px; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 10px; font-family: inherit; font-size: 0.92rem; background: #fbfcfa; resize: vertical;">{{ old('description', $material->description) }}</textarea>
                </div>

                <div style="display: flex; gap: 10px; border-top: 1px solid rgba(45, 74, 62, 0.08); padding-top: 1.25rem;">
                    <button type="submit" style="background: #2d4a3e; color: #ffffff; padding: 10px 22px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);">
                        Simpan Perubahan Materi
                    </button>
                    <a href="{{ route('dosen.courses.show', $course) }}" style="background: #eef5f1; color: #2d4a3e; padding: 10px 20px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.15);">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-layout>
