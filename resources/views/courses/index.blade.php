<x-layout title="Daftar Mata Kuliah">

    <style>
        .page-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
        }

        .page-header h1 {
            font-size: 1.6rem;
            font-weight: 700;
            color: #0f172a;
        }

        .btn-add {
            display: inline-block;
            padding: 9px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            background: #1d4ed8;
            color: white;
            transition: all 0.2s;
        }

        .btn-add:hover {
            background: #1e40af;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 8px rgba(0,0,0,0.07);
            border: 1px solid #e2e8f0;
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
        }

        th {
            padding: 12px 16px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.95rem;
            color: #334155;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: #f8fafc;
        }

        .badge-sks {
            background: #dbeafe;
            color: #1d4ed8;
            font-size: 0.8rem;
            font-weight: 600;
            padding: 2px 10px;
            border-radius: 20px;
        }

        .link-name {
            color: #1d4ed8;
            font-weight: 600;
            text-decoration: none;
        }

        .link-name:hover {
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
            display: inline-block;
            padding: 5px 12px;
            border-radius: 7px;
            font-size: 0.82rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-detail {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
        }

        .btn-detail:hover {
            background: #1d4ed8;
            color: white;
        }

        .btn-edit {
            background: #f0fdf4;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }

        .btn-edit:hover {
            background: #15803d;
            color: white;
        }

        .btn-delete {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
            cursor: pointer;
        }

        .btn-delete:hover {
            background: #dc2626;
            color: white;
        }

        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #94a3b8;
        }

        .alert-success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
        }
    </style>

    <div class="page-header">
        <h1>Daftar Mata Kuliah</h1>

        <a href="{{ route('courses.create') }}" class="btn-add">
            + Tambah Mata Kuliah
        </a>
    </div>

    @if (session('success'))
        <div class="alert-success">
            {{ session('success') }}
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
                            <code>{{ $course['code'] }}</code>
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

                        <td>
                            {{ $course['lecturer'] }}
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
                            Belum ada mata kuliah terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</x-layout>
```
