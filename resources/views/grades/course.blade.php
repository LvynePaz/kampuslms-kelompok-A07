<x-layout title="Rekap Nilai — {{ $course->name }}">
    <style>
        .recap-card {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.08);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);
            overflow: hidden;
        }
        .recap-header {
            padding: 1.5rem 1.75rem;
            background: #f7f9f7;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
        }
        .recap-header h1 {
            margin: 0 0 0.35rem;
            color: #1a2f25;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .recap-header p {
            margin: 0;
            color: #5e7166;
            font-size: 0.9rem;
        }
        .recap-table-wrap {
            overflow-x: auto;
        }
        .recap-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 0.88rem;
        }
        .recap-table th,
        .recap-table td {
            padding: 0.85rem 1.15rem;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
        }
        .recap-table th {
            color: #4a6354;
            background: #fbfdfc;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            white-space: nowrap;
        }
        .recap-table tbody tr:hover {
            background: #fbfdfc;
        }
        .badge-score {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 6px;
            background: #ecfdf5;
            color: #047857;
            font-weight: 700;
            font-size: 0.82rem;
        }
        .badge-pending {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 5px;
            background: #fff7ed;
            color: #b45309;
            font-size: 0.75rem;
            font-weight: 600;
        }
        .badge-missing {
            color: #94a3b8;
            font-size: 0.85rem;
        }
    </style>

    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('dosen.courses.show', $course) }}" style="color: #416454; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
            ← Kembali ke Detail Mata Kuliah
        </a>
    </div>

    <section class="recap-card">
        <header class="recap-header">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; flex-wrap: wrap; gap: 0.75rem;">
                <div>
                    <h1>Rekap Nilai Perkuliahan</h1>
                    <p>{{ $course->name }} ({{ $course->code }}) &bull; Dosen: {{ $course->lecturer?->name ?? '-' }}</p>
                    <p style="margin-top: 0.35rem; font-size: 0.82rem; color: #647a6e;">
                        {{ $course->students->count() }} mahasiswa terdaftar &bull; {{ $course->assignments->count() }} tugas dibuat
                    </p>
                </div>
            </div>
        </header>

        @if ($course->assignments->isEmpty())
            <div style="padding: 2rem; text-align: center; color: #647a6e; font-size: 0.95rem;">
                Belum ada tugas perkuliahan yang dibuat untuk mata kuliah ini.
            </div>
        @elseif ($course->students->isEmpty())
            <div style="padding: 2rem; text-align: center; color: #647a6e; font-size: 0.95rem;">
                Belum ada mahasiswa yang terdaftar di kelas ini.
            </div>
        @else
            <div class="recap-table-wrap">
                <table class="recap-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;">No</th>
                            <th>Mahasiswa</th>
                            @foreach ($course->assignments as $asg)
                                <th style="text-align: center;">
                                    <div style="font-weight: 700; color: #1a2f25;">{{ $asg->title }}</div>
                                    <div style="font-size: 0.7rem; color: #647a6e; font-weight: normal;">Maks {{ $asg->max_score }}</div>
                                </th>
                            @endforeach
                            <th style="text-align: center; background: #f0f7f3; color: #1b4b36;">Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($course->students as $index => $student)
                            @php
                                $totalScore = 0;
                                $gradedCount = 0;
                            @endphp
                            <tr>
                                <td style="color: #647a6e;">{{ $index + 1 }}</td>
                                <td>
                                    <strong style="color: #1a2f25;">{{ $student->name }}</strong>
                                    @if ($student->nim_nip)
                                        <div style="color: #647a6e; font-size: 0.78rem;">{{ $student->nim_nip }}</div>
                                    @endif
                                </td>

                                @foreach ($course->assignments as $asg)
                                    @php
                                        $sub = $student->submissions->firstWhere('assignment_id', $asg->id);
                                        $grd = $sub?->grade;
                                        if ($grd) {
                                            $totalScore += $grd->score;
                                            $gradedCount++;
                                        }
                                    @endphp
                                    <td style="text-align: center;">
                                        @if (! $sub)
                                            <span class="badge-missing">-</span>
                                        @elseif (! $grd)
                                            <span class="badge-pending" title="Tugas terkumpul namun belum dinilai">Terkumpul</span>
                                        @else
                                            <span class="badge-score" title="{{ $grd->feedback ? 'Catatan: ' . $grd->feedback : 'Tanpa catatan' }}">
                                                {{ $grd->score }}
                                            </span>
                                        @endif
                                    </td>
                                @endforeach

                                <td style="text-align: center; font-weight: 700; background: #fafdfb;">
                                    @if ($gradedCount > 0)
                                        <span style="color: #15803d; font-size: 0.92rem;">
                                            {{ number_format($totalScore / $gradedCount, 2) }}
                                        </span>
                                    @else
                                        <span class="badge-missing">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layout>
