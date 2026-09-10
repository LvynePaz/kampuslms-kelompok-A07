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

        body {
            font-family: 'Inter', sans-serif;
            background: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
        }

        /* NAVBAR */
        .navbar {
            background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%);
            padding: 0 2rem;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 12px rgba(30, 64, 175, 0.3);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-brand {
            color: white;
            font-weight: 700;
            font-size: 1.25rem;
            text-decoration: none;
            letter-spacing: -0.3px;
        }

        .navbar-brand span {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 6px;
            margin-left: 6px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .navbar-links {
            display: flex;
            gap: 0.5rem;
        }

        .navbar-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 6px 14px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
        }

        .navbar-links a:hover {
            background: rgba(255,255,255,0.15);
            color: white;
        }

        /* MAIN CONTENT */
        .main-content {
            max-width: 1100px;
            margin: 0 auto;
            padding: 2rem 1.5rem;
        }

        /* FOOTER */
        .footer {
            text-align: center;
            padding: 1.5rem;
            color: #94a3b8;
            font-size: 0.85rem;
            border-top: 1px solid #e2e8f0;
            margin-top: 3rem;
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