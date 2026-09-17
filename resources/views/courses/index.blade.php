<x-layout title="Daftar Mata Kuliah">

    <div class="page-header">
        <div class="header-title-area">
            <h1>Daftar Mata Kuliah</h1>
            <p>Kelola kurikulum dan mata kuliah aktif pada semester berjalan</p>
        </div>

        <a href="{{ route('courses.create') }}" class="btn-add">
            <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            <span>Tambah Mata Kuliah</span>
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <div class="table-card">
        <table>
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Mata Kuliah</th>
                    <th>SKS</th>
                    <th>Dosen Pengampu</th>
                    <th>Aksi</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($courses as $course)
                    <tr>
                        <td>
                            <span class="code-tag">{{ $course['code'] }}</span>
                        </td>

                        <td>
                            <a href="{{ route('courses.show', $course['id']) }}" class="link-name">
                                {{ $course['name'] }}
                            </a>
                        </td>

                        <td>
                            <span class="badge-sks">
                                {{ $course['sks'] }} SKS
                            </span>
                        </td>

                        <td style="color: #495e52;">
                            {{ $course->lecturer?->name ?? '-' }}
                        </td>

                        <td>
                            <div class="action-buttons">

                                <a href="{{ route('courses.show', $course['id']) }}" class="btn-detail">
                                    Lihat
                                </a>

                                <a href="{{ route('courses.edit', $course['id']) }}" class="btn-edit">
                                    Edit
                                </a>

                                <form
                                    action="{{ route('courses.destroy', $course['id']) }}"
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

                            </div>
                        </td>
                    </tr>

                @empty
                    <tr>
                        <td colspan="5" class="empty-state">
                            Belum ada mata kuliah terdaftar. Silakan tambahkan mata kuliah baru.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layout>
