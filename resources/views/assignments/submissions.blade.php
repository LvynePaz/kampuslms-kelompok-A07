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

    <div style="margin-bottom: 1.25rem;">
        <a href="{{ route('dosen.assignments.show', $assignment) }}" style="color: #416454; text-decoration: none; font-size: 0.9rem; font-weight: 600;">
            ← Kembali ke Detail Tugas
        </a>
    </div>

    <section class="monitoring-card">
        <header class="monitoring-header">
            <h1>Daftar Pengumpulan</h1>
            <p>{{ $course->name }} ({{ $course->code }}) &bull; {{ $assignment->title }}</p>
            <p style="margin-top: 0.35rem;">
                Tenggat: {{ $assignment->due_at?->translatedFormat('d M Y, H:i') ?? '-' }}
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
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($students as $student)
                            @php($submission = $student->submissions->first())
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
                                            <a href="{{ route('submissions.download', $submission) }}" target="_blank" rel="noopener noreferrer" style="display: inline-flex; align-items: center; gap: 6px; color: #2d4a3e; font-weight: 600; text-decoration: none;" onmouseover="this.style.textDecoration='underline'" onmouseout="this.style.textDecoration='none'">
                                                <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                                </svg>
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
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>
</x-layout>
