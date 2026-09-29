<x-layout title="403 — Akses Ditolak">
    <main style="max-width:640px;margin:5rem auto;padding:0 1.5rem;text-align:center">
        <div role="img" aria-label="Error" style="width:64px;height:64px;margin:0 auto 1rem;display:grid;place-items:center;border-radius:50%;background:#fff0ef;color:#b42318">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <circle cx="12" cy="12" r="9"></circle>
                <path stroke-linecap="round" d="M12 7v6m0 4h.01"></path>
            </svg>
        </div>
        <p style="margin:0;color:#b42318;font-size:0.9rem;font-weight:700">ERROR 403</p>
        <h1 style="margin:0.5rem 0;color:#202b27">Akses tidak tersedia</h1>
        <p style="color:#59645f;line-height:1.6">Anda tidak memiliki izin untuk membuka halaman ini. Silakan kembali dan gunakan menu yang tersedia untuk akun Anda.</p>
        <a href="{{ route('dashboard') }}" style="display:inline-block;margin-top:1rem;padding:0.7rem 1rem;background:#245a46;color:white;text-decoration:none;border-radius:6px">Kembali ke dashboard</a>
    </main>
</x-layout>