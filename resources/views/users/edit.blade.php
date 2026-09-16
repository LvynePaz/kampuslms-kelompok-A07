<x-layout title="Edit Pengguna">
    <style>
        .user-form-wrap { max-width: 900px; }
        .user-form-back { color: #5e7166; font-size: .82rem; text-decoration: none; }
        .user-form-heading { margin: 1.2rem 0 1.5rem; }
        .user-form-kicker { color: #416454; font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .user-form-heading h1 { color: #243c32; font-size: 2rem; letter-spacing: -.04em; margin-top: .4rem; }
        .user-form-heading p { color: #5e7166; margin-top: .45rem; }
        .user-form-card { background: #fff; border: 1px solid rgba(45,74,62,.1); border-radius: 14px; box-shadow: 0 8px 22px rgba(36,60,50,.05); overflow: hidden; }
        .user-form-card-head { border-bottom: 1px solid #e7eee9; padding: 1.2rem 1.5rem; }
        .user-form-card-head h2 { color: #243c32; font-size: 1rem; }
        .user-form-card-head p { color: #5e7166; font-size: .8rem; margin-top: .3rem; }
        .user-form-body { padding: 1.5rem; }
        .user-form-actions { border-top: 1px solid #e7eee9; display: flex; justify-content: flex-end; gap: .7rem; margin-top: 1.5rem; padding-top: 1.25rem; }
        .user-form-button { border: 0; border-radius: 8px; cursor: pointer; font: inherit; font-size: .82rem; font-weight: 700; padding: .7rem 1rem; text-decoration: none; }
        .user-form-button.primary { background: #2d4a3e; color: #fff; }
        .user-form-button.secondary { background: #e8f1ec; color: #2d4a3e; }
    </style>

    <div class="user-form-wrap">
        <a href="{{ route('users.show', $user) }}" class="user-form-back">&lt;- Kembali ke detail pengguna</a>
        <div class="user-form-heading"><div class="user-form-kicker">Mode admin / pengguna</div><h1>Edit pengguna</h1><p>Perbarui informasi akun dan role {{ $user->name }}.</p></div>
        <form action="{{ route('users.update', $user) }}" method="POST" class="user-form-card">
            @csrf @method('PUT')
            <div class="user-form-card-head"><h2>Informasi akun</h2><p>Password boleh dikosongkan jika tidak ingin mengubahnya.</p></div>
            <div class="user-form-body">
                @include('users._form', ['user' => $user])
                <div class="user-form-actions"><a href="{{ route('users.show', $user) }}" class="user-form-button secondary">Batal</a><button type="submit" class="user-form-button primary">Simpan perubahan</button></div>
            </div>
        </form>
    </div>
</x-layout>
