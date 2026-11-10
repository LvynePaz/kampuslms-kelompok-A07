@php
    $currentUserRole = auth()->user()?->role ?? 'mahasiswa';
@endphp

<x-layout title="Detail Mata Kuliah: {{ $course['name'] }}">

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
            padding: 2.25rem 2.5rem 1.75rem;
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
            font-size: 1.9rem;
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

        /* SUB-NAVIGATION TABS (SAGE HARMONY — GAYA GAMBAR 1) */
        .course-tabs-bar {
            background: #1d3329;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            gap: 0.25rem;
            padding: 0 1.5rem;
            overflow-x: auto;
            scrollbar-width: none;
        }
        .course-tabs-bar::-webkit-scrollbar {
            display: none;
        }
        .course-tab-item {
            background: transparent;
            border: none;
            color: rgba(232, 241, 236, 0.8);
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.95rem 1.4rem;
            cursor: pointer;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: inherit;
        }
        .course-tab-item:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }
        .course-tab-item.active {
            color: #ffffff;
            border-bottom: 3px solid #ffffff; /* Garis bawah putih tebal sesuai Gambar 1 */
            font-weight: 700;
        }
        .course-tab-count {
            background: rgba(232, 241, 236, 0.2);
            color: #ffffff;
            padding: 1px 7px;
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 700;
        }

        .detail-body {
            padding: 2.25rem 2.5rem;
        }

        .section-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #4a6354;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            margin-bottom: 0.75rem;
        }

        .description-text {
            font-size: 0.95rem;
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

        /* TUGAS PER MINGGU (GAMBAR 2 STYLE — SAGE HARMONY) */
        .task-week-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(285px, 1fr));
            gap: 1.25rem;
            margin-top: 1rem;
        }

        .task-week-card {
            background: #ffffff;
            border-radius: 14px;
            border: 1px solid rgba(45, 74, 62, 0.12);
            border-top: 4px solid #2d4a3e; /* Garis strip atas warna tema Sage sesuai Gambar 2 */
            padding: 1.35rem 1.25rem;
            box-shadow: 0 4px 14px rgba(35, 62, 49, 0.05);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .task-week-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(35, 62, 49, 0.1);
            border-color: rgba(45, 74, 62, 0.3);
        }

        .task-week-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 44px;
            height: 44px;
            border: 2px solid #5e7166; /* Kotak rounded outline sesuai Gambar 2 */
            border-radius: 10px;
            font-size: 1.35rem;
            font-weight: 700;
            color: #243c32;
            background: #fbfdfc;
            margin-bottom: 0.85rem;
        }

        .task-week-title {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1a2f25;
            line-height: 1.4;
            margin-bottom: 0.5rem;
        }

        .task-week-meta {
            font-size: 0.82rem;
            color: #5e7166;
            margin-bottom: 1.15rem;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .task-week-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 0.85rem;
            border-top: 1px solid rgba(45, 74, 62, 0.08);
            gap: 0.5rem;
            flex-wrap: wrap;
        }

        /* TABEL PESERTA & NILAI */
        .lms-table-wrap {
            overflow-x: auto;
            border: 1px solid rgba(45, 74, 62, 0.1);
            border-radius: 12px;
            background: #ffffff;
            margin-top: 1rem;
        }
        .lms-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
            text-align: left;
        }
        .lms-table th {
            background: #f0f5f2;
            color: #243c32;
            font-weight: 700;
            padding: 11px 16px;
            border-bottom: 1px solid rgba(45, 74, 62, 0.12);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .lms-table td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(45, 74, 62, 0.06);
            color: #1a2f25;
            vertical-align: middle;
        }
        .lms-table tr:last-child td {
            border-bottom: none;
        }
        .lms-table tr:hover td {
            background: #f9fbf9;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route($routePrefix . 'courses.index') }}" class="back-link" style="margin-bottom: 0;">
            Kembali
        </a>

        @if (auth()->user()?->role === 'admin')
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('admin.courses.edit', $course) }}" class="btn-edit" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; background: #eef5f1; color: #2d4a3e; border: 1px solid rgba(45, 74, 62, 0.2);">
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
        {{-- HEADER MATA KULIAH --}}
        <div class="detail-header">
            <div class="code-badge">{{ $course['code'] }}</div>
            <h1>{{ $course['name'] }}</h1>
            <div class="meta">
                <span>Dosen: <strong>{{ $course->lecturer?->name ?? '-' }}</strong></span>
                <span>Beban: <strong>{{ $course['sks'] }} SKS</strong></span>
            </div>
        </div>

        {{-- SUB-NAVIGATION TABS (GAYA GAMBAR 1 DENGAN TEMA WARNA PROJECT) --}}
        @php
            $displayedAssignments = $currentUserRole === 'mahasiswa'
                ? $course->assignments->where('status', 'published')->values()
                : $course->assignments->values();
        @endphp
        <div class="course-tabs-bar" role="tablist">
            <button type="button" class="course-tab-item active" data-tab="kursus" role="tab" aria-selected="true">
                Kursus
            </button>
            <button type="button" class="course-tab-item" data-tab="peserta" role="tab" aria-selected="false">
                Peserta <span class="course-tab-count">{{ $course->students->count() }}</span>
            </button>
            <button type="button" class="course-tab-item" data-tab="nilai" role="tab" aria-selected="false">
                Nilai
            </button>
        </div>

        <div class="detail-body">

            {{-- ======================================================== --}}
            {{-- TAB 1: KURSUS (DESKRIPSI, MATERI & TUGAS PER MINGGU)     --}}
            {{-- ======================================================== --}}
            <div id="tab-kursus" class="course-tab-panel" role="tabpanel">
                <div class="info-grid">
                    <div class="info-item">
                        <div class="label">Kode Mata Kuliah</div>
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
                <div class="description-text">{{ $course['description'] ?? 'Belum ada deskripsi untuk mata kuliah ini.' }}</div>

                {{-- A. DAFTAR TUGAS PERKULIAHAN PER MINGGU (GAYA GAMBAR 2) --}}
                <div style="margin-top: 2.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div>
                            <div class="section-title" style="margin-bottom: 2px;">Tugas Perkuliahan (Per Minggu)</div>
                            <small style="color: #647a6e;">Daftar penugasan dan evaluasi perkuliahan semester berjalan.</small>
                        </div>
                        @can('update', $course)
                            @if (auth()->user()?->role === 'dosen')
                                <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                                    <a href="{{ route('dosen.courses.assignments.create', $course) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #2d4a3e; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 0.82rem; font-weight: 600;">
                                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        <span>Buat Tugas Baru</span>
                                    </a>
                                </div>
                            @endif
                        @endcan
                    </div>

                    @if ($displayedAssignments->isEmpty())
                        <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.75rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                            Belum ada tugas perkuliahan untuk kelas ini.
                        </div>
                    @else
                        {{-- GRID KARTU PER MINGGU (GAMBAR 2) --}}
                        <div class="task-week-grid">
                            @foreach ($displayedAssignments as $asg)
                                @php
                                    $studentSubmission = $currentUserRole === 'mahasiswa'
                                        ? $asg->submissions->firstWhere('user_id', auth()->id())
                                        : null;
                                    $hasGraded = $studentSubmission && $studentSubmission->grade;
                                @endphp
                                <div class="task-week-card">
                                    <div>
                                        {{-- BADGE ANGKA KOTAK BERGARIS (SESUAI GAMBAR 2) --}}
                                        <div class="task-week-badge">
                                            {{ $loop->iteration }}
                                        </div>

                                        <h3 class="task-week-title">
                                            {{ $asg->title }}
                                        </h3>

                                        <div class="task-week-meta">
                                            <span>Deadline: <strong>{{ $asg->due_at?->format('d M Y, H:i') ?? '-' }}</strong></span>
                                            <span>Skor Maksimum: <strong>{{ $asg->max_score }} Poin</strong></span>
                                            @if ($currentUserRole === 'dosen')
                                                <span>Pengumpulan: <strong>{{ $asg->submissions->count() }} Mahasiswa</strong></span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="task-week-footer">
                                        {{-- STATUS BADGE --}}
                                        @if ($currentUserRole === 'mahasiswa')
                                            @if ($hasGraded)
                                                <span style="background: #ecfdf5; color: #047857; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; border: 1px solid #a7f3d0;">
                                                    Nilai: {{ $studentSubmission->grade->score }}/{{ $asg->max_score }}
                                                </span>
                                            @elseif ($studentSubmission)
                                                <span style="background: #eff6ff; color: #1d4ed8; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; border: 1px solid #bfdbfe;">
                                                    Sudah Dikumpulkan
                                                </span>
                                            @else
                                                <span style="background: #fffbeb; color: #b45309; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 700; border: 1px solid #fde68a;">
                                                    Belum Kumpul
                                                </span>
                                            @endif

                                            <a href="{{ route('mahasiswa.assignments.show', $asg) }}" style="color: #2d4a3e; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.2); padding: 5px 12px; border-radius: 8px; background: #ffffff;">
                                                Buka Tugas
                                            </a>
                                        @else
                                            <span style="display: inline-block; background: {{ $asg->status === 'published' ? '#ecfdf5' : '#f3f4f6' }}; color: {{ $asg->status === 'published' ? '#047857' : '#4b5563' }}; padding: 3px 10px; border-radius: 999px; font-size: 0.78rem; font-weight: 600;">
                                                {{ ucfirst($asg->status) }}
                                            </span>

                                            <div style="display: flex; gap: 6px; align-items: center; flex-wrap: wrap;">
                                                @if (in_array($currentUserRole, ['dosen', 'mahasiswa']))
                                                    <a href="{{ route($currentUserRole . '.assignments.show', $asg) }}" style="color: #2d4a3e; font-size: 0.85rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.2); padding: 5px 10px; border-radius: 8px; background: #ffffff;">
                                                        Buka
                                                    </a>
                                                @endif

                                                @can('update', $asg)
                                                    @if ($currentUserRole === 'dosen')
                                                        <a href="{{ route('dosen.assignments.edit', $asg) }}" style="color: #4b5e28; font-size: 0.82rem; font-weight: 600; text-decoration: none; border: 1px solid rgba(75, 94, 40, 0.25); padding: 5px 8px; border-radius: 8px; background: #f7f9f2;">
                                                            Edit
                                                        </a>
                                                        <form action="{{ route('dosen.assignments.destroy', $asg) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?')" style="display: inline;">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" style="color: #991b1b; font-size: 0.82rem; font-weight: 600; border: 1px solid #fecaca; padding: 5px 8px; border-radius: 8px; background: #fdf2f2; cursor: pointer; font-family: inherit;">
                                                                Hapus
                                                            </button>
                                                        </form>
                                                    @endif
                                                @endcan
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- B. DAFTAR MATERI PERKULIAHAN --}}
                <div style="margin-top: 2.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.85rem; flex-wrap: wrap; gap: 0.5rem;">
                        <div class="section-title" style="margin-bottom: 0;">Materi Perkuliahan ({{ $course->materials->count() }})</div>
                        @can('update', $course)
                            @if (auth()->user()?->role === 'dosen')
                                <a href="{{ route('dosen.courses.materials.create', $course) }}" style="display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: #2d4a3e; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 0.82rem; font-weight: 600;">
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
                                                    File: {{ $mat->original_name }}
                                                    @if ($mat->file_size)
                                                        <span>({{ $mat->formatted_file_size }})</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                    <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                                        @php
                                            $hasFile = $mat->file_path || str_starts_with($mat->external_url ?? '', 'file:///');
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
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 2: PESERTA (DOSEN PENGAMPU & DAFTAR MAHASISWA)       --}}
            {{-- ======================================================== --}}
            <div id="tab-peserta" class="course-tab-panel" style="display: none;" role="tabpanel">
                {{-- Dosen Pengampu --}}
                <div class="section-title">Dosen Pengampu</div>
                <div style="background: #f7f9f7; border: 1px solid rgba(45, 74, 62, 0.1); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; margin-bottom: 2rem;">
                    <div style="width: 50px; height: 50px; border-radius: 50%; background: #2d4a3e; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 700;">
                        {{ strtoupper(substr($course->lecturer?->name ?? 'D', 0, 1)) }}
                    </div>
                    <div>
                        <div style="font-weight: 700; color: #1a2f25; font-size: 1.05rem;">
                            {{ $course->lecturer?->name ?? 'Belum Ditugaskan' }}
                        </div>
                        <div style="font-size: 0.85rem; color: #5e7166;">
                            NIP: {{ $course->lecturer?->nim_nip ?? '-' }} &bull; Email: {{ $course->lecturer?->email ?? '-' }}
                        </div>
                    </div>
                </div>

                {{-- Daftar Mahasiswa --}}
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                    <div class="section-title" style="margin-bottom: 0;">Mahasiswa Terdaftar ({{ $course->students->count() }})</div>
                </div>

                @if ($course->students->isEmpty())
                    <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.75rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                        Belum ada mahasiswa yang terdaftar pada mata kuliah ini.
                    </div>
                @else
                    <div class="lms-table-wrap">
                        <table class="lms-table">
                            <thead>
                                <tr>
                                    <th style="width: 50px;">No</th>
                                    <th>Nama Mahasiswa</th>
                                    <th>NIM</th>
                                    <th>Email</th>
                                    <th>Status Terdaftar</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($course->students as $std)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div style="font-weight: 600; color: #1a2f25;">
                                                {{ $std->name }}
                                                @if ($std->id === auth()->id())
                                                    <span style="background: rgba(45, 74, 62, 0.1); color: #2d4a3e; padding: 2px 7px; border-radius: 6px; font-size: 0.7rem; font-weight: 700; margin-left: 4px;">Anda</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td>{{ $std->nim_nip ?? '-' }}</td>
                                        <td style="color: #5e7166;">{{ $std->email }}</td>
                                        <td>
                                            <span style="background: #ecfdf5; color: #047857; padding: 3px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 600;">
                                                Aktif
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- ======================================================== --}}
            {{-- TAB 3: NILAI (REKAP DOSEN ATAU NILAI SAYA MAHASISWA)    --}}
            {{-- ======================================================== --}}
            <div id="tab-nilai" class="course-tab-panel" style="display: none;" role="tabpanel">
                @if ($currentUserRole === 'dosen')
                    {{-- TAMPILAN TAB NILAI UNTUK DOSEN --}}
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
                        <div>
                            <div class="section-title" style="margin-bottom: 2px;">Rekapitulasi Nilai Kelas</div>
                            <small style="color: #647a6e;">Kelola dan beri penilaian tugas untuk seluruh mahasiswa terdaftar.</small>
                        </div>
                        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                            <a href="{{ route('dosen.courses.grades', $course) }}" style="display: inline-flex; align-items: center; padding: 8px 16px; background: #2d4a3e; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                                Buka Rekap Nilai Lengkap
                            </a>
                            <a href="{{ route('dosen.courses.grades.export', $course) }}" style="display: inline-flex; align-items: center; padding: 8px 16px; background: #205c48; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                                Ekspor Nilai (CSV)
                            </a>
                        </div>
                    </div>

                    @if ($course->assignments->isEmpty())
                        <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.75rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                            Belum ada tugas untuk dinilai. Silakan buat tugas terlebih dahulu.
                        </div>
                    @else
                        <div class="lms-table-wrap">
                            <table class="lms-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama Tugas</th>
                                        <th>Deadline</th>
                                        <th>Pengumpulan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($course->assignments as $asg)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td><strong>{{ $asg->title }}</strong></td>
                                            <td>{{ $asg->due_at?->format('d M Y, H:i') ?? '-' }}</td>
                                            <td>
                                                <span style="font-weight: 600; color: #2d4a3e;">
                                                    {{ $asg->submissions->count() }} / {{ $course->students->count() }} Mahasiswa
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('dosen.assignments.submissions', $asg) }}" style="color: #2d4a3e; font-weight: 600; text-decoration: none; border: 1px solid rgba(45, 74, 62, 0.25); padding: 5px 12px; border-radius: 7px; background: #f7f9f7; font-size: 0.82rem;">
                                                    Beri / Lihat Nilai
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                @else
                    {{-- TAMPILAN TAB NILAI UNTUK MAHASISWA --}}
                    <div class="section-title">Nilai Tugas Perkuliahan Anda</div>

                    @if ($displayedAssignments->isEmpty())
                        <div style="background: #fbfdfc; border: 1px dashed rgba(45, 74, 62, 0.2); border-radius: 12px; padding: 1.75rem; text-align: center; color: #647a6e; font-size: 0.9rem;">
                            Belum ada nilai tugas yang tersedia untuk mata kuliah ini.
                        </div>
                    @else
                        <div class="lms-table-wrap">
                            <table class="lms-table">
                                <thead>
                                    <tr>
                                        <th style="width: 50px;">No</th>
                                        <th>Nama Tugas</th>
                                        <th>Deadline</th>
                                        <th>Status Pengumpulan</th>
                                        <th>Nilai Diperoleh</th>
                                        <th>Catatan / Feedback</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($displayedAssignments as $asg)
                                        @php
                                            $sub = $asg->submissions->firstWhere('user_id', auth()->id());
                                            $grd = $sub?->grade;
                                        @endphp
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <a href="{{ route('mahasiswa.assignments.show', $asg) }}" style="color: #1a2f25; font-weight: 600; text-decoration: none;">
                                                    {{ $asg->title }}
                                                </a>
                                            </td>
                                            <td>{{ $asg->due_at?->format('d M Y, H:i') ?? '-' }}</td>
                                            <td>
                                                @if ($sub)
                                                    <span style="background: #eff6ff; color: #1d4ed8; padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                                        Dikumpulkan ({{ $sub->submitted_at?->format('d M') ?? 'Ada' }})
                                                    </span>
                                                @else
                                                    <span style="background: #fffbeb; color: #b45309; padding: 3px 8px; border-radius: 6px; font-size: 0.78rem; font-weight: 600;">
                                                        Belum Mengumpulkan
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($grd)
                                                    <span style="font-size: 1.05rem; font-weight: 700; color: #047857;">
                                                        {{ $grd->score }}
                                                    </span>
                                                    <small style="color: #647a6e;"> / {{ $asg->max_score }}</small>
                                                @else
                                                    <span style="color: #94a3b8; font-style: italic;">Belum Dinilai</span>
                                                @endif
                                            </td>
                                            <td style="color: #4a6354; font-size: 0.85rem;">
                                                {{ $grd?->feedback ?? '-' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>

    {{-- SCRIPT INTERAKTIF PINDAH TAB (INSTAN TANPA RELOAD) --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabs = document.querySelectorAll('.course-tab-item');
            const panels = document.querySelectorAll('.course-tab-panel');

            function activateTab(tabKey) {
                tabs.forEach(tab => {
                    const isActive = tab.getAttribute('data-tab') === tabKey;
                    tab.classList.toggle('active', isActive);
                    tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
                });

                panels.forEach(panel => {
                    if (panel.id === 'tab-' + tabKey) {
                        panel.style.display = 'block';
                    } else {
                        panel.style.display = 'none';
                    }
                });
            }

            tabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const tabKey = this.getAttribute('data-tab');
                    activateTab(tabKey);
                    if (history.replaceState) {
                        history.replaceState(null, null, '#' + tabKey);
                    }
                });
            });

            // Baca hash URL jika ada misal #peserta atau #nilai
            const currentHash = window.location.hash.replace('#', '');
            if (currentHash && ['kursus', 'peserta', 'nilai'].includes(currentHash)) {
                activateTab(currentHash);
            }
        });
    </script>

</x-layout>
