<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Masuk') &bull; {{ config('app.name', 'Al Ghifari Haramain') }}</title>

    <!-- Vite Assets -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: linear-gradient(135deg, #073B2E 0%, #0B4939 60%, #080B09 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: relative;
            overflow-x: hidden;
            padding: 1.5rem;
        }

        .auth-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.04;
            background-image: radial-gradient(#C9A45B 1px, transparent 1px), radial-gradient(#C9A45B 1px, #073B2E 1px);
            background-size: 36px 36px;
            background-position: 0 0, 18px 18px;
            pointer-events: none;
            z-index: 1;
        }

        .auth-card {
            background: rgba(255, 255, 255, 0.98);
            border-radius: 20px;
            box-shadow: 0 20px 45px rgba(0, 0, 0, 0.35), 0 0 0 1px rgba(201, 164, 91, 0.3);
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 2;
            overflow: hidden;
        }

        .auth-header {
            background: linear-gradient(135deg, #073B2E 0%, #0B4939 100%);
            padding: 2.25rem 2rem 1.75rem;
            text-align: center;
            position: relative;
        }

        .auth-logo-box {
            width: 82px;
            height: 82px;
            background: #F5F0E5;
            border-radius: 50%;
            margin: 0 auto 1rem auto;
            padding: 8px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.3), 0 0 0 2px #C9A45B;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }
    </style>
</head>
<body>
    <div class="auth-overlay"></div>

    <div class="auth-card">
        <div class="auth-header">
            <div class="auth-logo-box">
                <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Resmi Al Ghifari Haramain">
            </div>
            <h5 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif; letter-spacing: 0.5px;">
                AL GHIFARI HARAMAIN
            </h5>
            <small class="text-royal-gold fw-semibold" style="letter-spacing: 1.5px; font-size: 0.72rem;">
                UMROH &amp; HAJI SPECIALIST
            </small>
        </div>

        <div class="p-4 p-md-4">
            @yield('content')
        </div>
    </div>
    @stack('scripts')
</body>
</html>
