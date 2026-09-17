<x-layout title="Pengguna">

    <style>
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .header-title-area h1 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1a2f25;
            letter-spacing: -0.4px;
            margin-bottom: 4px;
        }

        .header-title-area p {
            color: #647a6e;
            font-size: 0.9rem;
        }

        .btn-add {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            background: #2d4a3e;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-add:hover {
            background: #22382f;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(45, 74, 62, 0.28);
        }

        .table-card {
            background: #ffffff;
            border-radius: 16px;
            box-shadow:
                0 4px 20px -2px rgba(35, 62, 49, 0.04),
                0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            overflow: hidden;
        }

        .table-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 18px;
            background: #ffffff;
            border-bottom: 1px solid #eef2ef;
        }

        .table-header h2 {
            font-size: 1rem;
            font-weight: 700;
            color: #243c32;
            margin: 0;
        }

        .table-header span {
            color: #647a6e;
            font-size: 0.82rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f4f7f5;
            border-bottom: 2px solid rgba(45, 74, 62, 0.1);
        }

        th {
            padding: 14px 18px;
            font-size: 0.78rem;
            font-weight: 700;
            color: #3c5447;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            text-align: left;
        }

        td {
            padding: 15px 18px;
            border-bottom: 1px solid #f0f4f1;
            font-size: 0.92rem;
            color: #21332a;
            vertical-align: middle;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #f9fbf9;
        }

        .user-name {
            color: #243c32;
            font-weight: 600;
        }

        .user-email {
            color: #647a6e;
            font-size: 0.8rem;
            margin-top: 3px;
        }

        .role-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .role-admin {
            background: #e3edf8;
            color: #28537d;
        }

        .role-dosen {
            background: #e4f2e8;
            color: #2d6b43;
        }

        .role-mahasiswa {
            background: #f8efd8;
            color: #876729;
        }

        .nim-nip {
            color: #495e52;
        }

        .action-buttons {
            display: flex;
            gap: 6px;
            align-items: center;
        }

        .btn-detail,
        .btn-edit,
        .btn-delete {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border: 1px solid transparent;
        }

        .btn-detail {
            background: #eef5f1;
            color: #2d4a3e;
            border-color: rgba(45, 74, 62, 0.18);
        }

        .btn-detail:hover {
            background: #2d4a3e;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-edit {
            background: #f7f9f2;
            color: #4b5e28;
            border-color: rgba(75, 94, 40, 0.2);
        }

        .btn-edit:hover {
            background: #4b5e28;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .btn-delete {
            background: #fdf2f2;
            color: #991b1b;
            border-color: #fecaca;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #991b1b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 3.5rem 1.5rem;
            color: #647a6e;
        }

        .pagination-wrapper {
            padding: 14px 18px;
            border-top: 1px solid #eef2ef;
        }

        .pagination-nav {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .pagination-info {
            font-size: 0.82rem;
            color: #647a6e;
        }

        .pagination-controls {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .page-arrow,
        .page-num {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
            height: 30px;
            padding: 0 6px;
            border-radius: 8px;
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            color: #3c5447;
            transition: all 0.2s ease;
        }

        .page-arrow {
            color: #2d4a3e;
            font-size: 1.1rem;
        }

        .page-arrow:hover,
        .page-num:hover {
            background: #eef5f1;
        }

        .page-arrow.disabled {
            color: #c3cec7;
            pointer-events: none;
        }

        .page-num.active {
            background: #2d4a3e;
            color: #ffffff;
        }

        @media (max-width: 700px) {
            .page-header {
                align-items: flex-start;
                flex-direction: column;
            }

            .table-card {
                overflow-x: auto;
            }

            table {
                min-width: 800px;
            }
        }
    </style>

    <div class="page-header">
        <div class="header-title-area">
            <h1>Pengguna</h1>
            <p>Kelola akun dan role pengguna KampusLMS dari satu tempat</p>
        </div>

        <a href="{{ route('users.create') }}" class="btn-add">
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
                                    href="{{ route('users.show', $user) }}"
                                    class="btn-detail"
                                >
                                    Lihat
                                </a>

                                <a
                                    href="{{ route('users.edit', $user) }}"
                                    class="btn-edit"
                                >
                                    Edit
                                </a>

                                <form
                                    action="{{ route('users.destroy', $user) }}"
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