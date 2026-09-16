<x-layout title="Tentang Kelompok A07">

    <style>
        .about-hero {
            text-align: center;
            padding: 2.5rem 1.5rem;
            margin-bottom: 2.5rem;
            background: linear-gradient(135deg, #20382d 0%, #29473a 50%, #36594a 100%);
            border-radius: 20px;
            color: #ffffff;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 8px 30px -4px rgba(26, 47, 37, 0.16);
        }

        .about-hero::before {
            content: '';
            position: absolute;
            top: -40%;
            right: -10%;
            width: 320px;
            height: 320px;
            background: radial-gradient(circle, rgba(123, 178, 151, 0.22) 0%, rgba(41, 71, 58, 0) 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .about-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(232, 241, 236, 0.14);
            border: 1px solid rgba(255, 255, 255, 0.18);
            color: #dbebe2;
            padding: 4px 14px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 500;
            margin-bottom: 1rem;
            backdrop-filter: blur(8px);
        }

        .about-hero h1 {
            font-size: 2rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            color: #ffffff;
            margin-bottom: 0.5rem;
        }

        .about-hero p {
            color: rgba(232, 241, 236, 0.9);
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(230px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .member-card {
            background: #ffffff;
            border-radius: 18px;
            border: 1px solid rgba(45, 74, 62, 0.1);
            padding: 2rem 1.5rem;
            text-align: center;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.04), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.25s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.25s;
            position: relative;
        }

        .member-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px -4px rgba(35, 62, 49, 0.12);
            border-color: rgba(45, 74, 62, 0.25);
        }

        .avatar-container {
            width: 72px;
            height: 72px;
            background: linear-gradient(135deg, #243c32 0%, #3a5c4d 100%);
            color: #e8f1ec;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.35rem;
            font-weight: 700;
            margin: 0 auto 1.25rem;
            border: 3px solid #eef4f0;
            box-shadow: 0 4px 12px rgba(36, 60, 50, 0.15);
            transition: transform 0.2s;
        }

        .member-card:hover .avatar-container {
            transform: scale(1.05);
        }

        .member-name {
            font-weight: 700;
            color: #1a2f25;
            font-size: 1.05rem;
            margin-bottom: 6px;
            letter-spacing: -0.2px;
        }

        .member-nim-badge {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #3c6152;
            background: #eef5f1;
            padding: 3px 12px;
            border-radius: 999px;
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            border: 1px solid rgba(45, 74, 62, 0.1);
            margin-bottom: 0.85rem;
        }

        .member-role {
            font-size: 0.82rem;
            color: #647a6e;
            line-height: 1.4;
        }

        .about-footer-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid rgba(45, 74, 62, 0.08);
            padding: 1.5rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(35, 62, 49, 0.03);
            flex-wrap: wrap;
            gap: 1rem;
        }

        .footer-card-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .footer-card-icon {
            width: 40px;
            height: 40px;
            background: #e8f1ec;
            color: #2d4a3e;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .footer-card-text h4 {
            font-size: 0.95rem;
            font-weight: 700;
            color: #1a2f25;
            margin-bottom: 2px;
        }

        .footer-card-text p {
            font-size: 0.83rem;
            color: #647a6e;
        }

        .btn-back-dash {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #2d4a3e;
            color: #ffffff;
            padding: 8px 18px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.88rem;
            transition: background 0.2s, transform 0.2s;
        }

        .btn-back-dash:hover {
            background: #22382f;
            transform: translateY(-1px);
        }
    </style>

    <div class="about-hero">
        <div class="about-hero-badge">
            <span>🌿 Profil Kelompok A07</span>
        </div>
        <h1>Kelompok A07 — KampusLMS</h1>
        <p>Institut Teknologi Kalimantan · Program Studi Sistem Informasi / Informatika · Mata Kuliah Pemrograman Web</p>
    </div>

    <div class="team-grid">
        <div class="member-card">
            <div class="avatar-container">PA</div>
            <div class="member-name">Patra Ananda</div>
            <span class="member-nim-badge">10241061</span>
            <p class="member-role">Pengembang KampusLMS</p>
        </div>

        <div class="member-card">
            <div class="avatar-container">NH</div>
            <div class="member-name">Nabil Hafidz Mubarok</div>
            <span class="member-nim-badge">10241055</span>
            <p class="member-role">Pengembang KampusLMS</p>
        </div>

        <div class="member-card">
            <div class="avatar-container">ND</div>
            <div class="member-name">Nur Diah Indah Claryza</div>
            <span class="member-nim-badge">10241059</span>
            <p class="member-role">Pengembang KampusLMS</p>
        </div>

        <div class="member-card">
            <div class="avatar-container">NK</div>
            <div class="member-name">Nova Kartika Candra</div>
            <span class="member-nim-badge">10241057</span>
            <p class="member-role">Pengembang KampusLMS</p>
        </div>
    </div>

    <div class="about-footer-card">
        <div class="footer-card-info">
            <div class="footer-card-icon">
                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="footer-card-text">
                <h4>Proyek Pengembangan Web KampusLMS</h4>
                <p>Dibangun secara kolaboratif menggunakan Laravel 11 dengan standar arsitektur bersih dan terstruktur.</p>
            </div>
        </div>
        <a href="{{ route('dashboard') }}" class="btn-back-dash">
            <svg width="15" height="15" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            <span>Kembali ke Dashboard</span>
        </a>
    </div>

</x-layout>
