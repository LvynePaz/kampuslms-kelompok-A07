<x-layout title="Pengguna">

    <div class="page-header">
        <div class="header-title-area">
            <h1>Pengguna</h1>
            <p>Kelola akun dan role pengguna KampusLMS dari satu tempat</p>
        </div>

        <a href="{{ route('admin.users.create') }}" class="btn-add">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4v16m8-8H4">
                </path>
            </svg>
            <span>Tambah Pengguna</span>
        </a>
    </div>

    @if (session('status'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0z">
                </path>
            </svg>

            <span>{{ session('status') }}</span>
        </div>
    @endif

    {{-- Form pencarian & filter role — state di query string, bukan session --}}
    <form method="GET" action="{{ route('admin.users.index') }}" style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari nama, email, atau NIM/NIP…"
            style="flex:1; min-width:220px; padding:9px 13px; border:1px solid rgba(45,74,62,0.2); border-radius:9px; font-size:0.9rem; background:#fbfcfa;"
        >
        <select name="role" style="padding:9px 13px; border:1px solid rgba(45,74,62,0.2); border-radius:9px; font-size:0.9rem; background:#fbfcfa;">
            <option value="">Semua Role</option>
            @foreach (['admin', 'dosen', 'mahasiswa'] as $r)
                <option value="{{ $r }}" @selected(request('role') === $r)>{{ ucfirst($r) }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:9px 18px; background:#2d4a3e; color:#fff; border:none; border-radius:9px; font-size:0.9rem; font-weight:600; cursor:pointer;">Cari</button>
        @if (request('q') || request('role'))
            <a href="{{ route('admin.users.index') }}" style="padding:9px 14px; background:#eef5f1; color:#2d4a3e; border:1px solid rgba(45,74,62,0.15); border-radius:9px; font-size:0.9rem; text-decoration:none;">Reset</a>
        @endif
    </form>

    <div class="table-card">

        <div class="table-header">
            <h2>Daftar Pengguna</h2>
            <span>{{ $users->total() }} akun</span>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Pengguna</th>
                    <th>Role</th>
                    <th>NIM / NIP</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($users as $user)
                    <tr>
                        <td>
                            <div class="user-name">
                                {{ $user->name }}
                            </div>

                            <div class="user-email">
                                {{ $user->email }}
                            </div>
                        </td>

                        <td>
                            <span class="role-badge role-{{ $user->role }}">
                                {{ $user->role }}
                            </span>
                        </td>

                        <td class="nim-nip">
                            {{ $user->nim_nip ?: '-' }}
                        </td>

                        <td>
                            <div class="action-buttons">

                                <a
                                    href="{{ route('admin.users.show', $user) }}"
                                    class="btn-detail"
                                >
                                    Lihat
                                </a>

                                <a
                                    href="{{ route('admin.users.edit', $user) }}"
                                    class="btn-edit"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('admin.users.destroy', $user) }}"
                                    method="POST"
                                    onsubmit="return confirm('Hapus pengguna ini?')"
                                    style="display: inline;"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button
                                        type="submit"
                                        class="btn-delete"
                                    >
                                        Hapus
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="4" class="empty-state">
                            Belum ada pengguna terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="pagination-wrapper">
            @if ($users->hasPages())
                <nav class="pagination-nav">
                    <span class="pagination-info">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} results
                    </span>

                    <div class="pagination-controls">
                        @if ($users->onFirstPage())
                            <span class="page-arrow disabled">&lsaquo;</span>
                        @else
                            <a href="{{ $users->previousPageUrl() }}" class="page-arrow">&lsaquo;</a>
                        @endif

                        @foreach ($users->getUrlRange(1, $users->lastPage()) as $page => $url)
                            @if ($page == $users->currentPage())
                                <span class="page-num active">{{ $page }}</span>
                            @else
                                <a href="{{ $url }}" class="page-num">{{ $page }}</a>
                            @endif
                        @endforeach

                        @if ($users->hasMorePages())
                            <a href="{{ $users->nextPageUrl() }}" class="page-arrow">&rsaquo;</a>
                        @else
                            <span class="page-arrow disabled">&rsaquo;</span>
                        @endif
                    </div>
                </nav>
            @endif
        </div>

    </div>

</x-layout>