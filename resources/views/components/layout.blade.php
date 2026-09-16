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

        /* MAIN CONTENT */
        .main-content {
            max-width: 1120px;
            margin: 0 auto;
            padding: 2.25rem 1.5rem;
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