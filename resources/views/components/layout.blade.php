<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="KampusLMS - Sistem Manajemen Pembelajaran Kelompok A07">
    <title>{{ $title ?? 'KampusLMS' }} — Kelompok A07</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --color-canvas: #f5f7f5;
            --color-text-main: #1f2d26;
            --color-text-muted: #5e7166;
            --color-sage-deep: #243c32;
            --color-sage-primary: #2d4a3e;
            --color-sage-mid: #416454;
            --color-sage-soft: #e8f1ec;
            --color-border-subtle: rgba(45, 74, 62, 0.1);
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: var(--color-canvas);
            color: var(--color-text-main);
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
        }

        /* NAVBAR SAGE HARMONY */
        .navbar {
            background: linear-gradient(135deg, #1f352c 0%, #294438 60%, #345446 100%);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 14px rgba(27, 46, 38, 0.12);
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.25rem;
            text-decoration: none;
            letter-spacing: -0.4px;
            display: inline-flex;
            align-items: center;
        }

        .navbar-brand span {
            background: rgba(232, 241, 236, 0.18);
            color: #dbe7e0;
            padding: 2px 9px;
            border-radius: 999px;
            margin-left: 8px;
            font-size: 0.72rem;
            font-weight: 600;
            letter-spacing: 0.3px;
            border: 1px solid rgba(255, 255, 255, 0.15);
        }

        .navbar-links {
            display: flex;
            gap: 0.4rem;
        }

        .navbar-links a {
            color: rgba(232, 241, 236, 0.88);
            text-decoration: none;
            padding: 7px 16px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .navbar-links a:hover {
            background: rgba(255, 255, 255, 0.14);
            color: #ffffff;
            transform: translateY(-1px);
        }

        .admin-menu { position: relative; }
        .admin-menu summary { color: rgba(232, 241, 236, 0.88); padding: 7px 16px; border-radius: 8px; cursor: pointer; font-size: 0.9rem; font-weight: 500; list-style: none; }
        .admin-menu summary::-webkit-details-marker { display: none; }
        .admin-menu summary:hover, .admin-menu[open] summary { background: rgba(255, 255, 255, 0.14); color: #ffffff; }
        .admin-menu-items { position: absolute; right: 0; top: 2.4rem; min-width: 180px; padding: .45rem; background: #ffffff; border: 1px solid var(--color-border-subtle); border-radius: 10px; box-shadow: 0 10px 24px rgba(27, 46, 38, .16); }
        .admin-menu-items a { display: block; color: var(--color-text-main); padding: .65rem .75rem; }
        .admin-menu-items a:hover { background: var(--color-sage-soft); color: var(--color-sage-deep); transform: none; }

        /* MAIN CONTENT */
        .main-content {
            max-width: 1120px;
            margin: 0 auto;
            padding: 2.25rem 1.5rem;
        }

        /* PAGE HEADER & TITLES */
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

        /* BUTTONS & ACTIONS */
        .btn-add,
        .btn-primary {
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
            border: none;
            cursor: pointer;
        }

        .btn-add:hover,
        .btn-primary:hover {
            background: #22382f;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(45, 74, 62, 0.28);
            color: #ffffff;
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
            cursor: pointer;
            font-family: inherit;
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
        }

        .btn-delete:hover {
            background: #991b1b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* TABLE & CARDS */
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

        .empty-state {
            text-align: center;
            padding: 3.5rem 1.5rem;
            color: #647a6e;
        }

        /* BADGES & TAGS */
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
            display: inline-flex;
            align-items: center;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            background: #f5f8f6;
            color: #3c5447;
            border: 1px solid rgba(45, 74, 62, 0.12);
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

        .nim-nip {
            color: #495e52;
        }

        /* ALERTS */
        .alert-success {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 18px;
            border-radius: 12px;
            background: #e8f5ec;
            border: 1px solid #b7e0c4;
            color: #1b5330;
            font-size: 0.88rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(27, 83, 48, 0.06);
        }

        /* PAGINATION */
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

        /* FORM STYLES */
        .form-card {
            background: #ffffff;
            border-radius: 18px;
            box-shadow: 0 4px 20px -2px rgba(35, 62, 49, 0.05), 0 2px 6px -1px rgba(35, 62, 49, 0.02);
            border: 1px solid rgba(45, 74, 62, 0.08);
            padding: 2.25rem;
            max-width: 800px;
        }

        .form-group {
            margin-bottom: 1.35rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 7px;
            font-size: 0.88rem;
            font-weight: 600;
            color: #21332a;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid rgba(45, 74, 62, 0.2);
            border-radius: 10px;
            font-family: inherit;
            font-size: 0.92rem;
            background-color: #fbfcfa;
            color: #1a2f25;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: #2d4a3e;
            background-color: #ffffff;
            box-shadow: 0 0 0 3px rgba(45, 74, 62, 0.12);
        }

        .form-group textarea {
            min-height: 110px;
            resize: vertical;
        }

        .error-message {
            margin-top: 5px;
            color: #dc2626;
            font-size: 0.82rem;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 2rem;
            padding-top: 1.25rem;
            border-top: 1px solid rgba(45, 74, 62, 0.08);
        }

        .btn-save,
        .btn-cancel {
            padding: 10px 20px;
            border-radius: 10px;
            font-size: 0.92rem;
            font-weight: 600;
            text-decoration: none;
            border: none;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }

        .btn-save {
            background: #2d4a3e;
            color: #ffffff;
            box-shadow: 0 4px 14px rgba(45, 74, 62, 0.2);
        }

        .btn-save:hover {
            background: #22382f;
            transform: translateY(-1px);
            box-shadow: 0 6px 18px rgba(45, 74, 62, 0.28);
            color: #ffffff;
        }

        .btn-cancel {
            background: #eef5f1;
            color: #2d4a3e;
            border: 1px solid rgba(45, 74, 62, 0.15);
        }

        .btn-cancel:hover {
            background: #e2ece6;
            color: #1a2f25;
        }

        /* Default Laravel SVG pagination constrain */
        nav svg {
            width: 1.25rem !important;
            height: 1.25rem !important;
            display: inline-block;
        }

        /* RESPONSIVE */
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

        /* FOOTER */
        .footer {
            text-align: center;
            padding: 2rem 1.5rem;
            color: var(--color-text-muted);
            font-size: 0.85rem;
            border-top: 1px solid var(--color-border-subtle);
            margin-top: 3.5rem;
        }
    </style>
</head>
<body>

    <nav class="navbar">
        <a href="{{ route('dashboard') }}" class="navbar-brand">
            KampusLMS <span>A07</span>
        </a>
        <div class="navbar-links">
            <a href="{{ route('dashboard') }}">Dashboard</a>
            <a href="{{ route('courses.index') }}">Mata Kuliah</a>
            <details class="admin-menu">
                <summary>Mode Admin</summary>
                <div class="admin-menu-items">
                    <a href="{{ route('users.index') }}">Semua Pengguna</a>
                    <a href="{{ route('users.create') }}">Tambah Pengguna</a>
                </div>
            </details>
            <a href="{{ route('tentang') }}">Tentang</a>
        </div>
    </nav>

    <main class="main-content">
        {{ $slot }}
    </main>

    <footer class="footer">
        &copy; {{ date('Y') }} KampusLMS — Kelompok A07 | Pemrograman Web
    </footer>

</body>
</html>