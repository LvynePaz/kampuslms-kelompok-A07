<x-layout title="Detail Mata Kuliah">

    <style>
        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #475569;
            text-decoration: none;
            font-size: 0.9rem;
            margin-bottom: 1.5rem;
            font-weight: 500;
            transition: color 0.2s;
        }
        .back-link:hover { color: #1e40af; }

        .detail-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        .detail-header {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            padding: 2rem 2.5rem;
        }

        .detail-header .code-badge {
            display: inline-block;
            background: rgba(255,255,255,0.2);
            padding: 3px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 0.75rem;
            letter-spacing: 0.5px;
        }

        .detail-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .detail-header .meta {
            display: flex;
            gap: 1.5rem;
            font-size: 0.9rem;
            opacity: 0.9;
            flex-wrap: wrap;
        }

        .detail-body {
            padding: 2rem 2.5rem;
        }

        .section-title {
            font-size: 1rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .description-text {
            font-size: 1rem;
            color: #334155;
            line-height: 1.7;
            background: #f8fafc;
            padding: 1.25rem;
            border-radius: 10px;
            border: 1px solid #e2e8f0;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }

        .info-item .label {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 4px;
        }

        .info-item .value {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
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
                <span> {{ $course['lecturer'] }}</span>
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
                    <div class="value">{{ $course['lecturer'] }}</div>
                </div>
            </div>

            <div class="section-title">Deskripsi Mata Kuliah</div>
            {{-- {{ }} aman dari XSS — Laravel akan escape karakter berbahaya --}}
            <div class="description-text">{{ $course['description'] }}</div>
        </div>
    </div>

</x-layout>
