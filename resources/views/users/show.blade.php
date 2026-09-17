<x-layout title="Detail Pengguna">
    <style>
        .user-detail { max-width: 820px; }
        .user-back { color: #5e7166; font-size: .82rem; text-decoration: none; }
        .user-back:hover { color: #2d4a3e; }
        .profile-card { background: #fff; border: 1px solid rgba(45,74,62,.1); border-radius: 14px; box-shadow: 0 8px 22px rgba(36,60,50,.05); margin-top: 1.25rem; overflow: hidden; }
        .profile-head { align-items: center; background: linear-gradient(135deg, #243c32, #416454); color: #fff; display: flex; gap: 1rem; padding: 1.8rem 2rem; }
        .profile-avatar { align-items: center; background: #dceadd; border-radius: 14px; color: #243c32; display: grid; font-size: 1.5rem; font-weight: 800; height: 60px; justify-content: center; width: 60px; }
        .profile-head h1 { font-size: 1.4rem; }
        .profile-head p { color: #dbe7e0; font-size: .82rem; margin-top: .3rem; }
        .profile-body { padding: 1.5rem 2rem; }
        .profile-grid { display: grid; gap: 1rem; grid-template-columns: repeat(2, 1fr); }
        .profile-item { background: #f5f8f6; border: 1px solid #e7eee9; border-radius: 9px; padding: .9rem 1rem; }
        .profile-item small { color: #5e7166; display: block; font-size: .68rem; letter-spacing: .06em; margin-bottom: .35rem; text-transform: uppercase; }
        .profile-item strong { color: #243c32; font-size: .88rem; }
        .profile-actions { border-top: 1px solid #e7eee9; display: flex; gap: .7rem; margin-top: 1.5rem; padding-top: 1.25rem; }
        .profile-actions a { border-radius: 8px; font-size: .8rem; font-weight: 700; padding: .65rem .9rem; text-decoration: none; }
        .profile-edit { background: #2d4a3e; color: #fff; }
        .profile-close { background: #e8f1ec; color: #2d4a3e; }
        @media (max-width: 600px) { .profile-head, .profile-body { padding: 1.25rem; } .profile-grid { grid-template-columns: 1fr; } }
    </style>

    <div class="user-detail">
        <a href="{{ route('users.index') }}" class="user-back">&lt;- Kembali ke daftar pengguna</a>
        <section class="profile-card">
            <div class="profile-head"><div class="profile-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div><div><h1>{{ $user->name }}</h1><p>Profil pengguna KampusLMS</p></div></div>
            <div class="profile-body">
                <div class="profile-grid">
                    <div class="profile-item"><small>Email</small><strong>{{ $user->email }}</strong></div>
                    <div class="profile-item"><small>Role akses</small><strong>{{ ucfirst($user->role) }}</strong></div>
                    <div class="profile-item"><small>NIM / NIP</small><strong>{{ $user->nim_nip ?: 'Belum diisi' }}</strong></div>
                    <div class="profile-item"><small>Terdaftar sejak</small><strong>{{ $user->created_at?->format('d M Y') ?: '-' }}</strong></div>
                </div>
                <div class="profile-actions"><a href="{{ route('users.edit', $user) }}" class="profile-edit">Edit pengguna</a><a href="{{ route('users.index') }}" class="profile-close">Tutup detail</a></div>
            </div>
        </section>
    </div>
</x-layout>
