<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @production
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        @endproduction

        <title>{{ config('app.name', 'KampusLMS') }} — Masuk</title>
        <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%232d4a3e'><path d='M12 3L1 9l4 2.18v6L12 21l7-3.82v-6l2-1.09V17h2V9L12 3zm6.82 6L12 12.72 5.18 9 12 5.28 18.82 9zM17 15.99l-5 2.73-5-2.73v-3.72L12 15l5-2.73v3.72z'/></svg>">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased text-[#1f2d26]" style="background-color: #e9eff5; font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 0; min-height: 100vh;">
        <div style="min-height: 100vh; display: flex; flex-direction: column; justify-content: center; align-items: center; padding: 1.5rem 1rem; background: radial-gradient(circle at 15% 20%, rgba(32, 92, 72, 0.08) 0%, #e9eff5 60%, rgba(210, 225, 235, 0.8) 100%);">
            {{ $slot }}
        </div>
    </body>
</html>
