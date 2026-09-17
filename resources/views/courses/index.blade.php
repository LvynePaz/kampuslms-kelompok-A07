<x-layout title="Daftar Mata Kuliah">

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
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.04), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            overflow: hidden;
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

        .code-tag {
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
            background: #eef5f1;
            color: #2d4a3e;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.85rem;
            font-weight: 600;
            border: 1px solid rgba(45, 74, 62, 0.12);
        }

        .badge-sks {
            background: #e8f1ec;
            color: #244436;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 4px 10px;
            border-radius: 999px;
            border: 1px solid rgba(45, 74, 62, 0.12);
            display: inline-block;
        }

        .link-name {
            color: #243c32;
            font-weight: 600;
            text-decoration: none;
            transition: color 0.15s;
        }

        .link-name:hover {
            color: #3d6352;
            text-decoration: underline;
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

        .alert-success {
            background: #edf5f0;
            color: #1b3829;
            border: 1px solid rgba(45, 74, 62, 0.2);
            padding: 12px 18px;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>

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
