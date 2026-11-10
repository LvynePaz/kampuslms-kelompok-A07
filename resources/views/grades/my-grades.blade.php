<x-layout title="Nilai Saya">
    <style>
        .page-intro {
            margin-bottom: 1.5rem;
        }
        .page-intro h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a2f25;
            margin-bottom: 4px;
        }
        .page-intro p {
            color: #647a6e;
            font-size: 0.9rem;
        }

        /* USER PROFILE STRIP (SESUAI GAMBAR 1) */
        .student-profile-strip {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.1);
            border-radius: 14px;
            padding: 1rem 1.35rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 10px rgba(35, 62, 49, 0.03);
        }
        .student-profile-left {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .student-avatar {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #2d4a3e;
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: 700;
        }
        .student-meta h2 {
            font-size: 1.05rem;
            font-weight: 700;
            color: #1a2f25;
            margin: 0;
        }
        .student-meta p {
            font-size: 0.82rem;
            color: #5e7166;
            margin: 0;
        }

        /* COURSE GRADE REPORT (SATU MATA KULIAH TAMPIL PENUH) */
        .course-report-box {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.1);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);
            margin-bottom: 2rem;
            overflow: hidden;
            display: block;
        }

        .course-report-header {
            padding: 1.35rem 1.75rem;
            background: #f7f9f7;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .course-report-title h2 {
            margin: 0 0 4px;
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a2f25;
        }
        .course-report-title p {
            margin: 0;
            font-size: 0.85rem;
            color: #5e7166;
        }

        /* MINI SUMMARY TIAP MATKUL */
        .course-stat-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            padding: 1.25rem 1.75rem;
            background: #ffffff;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
        }
        .course-stat-item {
            background: #fbfdfc;
            border: 1px solid rgba(45, 74, 62, 0.08);
            border-radius: 10px;
            padding: 0.85rem 1.15rem;
        }
        .course-stat-item .label {
            font-size: 0.74rem;
            font-weight: 700;
            color: #647a6e;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 3px;
        }
        .course-stat-item .value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #1a2f25;
        }

        /* TABEL ITEM PENILAIAN (SESUAI GAMBAR 1) */
        .grades-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }
        .grades-table th {
            color: #4a6354;
            background: #fbfdfc;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 11px 16px;
            border-bottom: 1px solid rgba(45, 74, 62, 0.1);
            white-space: nowrap;
        }
        .grades-table td {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(45, 74, 62, 0.06);
            color: #1a2f25;
            vertical-align: middle;
        }
        .grades-table tr:hover td {
            background: #fbfdfc;
        }
        .grades-table .total-row td {
            background: #f0f5f2;
            font-weight: 700;
            color: #1b3528;
            border-top: 2px solid rgba(45, 74, 62, 0.2);
            border-bottom: none;
            padding: 14px 16px;
        }
    </style>

    <div class="page-intro">
        <h1>Rekap Nilai Perkuliahan</h1>
        <p>Laporan nilai evaluasi akademik per mata kuliah yang Anda ikuti.</p>
    </div>

    {{-- IDENTITAS MAHASISWA (SESUAI GAMBAR 1: LIHAT LAPORAN PENGGUNA) --}}
    <div class="student-profile-strip">
        <div class="student-profile-left">
            <div class="student-avatar">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="student-meta">
                <h2>{{ auth()->user()->name }}</h2>
                <p>NIM: {{ auth()->user()->nim_nip ?? '-' }} &bull; Mahasiswa</p>
            </div>
        </div>
        <div>
            <span style="background: rgba(45, 74, 62, 0.1); color: #2d4a3e; padding: 4px 12px; border-radius: 999px; font-size: 0.8rem; font-weight: 600;">
                {{ $courses->count() }} Mata Kuliah Terdaftar
            </span>
        </div>
    </div>

    @if ($courses->isEmpty())
        <div style="padding: 2.5rem; text-align: center; background: #ffffff; border-radius: 16px; border: 1px solid rgba(45, 74, 62, 0.1); color: #647a6e;">
            Anda belum terdaftar pada mata kuliah apa pun.
        </div>
    @else
        {{-- DAFTAR LAPORAN NILAI PER MATA KULIAH (TERPISAH PER BOX) --}}
        @foreach ($courses as $course)
            @php
                $courseAssignments = $course->assignments;
                $courseTotalAsg = $courseAssignments->count();
                $courseGradedCount = 0;
                $courseTotalScore = 0;
                $courseTotalMaxScore = 0;

                foreach ($courseAssignments as $asg) {
                    $sub = $asg->submissions->first();
                    if ($sub?->grade) {
                        $courseGradedCount++;
                        $courseTotalScore += $sub->grade->score;
                        $courseTotalMaxScore += $asg->max_score;
                    }
                }

                $courseAverage = $courseGradedCount > 0 ? ($courseTotalScore / $courseGradedCount) : null;
            @endphp

            <section id="course-{{ $course->id }}" class="course-report-box {{ $loop->first ? 'is-active' : '' }}">
                {{-- HEADER MATKUL (SESUAI GAMBAR 1) --}}
                <header class="course-report-header">
                    <div class="course-report-title">
                        <h2>{{ $course->name }} ({{ $course->code }})</h2>
                        <p>Dosen Pengampu: <strong>{{ $course->lecturer?->name ?? '-' }}</strong> &bull; Beban: {{ $course->sks }} SKS</p>
                    </div>
                    <a href="{{ route('mahasiswa.courses.show', $course) }}" style="display: inline-flex; align-items: center; gap: 4px; padding: 6px 14px; background: #ffffff; color: #2d4a3e; border: 1px solid rgba(45, 74, 62, 0.2); border-radius: 8px; font-size: 0.82rem; font-weight: 600; text-decoration: none;">
                        Buka Kelas
                    </a>
                </header>

                {{-- STATISTIK MATA KULIAH INI --}}
                <div class="course-stat-bar">
                    <div class="course-stat-item">
                        <div class="label">Total Tugas Kelas</div>
                        <div class="value">{{ $courseTotalAsg }}</div>
                    </div>
                    <div class="course-stat-item">
                        <div class="label">Tugas Telah Dinilai</div>
                        <div class="value" style="color: #047857;">{{ $courseGradedCount }}</div>
                    </div>
                    <div class="course-stat-item">
                        <div class="label">Rata-rata Nilai Matkul Ini</div>
                        <div class="value" style="color: #2d4a3e;">
                            {{ $courseAverage !== null ? number_format($courseAverage, 2) : '-' }}
                        </div>
                    </div>
                </div>

                {{-- TABEL ITEM PENILAIAN --}}
                @if ($courseAssignments->isEmpty())
                    <div style="padding: 2rem; color: #647a6e; font-size: 0.88rem; text-align: center;">
                        Belum ada tugas atau evaluasi pada mata kuliah ini.
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th style="min-width: 200px;">Item Penilaian (Tugas)</th>
                                    <th style="white-space: nowrap; width: 160px;">Batas Waktu</th>
                                    <th style="white-space: nowrap; width: 150px;">Status Tugas</th>
                                    <th style="white-space: nowrap; width: 110px;">Rentang</th>
                                    <th style="white-space: nowrap; width: 110px;">Nilai</th>
                                    <th style="min-width: 220px;">Catatan / Feedback</th>
                                    <th style="text-align: right; width: 110px; white-space: nowrap;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($courseAssignments as $asg)
                                    @php
                                        $sub = $asg->submissions->first();
                                        $grd = $sub?->grade;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong style="color: #1a2f25; font-size: 0.92rem; line-height: 1.4; display: block;">{{ $asg->title }}</strong>
                                        </td>
                                        <td style="color: #5e7166; font-size: 0.82rem; white-space: nowrap;">
                                            {{ $asg->due_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                                        </td>
                                        <td style="white-space: nowrap;">
                                            @if (! $sub)
                                                <span style="display: inline-block; white-space: nowrap; padding: 2px 8px; border-radius: 999px; background: #fffbeb; color: #b45309; font-size: 0.76rem; font-weight: 600; border: 1px solid #fde68a;">
                                                    Belum Kumpul
                                                </span>
                                            @elseif ($sub->is_late)
                                                <span style="display: inline-block; white-space: nowrap; padding: 2px 8px; border-radius: 999px; background: #fff7ed; color: #b45309; font-size: 0.76rem; font-weight: 600; border: 1px solid #fed7aa;">
                                                    Terlambat
                                                </span>
                                            @else
                                                <span style="display: inline-block; white-space: nowrap; padding: 2px 8px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 0.76rem; font-weight: 600; border: 1px solid #a7f3d0;">
                                                    Tepat Waktu
                                                </span>
                                            @endif
                                        </td>
                                        <td style="color: #5e7166; font-size: 0.84rem; white-space: nowrap;">
                                            0 - {{ $asg->max_score }}
                                        </td>
                                        <td style="white-space: nowrap;">
                                            @if ($grd)
                                                <span style="display: inline-block; white-space: nowrap; padding: 3px 9px; border-radius: 6px; background: #ecfdf5; color: #047857; font-weight: 800; font-size: 0.95rem;">
                                                    {{ $grd->score }}
                                                </span>
                                            @elseif ($sub)
                                                <span style="color: #92400e; font-size: 0.8rem; font-style: italic; white-space: nowrap;">
                                                    Menunggu nilai
                                                </span>
                                            @else
                                                <span style="color: #94a3b8;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($grd?->feedback)
                                                <div style="font-size: 0.82rem; color: #374151; max-width: 280px; word-break: break-word; line-height: 1.45;">
                                                    "{{ $grd->feedback }}"
                                                </div>
                                            @elseif ($grd)
                                                <span style="color: #94a3b8; font-size: 0.8rem;">Tanpa catatan</span>
                                            @else
                                                <span style="color: #94a3b8;">-</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right; white-space: nowrap;">
                                            <a href="{{ route('mahasiswa.assignments.show', $asg) }}" style="display: inline-block; padding: 5px 12px; border-radius: 7px; border: 1px solid rgba(45, 74, 62, 0.2); background: #ffffff; color: #2d4a3e; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                                                Detail Tugas
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach

                                {{-- BARIS AKHIR: NILAI RATA-RATA / NILAI AKHIR MATKUL INI (SESUAI GAMBAR 1) --}}
                                <tr class="total-row">
                                    <td colspan="3">
                                        Nilai Akhir / Rata-rata Mata Kuliah: {{ $course->name }}
                                    </td>
                                    <td>0 - 100</td>
                                    <td>
                                        @if ($courseAverage !== null)
                                            <span style="display: inline-block; padding: 4px 10px; border-radius: 7px; background: #2d4a3e; color: #ffffff; font-size: 1rem; font-weight: 800;">
                                                {{ number_format($courseAverage, 2) }}
                                            </span>
                                        @else
                                            <span style="color: #647a6e;">-</span>
                                        @endif
                                    </td>
                                    <td colspan="2" style="font-size: 0.82rem; color: #4a6354; font-weight: normal;">
                                        @if ($courseGradedCount > 0)
                                            Dihitung dari {{ $courseGradedCount }} tugas yang telah dinilai.
                                        @else
                                            Belum ada tugas yang dinilai oleh dosen.
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    @endif
</x-layout>
