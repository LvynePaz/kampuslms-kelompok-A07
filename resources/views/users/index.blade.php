<x-layout title="Pengguna">
    <style>
        .user-hero { display: flex; justify-content: space-between; align-items: end; gap: 1rem; margin-bottom: 1.5rem; }
        .user-kicker { color: #416454; font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .user-hero h1 { color: #243c32; font-size: 2rem; letter-spacing: -.04em; margin-top: .4rem; }
        .user-hero p { color: #5e7166; margin-top: .45rem; }
        .user-primary { background: #2d4a3e; color: white; border-radius: 8px; padding: .7rem 1rem; text-decoration: none; font-size: .82rem; font-weight: 700; }
        .user-primary:hover { background: #243c32; }
        .user-panel { background: #fff; border: 1px solid rgba(45,74,62,.1); border-radius: 14px; overflow: hidden; box-shadow: 0 8px 22px rgba(36,60,50,.05); }
        .user-panel-head { display: flex; justify-content: space-between; align-items: center; padding: 1.15rem 1.25rem; border-bottom: 1px solid #e7eee9; }
        .user-panel-head h2 { color: #243c32; font-size: 1rem; }
        .user-panel-head span { color: #5e7166; font-size: .78rem; }
        .user-table { width: 100%; border-collapse: collapse; }
        .user-table th { background: #f5f8f6; color: #5e7166; font-size: .68rem; letter-spacing: .08em; padding: .8rem 1.25rem; text-align: left; text-transform: uppercase; }
        .user-table td { border-top: 1px solid #edf2ee; color: #33493d; font-size: .86rem; padding: 1rem 1.25rem; }
        .user-name { color: #243c32; font-weight: 700; }
        .user-email { color: #5e7166; font-size: .76rem; margin-top: .2rem; }
        .role-badge { border-radius: 999px; display: inline-block; font-size: .7rem; font-weight: 700; padding: .3rem .55rem; text-transform: capitalize; }
        .role-admin { background: #e3edf8; color: #28537d; }
        .role-dosen { background: #e4f2e8; color: #2d6b43; }
        .role-mahasiswa { background: #f8efd8; color: #876729; }
        .user-actions { display: flex; gap: .7rem; align-items: center; }
        .user-actions a, .user-actions button { background: none; border: 0; color: #416454; cursor: pointer; font: inherit; font-size: .78rem; padding: 0; text-decoration: none; }
        .user-actions .danger { color: #a64b4b; }
        .user-empty { color: #5e7166; padding: 2.5rem 1rem; text-align: center; }
        .user-pagination { padding: 1rem 1.25rem; }
        .user-status { background: #e8f1ec; border: 1px solid #cfe1d5; border-radius: 8px; color: #2d6b43; margin-bottom: 1rem; padding: .75rem 1rem; font-size: .82rem; }
        @media (max-width: 700px) { .user-hero { align-items: start; flex-direction: column; } .user-panel { overflow-x: auto; } .user-table { min-width: 680px; } }
    </style>

    <div class="user-hero">
        <div>
            <div class="user-kicker">Mode admin / manajemen akses</div>
            <h1>Pengguna</h1>
            <p>Kelola akun dan role pengguna KampusLMS dari satu tempat.</p>
        </div>
        <a href="{{ route('users.create') }}" class="user-primary">+ Tambah pengguna</a>
    </div>

    @if (session('status'))
        <div class="user-status">{{ session('status') }}</div>
    @endif

    <section class="user-panel">
        <div class="user-panel-head"><h2>Daftar pengguna</h2><span>{{ $users->total() }} akun</span></div>
        <table class="user-table">
            <thead><tr><th>Pengguna</th><th>Role</th><th>NIM / NIP</th><th>Aksi</th></tr></thead>
            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td><div class="user-name">{{ $user->name }}</div><div class="user-email">{{ $user->email }}</div></td>
                        <td><span class="role-badge role-{{ $user->role }}">{{ $user->role }}</span></td>
                        <td>{{ $user->nim_nip ?: '-' }}</td>
                        <td><div class="user-actions"><a href="{{ route('users.show', $user) }}">Lihat</a><a href="{{ route('users.edit', $user) }}">Edit</a><form action="{{ route('users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')<button type="submit" class="danger">Hapus</button></form></div></td>
                    </tr>
                @empty
                    <tr><td colspan="4"><div class="user-empty">Belum ada pengguna terdaftar.</div></td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="user-pagination">{{ $users->links() }}</div>
    </section>
</x-layout>
