<x-layout title="Rekap Nilai Saya">
    <style>
        .grades-summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.08);
            border-radius: 14px;
            padding: 1.25rem 1.5rem;
            box-shadow: 0 4px 16px -2px rgba(35, 62, 49, 0.04);
        }
        .stat-card .label {
            font-size: 0.78rem;
            color: #647a6e;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }
        .stat-card .value {
            font-size: 1.6rem;
            font-weight: 800;
            color: #1a2f25;
        }

        .course-card {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.08);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);
            margin-bottom: 1.75rem;
            overflow: hidden;
        }
        .course-card-header {
            padding: 1.25rem 1.5rem;
            background: #f7f9f7;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        .course-card-header h2 {
            margin: 0;
            font-size: 1.15rem;
            font-weight: 700;
            color: #1a2f25;
        }
        .course-card-header p {
            margin: 0;
            font-size: 0.82rem;
            color: #5e7166;
        }

        .grades-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }
        .grades-table th,
        .grades-table td {
            padding: 0.85rem 1.25rem;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
        }
        .grades-table th {
            color: #4a6354;
            background: #fbfdfc;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .grades-table tbody tr:last-child td {
            border-bottom: 0;
        }
    </style>

    <div style="margin-bottom: 1.5rem;">
        <h1 style="font-size: 1.75rem; font-weight: 700; color: #1a2f25; margin-bottom: 4px;">
            Rekap Nilai Perkuliahan
        </h1>
        <p style="color: #647a6e; font-size: 0.9rem;">
            Ringkasan seluruh tugas dan nilai evaluasi akademik Anda di KampusLMS.
        </p>
    </div>

    @php
        $totalAssignments = 0;
        $totalGraded = 0;
        $sumScore = 0;

        foreach ($courses as $c) {
            foreach ($c->assignments as $a) {
                $totalAssignments++;
                $sub = $a->submissions->first();
                if ($sub?->grade) {
                    $totalGraded++;
                    $sumScore += $sub->grade->score;
                }
            }
        }
        $avgScore = $totalGraded > 0 ? $sumScore / $totalGraded : null;
    @endphp

    <div class="grades-summary-grid">
        <div class="stat-card">
            <div class="label">Mata Kuliah Diikuti</div>
            <div class="value">{{ $courses->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Total Tugas Tersedia</div>
            <div class="value">{{ $totalAssignments }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Tugas Telah Dinilai</div>
            <div class="value" style="color: #047857;">{{ $totalGraded }}</div>
        </div>
        <div class="stat-card">
            <div class="label">Rata-rata Nilai Saya</div>
            <div class="value" style="color: #1b3528;">
                {{ $avgScore !== null ? number_format($avgScore, 2) : '-' }}
            </div>
        </div>
    </div>

    @if ($courses->isEmpty())
        <div style="padding: 2.5rem; text-align: center; background: #ffffff; border-radius: 16px; border: 1px solid rgba(45, 74, 62, 0.1); color: #647a6e;">
            Anda belum terdaftar pada mata kuliah apa pun.
        </div>
    @else
        @foreach ($courses as $course)
            <section class="course-card">
                <header class="course-card-header">
                    <div>
                        <h2>{{ $course->name }} ({{ $course->code }})</h2>
                        <p>Dosen Pengampu: {{ $course->lecturer?->name ?? '-' }}</p>
                    </div>
                    <a href="{{ route('mahasiswa.courses.show', $course) }}" style="color: #2d4a3e; font-size: 0.82rem; font-weight: 600; text-decoration: none;">
                        Buka Kelas →
                    </a>
                </header>

                @if ($course->assignments->isEmpty())
                    <div style="padding: 1.5rem; color: #647a6e; font-size: 0.88rem; text-align: center;">
                        Belum ada tugas pada mata kuliah ini.
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table class="grades-table">
                            <thead>
                                <tr>
                                    <th>Nama Tugas</th>
                                    <th>Batas Waktu</th>
                                    <th>Status Tugas</th>
                                    <th>Nilai</th>
                                    <th>Catatan / Feedback</th>
                                    <th style="text-align: right;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($course->assignments as $asg)
                                    @php
                                        $sub = $asg->submissions->first();
                                        $grd = $sub?->grade;
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong style="color: #1a2f25;">{{ $asg->title }}</strong>
                                            <div style="font-size: 0.78rem; color: #647a6e;">Maksimal: {{ $asg->max_score }}</div>
                                        </td>
                                        <td>{{ $asg->due_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                                        <td>
                                            @if (! $sub)
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; background: #f1f5f9; color: #64748b; font-size: 0.76rem; font-weight: 600;">
                                                    Belum Mengumpulkan
                                                </span>
                                            @elseif ($sub->is_late)
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; background: #fff7ed; color: #b45309; font-size: 0.76rem; font-weight: 600;">
                                                    Terlambat
                                                </span>
                                            @else
                                                <span style="display: inline-block; padding: 2px 8px; border-radius: 999px; background: #ecfdf5; color: #047857; font-size: 0.76rem; font-weight: 600;">
                                                    Tepat Waktu
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($grd)
                                                <span style="display: inline-block; padding: 3px 8px; border-radius: 6px; background: #ecfdf5; color: #047857; font-weight: 700; font-size: 0.88rem;">
                                                    {{ $grd->score }} / {{ $asg->max_score }}
                                                </span>
                                            @elseif ($sub)
                                                <span style="color: #92400e; font-size: 0.82rem; font-style: italic;">
                                                    Menunggu penilaian
                                                </span>
                                            @else
                                                <span style="color: #94a3b8;">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($grd?->feedback)
                                                <div style="font-size: 0.82rem; color: #374151; max-width: 250px; word-break: break-word;">
                                                    "{{ $grd->feedback }}"
                                                </div>
                                            @elseif ($grd)
                                                <span style="color: #94a3b8; font-size: 0.8rem;">Tanpa catatan</span>
                                            @else
                                                <span style="color: #94a3b8;">-</span>
                                            @endif
                                        </td>
                                        <td style="text-align: right;">
                                            <a href="{{ route('mahasiswa.assignments.show', $asg) }}" style="display: inline-block; padding: 4px 10px; border-radius: 6px; border: 1px solid rgba(45, 74, 62, 0.2); background: #ffffff; color: #2d4a3e; font-size: 0.8rem; font-weight: 600; text-decoration: none;">
                                                Detail Tugas
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    @endif
</x-layout>
