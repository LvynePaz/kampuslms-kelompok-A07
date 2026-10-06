<x-layout title="Detail Tugas">

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

        .assignment-card {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            overflow: hidden;
        }

        .assignment-header {
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

        .assignment-header h1 {
            font-size: 1.85rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: -0.3px;
        }

        .assignment-body {
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
            font-size: 1.1rem;
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

        .instruction-box {
            font-size: 0.98rem;
            color: #21332a;
            line-height: 1.7;
            background: #f7f9f7;
            padding: 1.25rem 1.5rem;
            border-radius: 12px;
            border: 1px solid rgba(45, 74, 62, 0.08);
            white-space: pre-line;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: capitalize;
        }
        .status-published {
            background: #e8f5e9;
            color: #2e7d32;
        }
        .status-draft {
            background: #fff8e1;
            color: #f57f17;
        }
    </style>

    <a href="{{ url('/' . auth()->user()->role . '/courses/' . $course->id) }}" class="back-link">
        ← Kembali ke Mata Kuliah: {{ $course->name }}
    </a>

    <div class="assignment-card">
        <div class="assignment-header">
            <div class="course-badge">{{ $course->code }} — {{ $course->name }}</div>
            <h1>{{ $assignment->title }}</h1>
        </div>

        <div class="assignment-body">
            <div class="info-grid">
                <div class="info-item">
                    <div class="label">Batas Waktu (Deadline)</div>
                    <div class="value">{{ $assignment->due_at ? $assignment->due_at->translatedFormat('d M Y, H:i') : '-' }}</div>
                </div>
                <div class="info-item">
                    <div class="label">Nilai Maksimal</div>
                    <div class="value">{{ $assignment->max_score }} Poin</div>
                </div>
                <div class="info-item">
                    <div class="label">Status</div>
                    <div class="value">
                        <span class="status-badge status-{{ $assignment->status }}">
                            {{ $assignment->status }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="section-title">Instruksi Pengerjaan</div>
            <div class="instruction-box">{{ $assignment->instructions ?: 'Belum ada instruksi tambahan untuk tugas ini.' }}</div>
        </div>
    </div>

</x-layout>
