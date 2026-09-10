<x-layout title="Dashboard">

    <style>
        .hero {
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            color: white;
            border-radius: 16px;
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 4px 24px rgba(30, 64, 175, 0.2);
        }
        .hero h1 { font-size: 2rem; font-weight: 700; margin-bottom: 0.5rem; }
        .hero p  { font-size: 1.05rem; opacity: 0.85; }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            text-align: center;
            box-shadow: 0 1px 6px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
            transition: transform 0.2s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-number { font-size: 2.2rem; font-weight: 700; color: #1e40af; }
        .stat-label  { color: #64748b; font-size: 0.9rem; margin-top: 4px; }

        .quick-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: white;
            color: #1e40af;
            border: 2px solid #bfdbfe;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.95rem;
            margin-top: 1rem;
            transition: all 0.2s;
        }
        .quick-link:hover {
            background: #1e40af;
            color: white;
            border-color: #1e40af;
        }
    </style>

    <div class="hero">
        <h1>Selamat Datang di KampusLMS</h1>
        <p>Sistem Manajemen Pembelajaran — Institut Teknologi Kalimantan · Kelompok A07</p>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-number">4</div>
            <div class="stat-label">Mata Kuliah</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">4</div>
            <div class="stat-label">Anggota Tim</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">W02</div>
            <div class="stat-label">Progres Minggu</div>
        </div>
    </div>

    <a href="{{ route('courses.index') }}" class="quick-link">
        Lihat Daftar Mata Kuliah
    </a>

</x-layout>
