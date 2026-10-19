@php
    $currentUserRole = auth()->user()?->role ?? 'mahasiswa';
@endphp

<x-layout title="Detail Mata Kuliah">

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

        .detail-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            overflow: hidden;
        }

        .detail-header {
            position: relative;
            background: linear-gradient(135deg, #20382d 0%, #29473a 50%, #36594a 100%);
            color: #ffffff;
            padding: 2.5rem 2.5rem;
            overflow: hidden;
        }

        .detail-header::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(123, 178, 151, 0.22) 0%, rgba(41, 71, 58, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .detail-header .code-badge {
            display: inline-block;
            background: rgba(232, 241, 236, 0.16);
            color: #dbebe2;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.85rem;
            letter-spacing: 0.5px;
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .detail-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.6rem;
            letter-spacing: -0.4px;
        }

        .detail-header .meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.9rem;
            color: #dbebe2;
            flex-wrap: wrap;
        }

        .detail-body {
            padding: 2.25rem 2.5rem;
        }

        .section-title {
            font-size: 0.82rem;
            font-weight: 700;
            color: #4a6354;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 0.75rem;
        }

        .description-text {
            font-size: 0.98rem;
            color: #21332a;
            line-height: 1.7;
            background: #f7f9f7;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(45, 74, 62, 0.08);
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
            font-size: 1.1rem;
            font-weight: 700;
            color: #1a2f25;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route($routePrefix . 'courses.index') }}" class="back-link" style="margin-bottom: 0;">
            ← Kembali ke Daftar Mata Kuliah
        </a>

        @if (auth()->user()?->role === 'admin')
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.courses.edit', $course) }}" class="btn-edit" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    <span>Edit Mata Kuliah</span>
                </a>
            </div>
        @endif
    </div>

    @if (session('success'))
        <div style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; padding: 12px 18px; border-radius: 12px; margin-bottom: 1.5rem; font-size: 0.92rem; font-weight: 500;">
            ✓ {{ session('success') }}
        </div>
    @endif

    <div class="detail-card">
        <div class="detail-header">
            <div class="code-badge">{{ $course['code'] }}</div>
            <h1>{{ $course['name'] }}</h1>
            <div class="meta">
                <span> {{ $course->lecturer?->name ?? '-' }}</span>
                <span> {{ $course['sks'] }} SKS</span>
            </div>
        </div>

        <div class="detail-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Kode Mata Kuliah</div>
                    {{-- Menggunakan {{ }} yang escaped — AMAN dari XSS --}}
                    <div class="value">{{ $course['code'] }}</div>
                </div>
                <div class="info-item">
                    <div class="label">SKS</div>
                    <div class="value">{{ $course['sks'] }} SKS</div>
                </div>
                <div class="info-item">
                    <div class="label">Dosen Pengampu</div>
                    <div class="value">{{ $course->lecturer?->name ?? '-' }}</div>
                </div>
            </div>

            <div class="section-title">Deskripsi Mata Kuliah</div>
            {{-- {{ }} aman dari XSS — Laravel akan escape karakter berbahaya --}}
            <div class="description-text">{{ $course['description'] ?? 'Belum ada deskripsi untuk mata kuliah ini.' }}</div>

            {{-- 1. DAFTAR MATERI PERKULIAHAN --}}
            <div style="margin-top: 2.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="section-title" style="margin-bottom: 0;">Materi Perkuliahan ({{ $course->materials->count() }})</div>
                    @can('update', $course)
                        @if (auth()->user()?->role === 'dosen')
                            <a href="{{ route('dosen.courses.materials.create', $course) }}" class="btn-primary" style="padding: 7px 14px; font-size: 0.82rem; border-radius: 8px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>Tambah Materi</span>
                            </a>
                        @endif
                    @endcan
                </div>

                @if ($course->materials->isEmpty())
                    <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.5rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                        Belum ada materi perkuliahan yang diunggah.
                    </div>
                @else
                    <div style="display: grid; gap: 0.75rem;">
                        @foreach ($course->materials as $mat)
                            <div style="background: #f7f9f7; border: 1px solid rgba(45, 74, 62, 0.08); border-radius: 12px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                                <div>
                                    <div style="font-weight: 600; color: #1a2f25; font-size: 0.95rem; margin-bottom: 3px;">
                                        @if (in_array($currentUserRole, ['dosen', 'mahasiswa']))
                                            <a href="{{ route($currentUserRole . '.materials.show', $mat) }}" style="color: #1a2f25; text-decoration: none;">
                                                {{ $mat->title }}
                                            </a>
                                        @else
                                            {{ $mat->title }}
                                        @endif
                                    </div>
                                    @if ($mat->description)
                                        <div style="font-size: 0.85rem; color: #5e7166; margin-bottom: 4px;">{{ $mat->description }}</div>
                                    @endif
                                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        <span style="display: inline-block; background: rgba(45, 74, 62, 0.1); color: #2d4a3e; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase;">
                                            {{ $mat->type }}
                                        </span>
                                        @if ($mat->original_name)
                                            <span style="font-size: 0.8rem; color: #5e7166; display: inline-flex; align-items: center; gap: 4px;">
                                                📁 {{ $mat->original_name }}
                                                @if ($mat->file_size)
                                                    <span>({{ $mat->formatted_file_size }})</span>
                                                @endif
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                    @php
                                        // Cek apakah ada file fisik atau file:/// path lokal atau seeded rps
                                        $hasFile = $mat->file_path || str_starts_with($mat->external_url ?? '', 'file:///');
                                        // Cek apakah ada link web
                                        $isWebLink = $mat->external_url && !str_starts_with($mat->external_url, 'file:///');
                                    @endphp

                                    @if ($hasFile)
                                        <a href="{{ route('materials.download', $mat) }}" style="color: #ffffff; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: 1px solid #2d4a3e; padding: 5px 12px; border-radius: 8px; background: #2d4a3e; display: inline-flex; align-items: center; gap: 4px;">
                                            <svg width="13" height="13" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                            <span>Unduh</span>
                                        </a>
                                        <a href="{{ route('materials.file', $mat) }}" target="_blank" rel="noopener noreferrer" style="color: #2d4a3e; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.2); padding: 5px 12px; border-radius: 8px; background: #ffffff;">
                                            Buka
                                        </a>
                                    @elseif ($isWebLink)
                                        @php
                                            $linkUrl = $mat->external_url;
                                            if (str_contains($linkUrl, 'kampuslms.test')) {
                                                $linkUrl = route('materials.file', $mat);
                                            } elseif (!preg_match('~^(?:f|ht)tps?://~i', $linkUrl)) {
                                                $linkUrl = 'https://' . $linkUrl;
                                            }
                                        @endphp
                                        <a href="{{ $linkUrl }}" target="_blank" rel="noopener noreferrer" style="color: #2d4a3e; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.2); padding: 5px 12px; border-radius: 8px; background: #ffffff;">
                                            Buka Tautan
                                        </a>
                                    @endif

                                    @can('update', $mat)
                                        @if (auth()->user()?->role === 'dosen')
                                            <a href="{{ route('dosen.materials.edit', $mat) }}" style="color: #4b5e28; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(75, 94, 40, 0.25); padding: 5px 10px; border-radius: 8px; background: #f7f9f2;">
                                                Edit
                                            </a>
                                        @endif
                                    @endcan

                                    @can('delete', $mat)
                                        @if (auth()->user()?->role === 'dosen')
                                            <form action="{{ route('dosen.materials.destroy', $mat) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus materi ini?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="color: #991b1b; font-size: 0.82rem; font-weight: 600; border: 1px solid #fecaca; padding: 5px 10px; border-radius: 8px; background: #fdf2f2; cursor: pointer; font-family: inherit;">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 2. DAFTAR TUGAS PERKULIAHAN --}}
            @php
                $currentUserRole = auth()->user()?->role ?? 'mahasiswa';
                $displayedAssignments = $currentUserRole === 'mahasiswa'
                    ? $course->assignments->where('status', 'published')
                    : $course->assignments;
            @endphp
            <div style="margin-top: 2.25rem;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                    <div class="section-title" style="margin-bottom: 0;">Tugas Perkuliahan ({{ $displayedAssignments->count() }})</div>
                    @can('update', $course)
                        @if (auth()->user()?->role === 'dosen')
                            <a href="{{ route('dosen.courses.assignments.create', $course) }}" class="btn-primary" style="padding: 7px 14px; font-size: 0.82rem; border-radius: 8px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>Buat Tugas</span>
                            </a>
                        @endif
                    @endcan
                </div>

                @if ($displayedAssignments->isEmpty())
                    <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.5rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                        Belum ada tugas perkuliahan untuk kelas ini.
                    </div>
                @else
                    <div style="display: grid; gap: 0.75rem;">
                        @foreach ($displayedAssignments as $asg)
                            <div style="background: #f7f9f7; border: 1px solid rgba(45, 74, 62, 0.08); border-radius: 12px; padding: 1rem 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.75rem;">
                                <div>
                                    <div style="font-weight: 600; color: #1a2f25; font-size: 0.95rem; margin-bottom: 3px;">
                                        {{ $asg->title }}
                                    </div>
                                    <div style="font-size: 0.82rem; color: #5e7166;">
                                        Deadline: <strong>{{ $asg->due_at?->format('d M Y, H:i') ?? '-' }}</strong> &bull; Skor Maks: {{ $asg->max_score }}
                                    </div>
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                                    <span style="display: inline-block; background: {{ $asg->status === 'published' ? '#ecfdf5' : '#f3f4f6' }}; color: {{ $asg->status === 'published' ? '#047857' : '#4b5563' }}; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 600;">
                                        {{ ucfirst($asg->status) }}
                                    </span>

                                    @if (in_array($currentUserRole, ['dosen', 'mahasiswa']))
                                        <a href="{{ route($currentUserRole . '.assignments.show', $asg) }}" style="color: #2d4a3e; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.2); padding: 5px 12px; border-radius: 8px; background: #ffffff;">
                                            Buka Tugas
                                        </a>
                                    @endif

                                    @can('update', $asg)
                                        @if ($currentUserRole === 'dosen')
                                            <a href="{{ route('dosen.assignments.edit', $asg) }}" style="color: #4b5e28; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(75, 94, 40, 0.25); padding: 5px 10px; border-radius: 8px; background: #f7f9f2;">
                                                Edit
                                            </a>
                                            <form action="{{ route('dosen.assignments.destroy', $asg) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="color: #991b1b; font-size: 0.82rem; font-weight: 600; border: 1px solid #fecaca; padding: 5px 10px; border-radius: 8px; background: #fdf2f2; cursor: pointer; font-family: inherit;">
                                                    Hapus
                                                </button>
                                            </form>
                                        @endif
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- 3. DAFTAR MAHASISWA TERDAFTAR --}}
            <div style="margin-top: 2.25rem;">
                <div class="section-title">Mahasiswa Terdaftar ({{ $course->students->count() }})</div>
                @if ($course->students->isEmpty())
                    <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.5rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                        Belum ada mahasiswa yang terdaftar di kelas ini.
                    </div>
                @else
                    <div style="display: flex; flex-wrap: wrap; gap: 8px; margin-top: 0.5rem;">
                        @foreach ($course->students as $std)
                            <span style="background: #f0f5f2; border: 1px solid rgba(45, 74, 62, 0.12); color: #1a2f25; padding: 5px 12px; border-radius: 8px; font-size: 0.84rem; display: inline-flex; align-items: center; gap: 6px;">
                                <strong>{{ $std->name }}</strong>
                                <small style="color: #647a6e;">({{ $std->nim_nip ?? $std->email }})</small>
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layout>
