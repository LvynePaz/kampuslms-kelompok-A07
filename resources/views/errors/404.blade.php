<x-layout title="404 — Halaman Tidak Ditemukan">

    <style>
        .error-wrapper {
            text-align: center;
            padding: 5rem 2rem;
        }
        .error-code {
            font-size: 7rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            line-height: 1;
            margin-bottom: 1rem;
        }
        .error-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .error-desc {
            color: #64748b;
            font-size: 1rem;
            margin-bottom: 2rem;
        }
        .btn-home {
            display: inline-block;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            text-decoration: none;
            padding: 12px 28px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.3);
            transition: transform 0.2s;
        }
        .btn-home:hover { transform: translateY(-2px); }
    </style>

    <div class="error-wrapper">
        <div class="error-code">404</div>
        <div class="error-title">Halaman Tidak Ditemukan</div>
        <div class="error-desc">Maaf, halaman atau data yang Anda cari tidak ada.</div>
        <a href="{{ route('courses.index') }}" class="btn-home">
            Kembali ke Daftar Mata Kuliah
        </a>
    </div>

</x-layout>
