<x-layout title="Daftar Mata Kuliah">

    <div class="page-header">
        <div class="header-title-area">
            <h1>Daftar Mata Kuliah</h1>
            <p>Kelola kurikulum dan mata kuliah aktif pada semester berjalan</p>
        </div>

        @if ($userRole === 'admin')
        <a href="{{ route($routePrefix . 'courses.create') }}" class="btn-add">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Mata Kuliah</span>
        </a>
        @endif
    </div>

    @if (session('success'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Form pencarian & filter status — state disimpan di query string, bukan session --}}
    <form method="GET" action="{{ route($routePrefix . 'courses.index') }}" style="display:flex; gap:0.75rem; margin-bottom:1.25rem; flex-wrap:wrap;">
        <input
            type="text"
            name="q"
            value="{{ request('q') }}"
            placeholder="Cari kode atau nama mata kuliah…"
            style="flex:1; min-width:220px; padding:9px 13px; border:1px solid rgba(45,74,62,0.2); border-radius:9px; font-size:0.9rem; background:#fbfcfa;"
        >
        <select name="status" style="padding:9px 13px; border:1px solid rgba(45,74,62,0.2); border-radius:9px; font-size:0.9rem; background:#fbfcfa;">
            <option value="">Semua Status</option>
            @foreach (['draft','active','archived'] as $s)
                <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
        <button type="submit" style="padding:9px 18px; background:#2d4a3e; color:#fff; border:none; border-radius:9px; font-size:0.9rem; font-weight:600; cursor:pointer;">Cari</button>
        @if (request('q') || request('status'))
            <a href="{{ route($routePrefix . 'courses.index') }}" style="padding:9px 14px; background:#eef5f1; color:#2d4a3e; border:1px solid rgba(45,74,62,0.15); border-radius:9px; font-size:0.9rem; text-decoration:none;">Reset</a>
        @endif
    </form>

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Status</th>
                    <th>Dosen Pengampu</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($courses as $course)
                    <tr>
                        <td>
                            <span class="code-tag">{{ $course->code }}</span>
                        </td>

                        <td>
                            <a href="{{ route($routePrefix . 'courses.show', $course) }}" class="link-name">
                                {{ $course->name }}
                            </a>
                        </td>

                        <td>
                            <span class="badge-sks">{{ $course->sks }} SKS</span>
                        </td>

                        <td>
                            @php
                                $statusClass = match($course->status) {
                                    'active'   => 'background:#d1fae5; color:#065f46;',
                                    'draft'    => 'background:#fef3c7; color:#92400e;',
                                    'archived' => 'background:#f3f4f6; color:#6b7280;',
                                    default    => '',
                                };
                            @endphp
                            <span style="padding:3px 10px; border-radius:20px; font-size:0.78rem; font-weight:600; {{ $statusClass }}">
                                {{ ucfirst($course->status) }}
                            </span>
                        </td>

                        <td style="color: #495e52;">
                            {{ $course->lecturer?->name ?? '-' }}
                        </td>

                        <td>
                            <div class="action-buttons">

                                <a href="{{ route($routePrefix . 'courses.show', $course) }}" class="btn-detail">
                                    Lihat
                                </a>

                                @if ($userRole === 'admin')
                                <a href="{{ route($routePrefix . 'courses.edit', $course) }}" class="btn-edit">
                                    Edit
                                </a>

                                <form
                                    action="{{ route($routePrefix . 'courses.destroy', $course) }}"
                                    method="POST"
                                    onsubmit="return confirm('Apakah kamu yakin ingin menghapus mata kuliah ini?')"
                                    style="display: inline;"
                                >
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" class="btn-delete">
                                        Hapus
                                    </button>
                                </form>
                                @endif

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="6" class="empty-state">
                            @if (request('q') || request('status'))
                                Tidak ada mata kuliah yang cocok dengan pencarian.
                                <a href="{{ route($routePrefix . 'courses.index') }}" style="color:#2d4a3e;">Reset filter</a>
                            @else
                                Belum ada mata kuliah terdaftar. Silakan tambahkan mata kuliah baru.
                            @endif
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination bawaan Laravel — query string dibawa otomatis via ->withQueryString() --}}
        <div class="pagination-wrapper">
            {{ $courses->links() }}
        </div>
    </div>

</x-layout>

