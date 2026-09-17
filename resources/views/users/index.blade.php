<x-layout title="Pengguna">

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