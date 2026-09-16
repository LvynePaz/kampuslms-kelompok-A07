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

    {{-- Tombol Kembali menggunakan route() —  bukan hardcode '/courses' --}}
    <a href="{{ route('courses.index') }}" class="back-link">
        ← Kembali ke Daftar Mata Kuliah
    </a>

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
            <div class="description-text">{{ $course['description'] }}</div>
        </div>
    </div>

</x-layout>
