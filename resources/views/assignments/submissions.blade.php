<x-layout title="Daftar Pengumpulan">
    <style>
        .monitoring-card {
            background: #ffffff;
            border: 1px solid rgba(45, 74, 62, 0.08);
            border-radius: 16px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05);
            overflow: hidden;
        }
        .monitoring-header {
            padding: 1.5rem 1.75rem;
            background: #f7f9f7;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
        }
        .monitoring-header h1 {
            margin: 0 0 0.35rem;
            color: #1a2f25;
            font-size: 1.4rem;
            font-weight: 700;
        }
        .monitoring-header p {
            margin: 0;
            color: #5e7166;
            font-size: 0.9rem;
        }
        .monitoring-table-wrap {
            overflow-x: auto;
        }
        .monitoring-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }
        .monitoring-table th,
        .monitoring-table td {
            padding: 0.9rem 1.25rem;
            border-bottom: 1px solid rgba(45, 74, 62, 0.08);
        }
        .monitoring-table th {
            color: #4a6354;
            background: #fbfdfc;
            font-size: 0.76rem;
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }
        .monitoring-table td {
            color: #21332a;
            font-size: 0.9rem;
        }
        .monitoring-table tbody tr:last-child td {
            border-bottom: 0;
        }
        .submission-status {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 700;
            white-space: nowrap;
        }
        .status-submitted {
            color: #047857;
            background: #ecfdf5;
        }
        .status-late {
            color: #b45309;
            background: #fff7ed;
        }
        .status-missing {
            color: #64748b;
            background: #f1f5f9;
        }
        .monitoring-empty {
            padding: 1.5rem;
            color: #647a6e;
            text-align: center;
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.75rem;">
        <a href="{{ route('dosen.assignments.show', $assignment) }}" style="color: #416454; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
            Kembali ke Detail Tugas
        </a>
        <a href="{{ route('dosen.courses.grades', $course) }}" style="display: inline-flex; align-items: center; padding: 7px 14px; background: #2d4a3e; color: #ffffff; text-decoration: none; border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
            Rekap Nilai Mata Kuliah
        </a>
    </div>

    <section class="monitoring-card">
        <header class="monitoring-header">
            <h1>Daftar Pengumpulan</h1>
            <p>{{ $course->name }} ({{ $course->code }}) &bull; {{ $assignment->title }}</p>
            <p style="margin-top: 0.35rem;">
                Tenggat: {{ $assignment->due_at?->translatedFormat('d M Y, H:i') ?? '-' }}
                &bull; Nilai Maksimal: {{ $assignment->max_score }}
                &bull; {{ $students->count() }} mahasiswa terdaftar
            </p>
        </header>

        @if ($students->isEmpty())
            <div class="monitoring-empty">Belum ada mahasiswa terdaftar di mata kuliah ini.</div>
        @else
            <div class="monitoring-table-wrap">
                <table class="monitoring-table">
                    <thead>
                        <tr>
                            <th>Mahasiswa</th>
                            <th>Status Pengumpulan</th>
                            <th>Waktu Pengumpulan</th>
                            <th>Berkas Tugas</th>
                            <th>Nilai & Feedback</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php
                                $submission = $student->submissions->first();
                                $grade = $submission?->grade;
                            @endphp
                            <tr>
                                <td>
                                    <strong>{{ $student->name }}</strong>
                                    @if ($student->nim_nip)
                                        <div style="margin-top: 0.2rem; color: #647a6e; font-size: 0.82rem;">{{ $student->nim_nip }}</div>
                                    @endif
                                </td>
                                <td>
                                    @if (! $submission)
                                        <span class="submission-status status-missing">Belum mengumpulkan</span>
                                    @elseif ($submission->is_late)
                                        <span class="submission-status status-late">Terlambat</span>
                                    @else
                                        <span class="submission-status status-submitted">Sudah mengumpulkan</span>
                                    @endif
                                </td>
                                <td>{{ $submission?->submitted_at?->translatedFormat('d M Y, H:i') ?? '-' }}</td>
                                <td>
                                    @if ($submission)
                                        <div style="display: flex; flex-direction: column; gap: 3px;">
                                            <a href="{{ route('submissions.download', $submission) }}" target="_blank" rel="noopener noreferrer" style="color: #2d4a3e; font-weight: 600; text-decoration: underline;">
                                                {{ $submission->original_name }}
                                            </a>
                                            <span style="font-size: 0.78rem; color: #647a6e;">
                                                {{ \Illuminate\Support\Number::fileSize($submission->file_size) }}
                                            </span>
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.85rem;">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($submission)
                                        <div>
                                            @if ($grade)
                                                <div style="display: inline-block; padding: 3px 8px; background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 6px; color: #065f46; font-size: 0.82rem; font-weight: 700;">
                                                    Nilai: {{ $grade->score }} / {{ $assignment->max_score }}
                                                </div>
                                                @if ($grade->feedback)
                                                    <div style="margin-top: 4px; font-size: 0.78rem; color: #374151; max-width: 220px; word-break: break-word;">
                                                        <em>"{{ $grade->feedback }}"</em>
                                                    </div>
                                                @endif
                                            @else
                                                <span style="display: inline-block; padding: 2px 7px; background: #fef3c7; color: #92400e; border-radius: 5px; font-size: 0.78rem; font-weight: 600;">
                                                    Belum dinilai
                                                </span>
                                            @endif

                                            <details style="margin-top: 6px; position: relative;">
                                                <summary style="cursor: pointer; color: #2d4a3e; font-size: 0.8rem; font-weight: 600;">
                                                    {{ $grade ? 'Ubah Nilai / Feedback' : '+ Beri Nilai' }}
                                                </summary>
                                                <div style="margin-top: 6px; padding: 10px; background: #ffffff; border: 1px solid rgba(45, 74, 62, 0.18); border-radius: 8px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); min-width: 220px;">
                                                    <form action="{{ route('dosen.submissions.grades.store', $submission) }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="submission_id" value="{{ $submission->id }}">
                                                        <div style="margin-bottom: 6px;">
                                                            <label style="display: block; font-size: 0.74rem; font-weight: 700; color: #2d4a3e; margin-bottom: 2px;">
                                                                Nilai (0 - {{ $assignment->max_score }}):
                                                            </label>
                                                            <input type="number" step="0.01" min="0" max="{{ $assignment->max_score }}" name="score" value="{{ old('submission_id') == $submission->id ? old('score') : $grade?->score }}" required style="width: 100%; padding: 4px 8px; border: 1px solid #c8d6ce; border-radius: 6px; font-size: 0.85rem; font-family: inherit;">
                                                        </div>
                                                        <div style="margin-bottom: 8px;">
                                                            <label style="display: block; font-size: 0.74rem; font-weight: 700; color: #2d4a3e; margin-bottom: 2px;">
                                                                Catatan Feedback:
                                                            </label>
                                                            <textarea name="feedback" rows="2" placeholder="Catatan untuk mahasiswa..." style="width: 100%; padding: 4px 8px; border: 1px solid #c8d6ce; border-radius: 6px; font-size: 0.8rem; font-family: inherit;">{{ old('submission_id') == $submission->id ? old('feedback') : $grade?->feedback }}</textarea>
                                                        </div>
                                                        <button type="submit" style="width: 100%; padding: 6px; background: #2d4a3e; color: #ffffff; border: none; border-radius: 6px; font-size: 0.8rem; font-weight: 600; cursor: pointer; font-family: inherit;">
                                                            {{ $grade ? 'Simpan Perubahan' : 'Kirim Nilai' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </details>
                                        </div>
                                    @else
                                        <span style="color: #94a3b8; font-size: 0.82rem;">-</span>
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
