<x-layout title="Tentang Kelompok A07">

    <style>
        .about-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }
        .about-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }
        .about-header p { color: #64748b; }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 1.25rem;
        }

        .member-card {
            background: white;
            border-radius: 14px;
            border: 1px solid #e2e8f0;
            padding: 1.75rem;
            text-align: center;
            box-shadow: 0 1px 6px rgba(0,0,0,0.06);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .member-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(30, 64, 175, 0.12);
        }

        .avatar {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, #1e40af, #3b82f6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin: 0 auto 1rem;
        }

        .member-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 1rem;
            margin-bottom: 4px;
        }

        .member-nim {
            font-size: 0.85rem;
            color: #94a3b8;
            font-family: monospace;
        }
    </style>

    <div class="about-header">
        <h1>Kelompok A07 — KampusLMS</h1>
        <p>Institut Teknologi Kalimantan · Mata Kuliah Pemrograman Web</p>
    </div>

    <div class="team-grid">
        <div class="member-card">
            <div class="avatar"></div>
            <div class="member-name">Patra Ananda</div>
            <div class="member-nim">10241061</div>
        </div>
        <div class="member-card">
            <div class="avatar"></div>
            <div class="member-name">Nabil Hafidz Mubarok</div>
            <div class="member-nim">10241055</div>
        </div>
        <div class="member-card">
            <div class="avatar"></div>
            <div class="member-name">Nur Diah Indah Claryza</div>
            <div class="member-nim">10241059</div>
        </div>
        <div class="member-card">
            <div class="avatar"></div>
            <div class="member-name">Nova Kartika Candra</div>
            <div class="member-nim">10241057</div>
        </div>
    </div>

</x-layout>
