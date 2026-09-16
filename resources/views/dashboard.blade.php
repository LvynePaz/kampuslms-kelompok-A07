<x-layout title="Dashboard">

    <style>
        :root {
            --sage-900: #1a2f25;
            --sage-800: #233e31;
            --sage-700: #2d4a3e;
            --sage-600: #3c6152;
            --sage-500: #4e7765;
            --sage-400: #719987;
            --sage-300: #9cbcae;
            --sage-200: #c8ddd3;
            --sage-100: #e5eee8;
            --sage-50:  #f2f7f4;
            --amber-accent: #c4873f;
            --amber-soft: #fcf4ea;
            --surface-card: #ffffff;
            --text-title: #182820;
            --text-body: #475a50;
            --text-muted: #6f8377;
            --border-card: rgba(45, 74, 62, 0.08);
            --border-card-hover: rgba(45, 74, 62, 0.2);
            --shadow-card: 0 4px 20px -2px rgba(35, 62, 49, 0.04), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            --shadow-hover: 0 12px 28px -4px rgba(35, 62, 49, 0.09), 0 4px 10px -2px rgba(35, 62, 49, 0.04);
            --ease-out: cubic-bezier(0.16, 1, 0.3, 1);
        }

        /* HERO BANNER (Natural Organic Sage Atmosphere) */
        .hero-banner {
            position: relative;
            background: linear-gradient(135deg, #20382d 0%, #29473a 50%, #36594a 100%);
            border-radius: 20px;
            padding: 3rem 2.5rem;
            margin-bottom: 2rem;
            color: #ffffff;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 30px -4px rgba(26, 47, 37, 0.2);
        }

        .hero-banner::before {
            content: '';
            position: absolute;
            top: -30%;
            right: -10%;
            width: 380px;
            height: 380px;
            background: radial-gradient(circle, rgba(123, 178, 151, 0.25) 0%, rgba(41, 71, 58, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-banner::after {
            content: '';
            position: absolute;
            bottom: -35%;
            left: 20%;
            width: 260px;
            height: 260px;
            background: radial-gradient(circle, rgba(200, 221, 211, 0.15) 0%, rgba(41, 71, 58, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .hero-content {
            position: relative;
            z-index: 1;
            max-width: 720px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(232, 241, 236, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #dbebe2;
            padding: 5px 14px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 500;
            letter-spacing: 0.2px;
            margin-bottom: 1.25rem;
            backdrop-filter: blur(8px);
        }

        .hero-badge svg {
            width: 14px;
            height: 14px;
            color: #a8d5be;
        }

        .hero-title {
            font-size: 2.2rem;
            font-weight: 700;
            line-height: 1.2;
            letter-spacing: -0.6px;
            color: #ffffff;
            margin-bottom: 0.75rem;
        }

        .hero-desc {
            font-size: 1.05rem;
            line-height: 1.6;
            color: rgba(235, 243, 238, 0.9);
            margin-bottom: 1.75rem;
            font-weight: 400;
        }

        .hero-meta {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
            color: #cfe1d7;
        }

        .hero-meta-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .hero-meta-item svg {
            width: 15px;
            height: 15px;
            opacity: 0.85;
        }

        .pulse-dot {
            width: 8px;
            height: 8px;
            background-color: #6ee7b7;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 3px rgba(110, 231, 183, 0.25);
            animation: pulse-aura 2.4s infinite;
        }

        @keyframes pulse-aura {
            0%   { box-shadow: 0 0 0 0 rgba(110, 231, 183, 0.5); }
            70%  { box-shadow: 0 0 0 8px rgba(110, 231, 183, 0); }
            100% { box-shadow: 0 0 0 0 rgba(110, 231, 183, 0); }
        }

        /* BENTO STATS GRID */
        .bento-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .bento-card {
            background: var(--surface-card);
            border-radius: 16px;
            padding: 1.5rem;
            border: 1px solid var(--border-card);
            box-shadow: var(--shadow-card);
            transition: transform 0.25s var(--ease-out), box-shadow 0.25s var(--ease-out), border-color 0.25s var(--ease-out);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
        }

        .bento-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-hover);
            border-color: var(--border-card-hover);
        }

        .bento-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .icon-bubble {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: transform 0.2s;
        }

        .bento-card:hover .icon-bubble {
            transform: scale(1.05);
        }

        .icon-bubble.sage {
            background: var(--sage-100);
            color: var(--sage-700);
        }

        .icon-bubble.moss {
            background: #e3ede7;
            color: #244b38;
        }

        .icon-bubble.earth {
            background: #edece5;
            color: #55523e;
        }

        .icon-bubble.amber {
            background: var(--amber-soft);
            color: var(--amber-accent);
        }

        .icon-bubble svg {
            width: 22px;
            height: 22px;
        }

        .card-pill-tag {
            font-size: 0.75rem;
            font-weight: 600;
            padding: 3px 9px;
            border-radius: 999px;
            background: var(--sage-50);
            color: var(--sage-600);
            border: 1px solid rgba(45, 74, 62, 0.08);
        }

        .card-pill-tag.amber {
            background: var(--amber-soft);
            color: var(--amber-accent);
            border-color: rgba(196, 135, 63, 0.2);
        }

        .bento-body {
            margin-bottom: 1.25rem;
        }

        .stat-value {
            font-size: 2.35rem;
            font-weight: 700;
            line-height: 1.1;
            color: var(--text-title);
            letter-spacing: -0.8px;
            margin-bottom: 0.25rem;
        }

        .stat-name {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--text-body);
            margin-bottom: 0.25rem;
        }

        .stat-subtext {
            font-size: 0.82rem;
            color: var(--text-muted);
            line-height: 1.4;
        }

        .bento-footer {
            border-top: 1px solid rgba(45, 74, 62, 0.06);
            padding-top: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-action-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: var(--sage-600);
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s, transform 0.15s;
        }

        .footer-action-link:hover {
            color: var(--sage-800);
            transform: translateX(2px);
        }

        .footer-action-link svg {
            width: 14px;
            height: 14px;
        }

        .avatar-group {
            display: inline-flex;
            align-items: center;
        }

        .avatar-pill {
            width: 26px;
            height: 26px;
            border-radius: 50%;
            background: var(--sage-200);
            color: var(--sage-800);
            border: 2px solid #ffffff;
            font-size: 0.65rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-left: -6px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
        }

        .avatar-pill:first-child {
            margin-left: 0;
            background: #d1dfd6;
        }

        .micro-progress-wrapper {
            width: 100%;
        }

        .progress-track {
            height: 6px;
            background: var(--sage-100);
            border-radius: 999px;
            overflow: hidden;
            margin-bottom: 5px;
        }

        .progress-bar-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--sage-500), var(--amber-accent));
            border-radius: 999px;
            transition: width 0.4s ease;
        }

        .progress-caption {
            font-size: 0.75rem;
            color: var(--text-muted);
            display: flex;
            justify-content: space-between;
        }

        /* HIGHLIGHTS & QUICK ACTIONS */
        .highlights-row {
            margin-bottom: 1.5rem;
        }

        .panel-card {
            background: var(--surface-card);
            border-radius: 18px;
            padding: 1.75rem;
            border: 1px solid var(--border-card);
            box-shadow: var(--shadow-card);
        }

        .panel-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 1.25rem;
        }

        .panel-title-icon {
            color: var(--sage-600);
            display: flex;
        }

        .panel-title-icon svg {
            width: 20px;
            height: 20px;
        }

        .panel-title {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--text-title);
            letter-spacing: -0.3px;
        }

        .quick-actions-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 0.85rem;
        }

        .btn-action-primary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--sage-700);
            color: #ffffff;
            padding: 0.95rem 1.25rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: background 0.2s var(--ease-out), transform 0.2s var(--ease-out), box-shadow 0.2s;
            box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);
        }

        .btn-action-primary:hover {
            background: var(--sage-800);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(45, 74, 62, 0.28);
        }

        .btn-action-primary .btn-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-action-secondary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--sage-50);
            color: var(--sage-800);
            border: 1px solid rgba(45, 74, 62, 0.12);
            padding: 0.85rem 1.25rem;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.92rem;
            transition: all 0.2s var(--ease-out);
        }

        .btn-action-secondary:hover {
            background: var(--sage-100);
            border-color: rgba(45, 74, 62, 0.25);
            transform: translateY(-1px);
        }

        .btn-action-secondary .btn-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>

    <!-- 1. HERO BANNER SAGE -->
    <div class="hero-banner">
        <div class="hero-content">
            <div class="hero-badge">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
                <span>Semester Ganjil 2026/2027 · Institut Teknologi Kalimantan</span>
            </div>

            <h1 class="hero-title">Selamat Datang di KampusLMS</h1>

            <p class="hero-desc">
                Portal pembelajaran terpadu untuk perkuliahan Pemrograman Web. Dikelola secara kolaboratif oleh <strong>Kelompok A07</strong> untuk pengalaman belajar yang jernih, tenang, dan terstruktur.
            </p>

            <div class="hero-meta">
                <div class="hero-meta-item">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                    <span>{{ date('d F Y') }}</span>
                </div>
                <span>•</span>
                <div class="hero-meta-item">
                    <span class="pulse-dot"></span>
                    <span>Sistem Perkuliahan Aktif</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. BENTO METRICS GRID -->
    <div class="bento-grid">

        <!-- Card 1: Mata Kuliah -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="icon-bubble sage">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                    </svg>
                </div>
                <span class="card-pill-tag">Kurikulum</span>
            </div>
            <div class="bento-body">
                <div class="stat-value">{{ $courseCount }}</div>
                <div class="stat-name">Mata Kuliah</div>
                <p class="stat-caption">Mata kuliah aktif terdaftar pada katalog sistem.</p>
            </div>
            <div class="bento-footer">
                <a href="{{ route('courses.index') }}" class="footer-action-link">
                    <span>Lihat Daftar</span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Card 2: Pengguna -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="icon-bubble moss">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <span class="card-pill-tag">Civitas</span>
            </div>
            <div class="bento-body">
                <div class="stat-value">{{ $userCount }}</div>
                <div class="stat-name">Total Pengguna</div>
                <p class="stat-caption">Mahasiswa dan dosen yang terintegrasi di portal.</p>
            </div>
            <div class="bento-footer">
                <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: 500;">
                    Akses Terverifikasi
                </span>
            </div>
        </div>

        <!-- Card 3: Tim Pengembang -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="icon-bubble earth">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path>
                    </svg>
                </div>
                <span class="card-pill-tag">Tim A07</span>
            </div>
            <div class="bento-body">
                <div class="stat-value">4</div>
                <div class="stat-name">Anggota Tim</div>
                <p class="stat-caption">Mahasiswa pengembang proyek KampusLMS.</p>
            </div>
            <div class="bento-footer">
                <div class="avatar-group" title="Tim Kelompok A07">
                    <span class="avatar-pill">NK</span>
                    <span class="avatar-pill">NH</span>
                    <span class="avatar-pill">PA</span>
                    <span class="avatar-pill">04</span>
                </div>
                <a href="{{ route('tentang') }}" class="footer-action-link">
                    <span>Detail Tim</span>
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Card 4: Progres Minggu -->
        <div class="bento-card">
            <div class="bento-header">
                <div class="icon-bubble amber">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
                <span class="card-pill-tag amber">Milestone</span>
            </div>
            <div class="bento-body">
                <div class="stat-value">W03</div>
                <div class="stat-name">Minggu Berjalan</div>
                <p class="stat-caption">Fase penyempurnaan UI/UX dan basis data.</p>
            </div>
            <div class="bento-footer" style="padding-top: 0.6rem;">
                <div class="micro-progress-wrapper">
                    <div class="progress-track">
                        <div class="progress-bar-fill" style="width: 25%;"></div>
                    </div>
                    <div class="progress-caption">
                        <span>Minggu ke-3</span>
                        <span>Tahap Aktif</span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- 3. HIGHLIGHTS & QUICK ACTIONS -->
    <div class="highlights-row">

        <!-- Quick Actions Panel -->
        <div class="panel-card">
            <div class="panel-title-row">
                <div class="panel-title-icon">
                    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"></path>
                    </svg>
                </div>
                <h3 class="panel-title">Aksi Cepat & Navigasi</h3>
            </div>

            <div class="quick-actions-list">
                <a href="{{ route('courses.index') }}" class="btn-action-primary">
                    <div class="btn-content">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <span>Jelajahi Semua Mata Kuliah</span>
                    </div>
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                    </svg>
                </a>

                <a href="{{ route('tentang') }}" class="btn-action-secondary">
                    <div class="btn-content">
                        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Tentang Tim Kelompok A07</span>
                    </div>
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

</x-layout>
