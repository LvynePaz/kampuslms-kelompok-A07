@php
    $currentUserRole = auth()->user()?->role ?? 'mahasiswa';
    $routePrefix = in_array($currentUserRole, ['admin', 'dosen', 'mahasiswa']) ? $currentUserRole . '.' : 'mahasiswa.';
@endphp

<x-layout title="Detail Materi Perkuliahan">

    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: #4a6354;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            font-weight: 600;
            transition: all 0.2s;
        }
        .back-link:hover {
            color: #1a2f25;
            transform: translateX(-2px);
        }

        .material-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            overflow: hidden;
        }

        .material-header {
            position: relative;
            background: linear-gradient(135deg, #1b3528 0%, #244837 50%, #2f5d47 100%);
            color: #ffffff;
            padding: 2.25rem 2.5rem;
        }

        .course-badge {
            display: inline-block;
            background: rgba(232, 241, 236, 0.18);
            color: #dbebe2;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.85rem;
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .material-header h1 {
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.3px;
        }

        .material-header .meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: #dbebe2;
            flex-wrap: wrap;
        }

        .material-body {
            padding: 2.25rem 2.5rem;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .info-item {
            background: #f7f9f7;
            border: 1px solid rgba(45, 74, 62, 0.08);
            border-radius: 12px;
            padding: 1.15rem 1.25rem;
        }

        .info-item .label {
            font-size: 0.76rem;
            color: #647a6e;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 4px;
        }

        .info-item .value {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1a2f25;
        }

        .section-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #4a6354;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 0.75rem;
        }

        .description-box {
            font-size: 0.98rem;
            color: #21332a;
            line-height: 1.7;
            background: #f7f9f7;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(45, 74, 62, 0.08);
            margin-bottom: 2rem;
            white-space: pre-line;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route($routePrefix . 'courses.show', $course) }}" class="back-link" style="margin-bottom: 0;">
            ← Kembali ke {{ $course->name }}
        </a>

        @can('update', $material)
            @if ($currentUserRole === 'dosen')
                <div style="display: flex; gap: 8px; align-items: center;">
                    <a href="{{ route('dosen.materials.edit', $material) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; background: #e8f1ec; color: #2d4a3e; border: 1px solid rgba(45, 74, 62, 0.2);">
                        Edit Materi
                    </a>
                    <form action="{{ route('dosen.materials.destroy', $material) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="color: #991b1b; font-size: 0.85rem; font-weight: 600; border: 1px solid #fecaca; padding: 7px 14px; border-radius: 8px; background: #fdf2f2; cursor: pointer; font-family: inherit;">
                            Hapus Materi
                        </button>
                    </form>
                </div>
            @endif
        @endcan
    </div>

    @if (session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.92rem; font-weight: 500;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="material-card">
        <div class="material-header">
            <div class="course-badge">{{ $course->code }} &bull; {{ $course->name }}</div>
            <h1>{{ $material->title }}</h1>
            <div class="meta">
                <span>Dosen: {{ $course->lecturer?->name ?? 'Dosen Pengampu' }}</span>
                <span>Diunggah: {{ $material->created_at?->format('d M Y, H:i') ?? '-' }}</span>
            </div>
        </div>

        <div class="material-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Tipe Materi</div>
                    <div class="value" style="text-transform: uppercase;">{{ $material->type }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Nama Berkas / Tautan</div>
                    <div class="value" style="font-size: 0.95rem; word-break: break-all;">
                        {{ $material->original_name ?: ($material->external_url ?: 'Berkas Perkuliahan') }}
                    </div>
                </div>
                @if ($material->file_size)
                    <div class="info-item">
                        <div class="label">Ukuran Berkas</div>
                        <div class="value">{{ $material->formatted_file_size }}</div>
                    </div>
                @endif
            </div>

            <div class="section-title">Deskripsi / Petunjuk Bacaan</div>
            <div class="description-box">
                {{ $material->description ?: 'Tidak ada deskripsi tambahan untuk materi ini.' }}
            </div>

            @php
                $hasFile = $material->file_path || str_starts_with($material->external_url ?? '', 'file:///');
                $isWebLink = $material->external_url && !str_starts_with($material->external_url, 'file:///');
            @endphp

            <div style="background: #fdfefe; border: 1px solid rgba(45, 74, 62, 0.12); border-radius: 14px; padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <div style="font-weight: 700; color: #1a2f25; font-size: 1rem; margin-bottom: 4px;">
                        Akses Berkas Materi Perkuliahan
                    </div>
                    <div style="font-size: 0.88rem; color: #5e7166;">
                        Berkas disimpan secara privat dan hanya dapat diunduh/dibuka oleh mahasiswa terdaftar di mata kuliah ini.
                    </div>
                </div>

                <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                    @if ($hasFile)
                        <a href="{{ route('materials.download', $material) }}" style="display: inline-flex; align-items: center; gap: 8px; background: #2d4a3e; color: #ffffff; padding: 10px 20px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; text-decoration: none; box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            <span>Unduh Berkas</span>
                        </a>
                        <a href="{{ route('materials.file', $material) }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 8px; background: #e8f1ec; color: #2d4a3e; padding: 10px 18px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.2);">
                            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                            <span>Buka di Tab Baru</span>
                        </a>
                    @elseif ($isWebLink)
                        @php
                            $linkUrl = $material->external_url;
                            if (str_contains($linkUrl, 'kampuslms.test')) {
                                $linkUrl = route('materials.file', $material);
                            } elseif (!preg_match('~^(?:f|ht)tps?://~i', $linkUrl)) {
                                $linkUrl = 'https://' . $linkUrl;
                            }
                        @endphp
                        <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 8px; background: #2d4a3e; color: #ffffff; padding: 10px 20px; border-radius: 10px; font-size: 0.92rem; font-weight: 600; text-decoration: none; box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);">
                            Buka Tautan Eksternal
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layout>
