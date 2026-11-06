<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') &bull; {{ config('app.name', 'Al Ghifari Haramain') }}</title>

    <!-- Vite Assets (Bootstrap 5, Icons, Sass, JS, Chart.js) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-width: 270px;
            --brand-forest-green: #073B2E;
            --brand-deep-emerald: #0B4939;
            --brand-royal-gold: #C9A45B;
            --brand-light-gold: #E5CF96;
            --brand-obsidian: #080B09;
            --brand-warm-ivory: #F5F0E5;
        }

        body {
            background-color: #F8F9FA;
            font-family: 'Plus Jakarta Sans', sans-serif;
            color: #2D3748;
            overflow-x: hidden;
        }

        /* Sidebar Styling */
        #sidebar-wrapper {
            width: var(--sidebar-width);
            height: 100vh;
            background: linear-gradient(180deg, #073B2E 0%, #0B4939 60%, #080B09 100%);
            box-shadow: 4px 0 20px rgba(7, 59, 46, 0.15);
            transition: all 0.3s ease;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: thin;
            scrollbar-color: rgba(201, 164, 91, 0.4) transparent;
        }

        #sidebar-wrapper::-webkit-scrollbar {
            width: 4px;
        }

        #sidebar-wrapper::-webkit-scrollbar-track {
            background: transparent;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb {
            background: rgba(201, 164, 91, 0.4);
            border-radius: 4px;
        }

        #sidebar-wrapper::-webkit-scrollbar-thumb:hover {
            background: rgba(201, 164, 91, 0.7);
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem 1.2rem;
            border-bottom: 1px solid rgba(201, 164, 91, 0.2);
            text-align: center;
        }

        .sidebar-logo-box {
            width: 70px;
            height: 70px;
            background: #F5F0E5;
            border-radius: 50%;
            margin: 0 auto 0.75rem auto;
            padding: 6px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3), 0 0 0 2px #C9A45B;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo-box img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .sidebar-heading {
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #E5CF96;
            padding: 1rem 1.25rem 0.4rem;
            font-weight: 700;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0.5rem 0.75rem 2rem;
            margin: 0;
        }

        .sidebar-item {
            margin-bottom: 3px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.65rem 0.9rem;
            color: rgba(245, 240, 229, 0.82);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.88rem;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-link i {
            font-size: 1.15rem;
            color: #C9A45B;
            transition: transform 0.2s ease;
        }

        .sidebar-link:hover {
            color: #FFFFFF;
            background: rgba(201, 164, 91, 0.15);
            transform: translateX(4px);
        }

        .sidebar-link:hover i {
            transform: scale(1.15);
        }

        .sidebar-link.active {
            color: #073B2E;
            background: #C9A45B;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(201, 164, 91, 0.35);
        }

        .sidebar-link.active i {
            color: #073B2E;
        }

        /* Page Content Wrapper */
        #page-content-wrapper {
            margin-left: var(--sidebar-width);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            transition: margin-left 0.3s ease;
        }

        /* Top Navbar */
        .top-navbar {
            background: #FFFFFF;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            padding: 0.85rem 1.75rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.02);
        }

        /* Responsive */
        @media (max-width: 991.98px) {
            #sidebar-wrapper {
                margin-left: calc(-1 * var(--sidebar-width));
            }
            #sidebar-wrapper.toggled {
                margin-left: 0;
            }
            #page-content-wrapper {
                margin-left: 0;
            }
        }

        .card-custom {
            border: 1px solid rgba(0, 0, 0, 0.05);
            border-radius: 14px;
            box-shadow: 0 4px 14px rgba(7, 59, 46, 0.04);
            background: #FFFFFF;
        }

        .badge-subtle-success {
            background-color: #E8F5E9;
            color: #2E7D32;
        }

        .badge-subtle-warning {
            background-color: #FFF8E1;
            color: #B78103;
        }

        .badge-subtle-info {
            background-color: #E1F5FE;
            color: #0277BD;
        }

        .badge-subtle-danger {
            background-color: #FFEBEE;
            color: #C62828;
        }

        .table-custom thead th {
            background-color: #F8F9FA;
            color: #555E68;
            font-size: 0.78rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid rgba(7, 59, 46, 0.1);
            padding: 0.75rem 1rem;
        }

        .table-custom tbody td {
            padding: 0.85rem 1rem;
            vertical-align: middle;
            font-size: 0.88rem;
        }

        /* Pagination & Arrow Icon Sizing */
        nav[role="navigation"] svg,
        .pagination svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 1rem !important;
            max-height: 1rem !important;
            display: inline-block !important;
            vertical-align: middle !important;
        }

        .pagination {
            margin-bottom: 0;
            gap: 4px;
            flex-wrap: wrap;
        }

        .pagination .page-item .page-link {
            color: var(--brand-forest-green, #073B2E);
            border-radius: 8px;
            border: 1px solid #E2E8F0;
            padding: 0.35rem 0.75rem;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1.25;
            transition: all 0.2s ease;
        }

        .pagination .page-item .page-link:hover {
            background-color: #F5F0E5;
            color: #073B2E;
            border-color: #C9A45B;
        }

        .pagination .page-item.active .page-link {
            background-color: #073B2E;
            border-color: #073B2E;
            color: #FFFFFF;
            font-weight: 600;
        }

        .pagination .page-item.disabled .page-link {
            color: #A0AEC0;
            background-color: #F8F9FA;
            border-color: #E2E8F0;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Sidebar -->
    <div id="sidebar-wrapper">
        <div class="sidebar-brand">
            <div class="sidebar-logo-box">
                <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Al Ghifari Haramain">
            </div>
            <h6 class="text-white fw-bold mb-0" style="font-family: 'Outfit', sans-serif; letter-spacing: 0.5px;">
                AL GHIFARI HARAMAIN
            </h6>
            <small class="text-royal-gold fw-semibold" style="font-size: 0.65rem; letter-spacing: 1.5px;">
                UMROH &amp; HAJI SPECIALIST
            </small>
        </div>

        <ul class="sidebar-menu">
            <!-- 1. DASHBOARD -->
            @can('dashboard.view')
            <li class="sidebar-item">
                <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="bi bi-speedometer2"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            @endcan

            <!-- 2. JAMAAH -->
            @canany(['jamaah.view', 'jamaah.create'])
            <div class="sidebar-heading">Operasional Jamaah</div>
            @can('jamaah.view')
            <li class="sidebar-item">
                <a href="{{ route('jamaah.index') }}" class="sidebar-link {{ request()->routeIs('jamaah.*') && !request()->routeIs('jamaah.create') ? 'active' : '' }}">
                    <i class="bi bi-people"></i>
                    <span>Data Jamaah</span>
                </a>
            </li>
            @endcan
            @can('jamaah.create')
            <li class="sidebar-item">
                <a href="{{ route('jamaah.create') }}" class="sidebar-link {{ request()->routeIs('jamaah.create') ? 'active' : '' }}">
                    <i class="bi bi-person-plus"></i>
                    <span>Pendaftaran Baru</span>
                </a>
            </li>
            @endcan
            @can('jamaah.view')
            <li class="sidebar-item">
                <a href="{{ route('registrations.index') }}" class="sidebar-link {{ request()->routeIs('registrations.*') ? 'active' : '' }}">
                    <i class="bi bi-card-checklist"></i>
                    <span>Registrasi &amp; Paket</span>
                </a>
            </li>
            @endcan
            @endcanany

            <!-- 3. PAKET & KEBERANGKATAN -->
            @canany(['package.view', 'departure.view'])
            <div class="sidebar-heading">Paket &amp; Perjalanan</div>
            @can('package.view')
            <li class="sidebar-item">
                <a href="{{ route('packages.index') }}" class="sidebar-link {{ request()->routeIs('packages.*') ? 'active' : '' }}">
                    <i class="bi bi-box-seam"></i>
                    <span>Paket Haji &amp; Umrah</span>
                </a>
            </li>
            @endcan
            @can('departure.view')
            <li class="sidebar-item">
                <a href="{{ route('departures.index') }}" class="sidebar-link {{ request()->routeIs('departures.*') && !request()->routeIs('departures.calendar') ? 'active' : '' }}">
                    <i class="bi bi-airplane"></i>
                    <span>Jadwal &amp; Manifest</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('departures.calendar') }}" class="sidebar-link {{ request()->routeIs('departures.calendar') ? 'active' : '' }}">
                    <i class="bi bi-calendar3"></i>
                    <span>Kalender Keberangkatan</span>
                </a>
            </li>
            @endcan
            @endcanany

            <!-- 4. KEUANGAN -->
            @can('finance.view')
            <div class="sidebar-heading">Keuangan &amp; Transaksi</div>
            <li class="sidebar-item">
                <a href="{{ route('finance.summary') }}" class="sidebar-link {{ request()->routeIs('finance.summary') ? 'active' : '' }}">
                    <i class="bi bi-pie-chart"></i>
                    <span>Ringkasan Keuangan</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('finance.invoices.index') }}" class="sidebar-link {{ request()->routeIs('finance.invoices.*') ? 'active' : '' }}">
                    <i class="bi bi-receipt"></i>
                    <span>Tagihan (Invoices)</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('finance.payments.index') }}" class="sidebar-link {{ request()->routeIs('finance.payments.*') ? 'active' : '' }}">
                    <i class="bi bi-cash-coin"></i>
                    <span>Pembayaran &amp; Kwitansi</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('finance.expenses.index') }}" class="sidebar-link {{ request()->routeIs('finance.expenses.*') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i>
                    <span>Pengeluaran Operasional</span>
                </a>
            </li>
            <li class="sidebar-item">
                <a href="{{ route('finance.refunds.index') }}" class="sidebar-link {{ request()->routeIs('finance.refunds.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-counterclockwise"></i>
                    <span>Pengembalian (Refund)</span>
                </a>
            </li>
            @endcan

            <!-- 5. LAPORAN & KOMUNIKASI -->
            @canany(['report.view', 'whatsapp.view'])
            <div class="sidebar-heading">Laporan &amp; Layanan</div>
            @can('report.view')
            <li class="sidebar-item">
                <a href="{{ route('reports.index') }}" class="sidebar-link {{ request()->routeIs('reports.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-bar-graph"></i>
                    <span>Pusat Laporan</span>
                </a>
            </li>
            @endcan
            @can('whatsapp.view')
            <li class="sidebar-item">
                <a href="{{ route('whatsapp.index') }}" class="sidebar-link {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}">
                    <i class="bi bi-whatsapp"></i>
                    <span>WhatsApp Cloud API</span>
                </a>
            </li>
            @endcan
            @endcanany

            <!-- 6. SISTEM & USER -->
            @canany(['user.view', 'audit.view', 'setting.view'])
            <div class="sidebar-heading">Sistem &amp; Pengaturan</div>
            @can('user.view')
            <li class="sidebar-item">
                <a href="{{ route('users.index') }}" class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="bi bi-person-gear"></i>
                    <span>User &amp; Hak Akses</span>
                </a>
            </li>
            @endcan
            @can('audit.view')
            <li class="sidebar-item">
                <a href="{{ route('audit-logs.index') }}" class="sidebar-link {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                    <i class="bi bi-shield-check"></i>
                    <span>Audit Log Sistem</span>
                </a>
            </li>
            @endcan
            @can('setting.view')
            <li class="sidebar-item">
                <a href="{{ route('settings.index') }}" class="sidebar-link {{ request()->routeIs('settings.*') ? 'active' : '' }}">
                    <i class="bi bi-sliders"></i>
                    <span>Pengaturan Aplikasi</span>
                </a>
            </li>
            @endcan
            @endcanany
        </ul>
    </div>

    <!-- Page Content Wrapper -->
    <div id="page-content-wrapper">
        <!-- Top Navbar -->
        <nav class="top-navbar d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button class="btn btn-sm btn-light border d-lg-none" id="sidebar-toggle-btn">
                    <i class="bi bi-list fs-5"></i>
                </button>
                <div>
                    <h5 class="mb-0 fw-bold text-forest-green" style="font-family: 'Outfit', sans-serif;">
                        @yield('header_title', 'Al Ghifari Haramain Management System')
                    </h5>
                    <small class="text-muted d-none d-md-inline-block">
                        <i class="bi bi-geo-alt-fill text-royal-gold me-1"></i> Kantor Pusat &bull; {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                    </small>
                </div>
            </div>

            <div class="d-flex align-items-center gap-3">
                <!-- Notifications Bell -->
                @php
                    $unreadCount = \App\Models\Notification::where('is_read', false)->count();
                @endphp
                <div class="dropdown">
                    <a href="{{ route('notifications.index') }}" class="btn btn-light position-relative rounded-circle p-2" title="Notifikasi Sistem">
                        <i class="bi bi-bell fs-5 text-muted"></i>
                        @if($unreadCount > 0)
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 0.65rem;">
                                {{ $unreadCount > 9 ? '9+' : $unreadCount }}
                            </span>
                        @endif
                    </a>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-light d-flex align-items-center gap-2 border-0 px-2 py-1 rounded-pill" type="button" data-bs-toggle="dropdown">
                        <div class="rounded-circle bg-forest-green text-white fw-bold d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 0.9rem;">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <div class="text-start d-none d-md-block me-1">
                            <div class="fw-semibold text-dark small" style="line-height: 1.2;">{{ auth()->user()->name }}</div>
                            <small class="text-muted" style="font-size: 0.72rem;">{{ auth()->user()->roles->pluck('name')->first() ?? 'Staff' }}</small>
                        </div>
                        <i class="bi bi-chevron-down text-muted small"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li>
                            <div class="px-3 py-2 border-bottom">
                                <span class="d-block fw-semibold text-dark small">{{ auth()->user()->name }}</span>
                                <span class="d-block text-muted small">{{ auth()->user()->email }}</span>
                            </div>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 small" href="{{ route('profile.edit') }}">
                                <i class="bi bi-person me-2 text-royal-gold"></i> Profil Saya
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item py-2 small" href="{{ route('notifications.index') }}">
                                <i class="bi bi-bell me-2 text-royal-gold"></i> Notifikasi
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item py-2 small text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i> Keluar
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Toast Notifications Alert -->
        <div class="container-fluid px-4 pt-3">
            @if(session('toast_success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success"></i>
                    <div>{{ session('toast_success') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('toast_danger'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
                    <div>{{ session('toast_danger') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('toast_info'))
                <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-info-circle-fill fs-5 text-info"></i>
                    <div>{{ session('toast_info') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('toast_warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0 d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-circle-fill fs-5 text-warning"></i>
                    <div>{{ session('toast_warning') }}</div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <!-- Main Content Area -->
        <main class="container-fluid px-4 py-3 flex-grow-1">
            @yield('content')
        </main>

        <!-- Footer -->
        <footer class="bg-white border-top py-3 px-4 mt-auto">
            <div class="d-flex flex-wrap align-items-center justify-content-between text-muted small">
                <div>
                    &copy; {{ date('Y') }} <strong>Al Ghifari Haramain</strong> &bull; UMROH &amp; HAJI SPECIALIST
                </div>
                <div class="mt-1 mt-md-0">
                    Sistem Manajemen Operasional Terpadu &bull; Versi 1.0.0
                </div>
            </div>
        </footer>
    </div>

    <!-- Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const toggleBtn = document.getElementById('sidebar-toggle-btn');
            const sidebar = document.getElementById('sidebar-wrapper');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function () {
                    sidebar.classList.toggle('toggled');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
