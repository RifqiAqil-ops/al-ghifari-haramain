<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Al Ghifari Haramain Management System') }} - Sistem Operasional Haji & Umrah</title>
    
    <!-- Vite Assets (Bootstrap 5, Icons, Brand SCSS & JS) -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <style>
        body {
            background: linear-gradient(135deg, #073B2E 0%, #0B4939 50%, #080B09 100%);
            min-height: 100vh;
            color: #F5F0E5;
            font-family: 'Plus Jakarta Sans', sans-serif;
            position: relative;
            overflow-x: hidden;
        }

        /* Subtle Islamic Geometric Motif Overlay */
        .islamic-pattern-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            opacity: 0.04;
            background-image: radial-gradient(#C9A45B 1px, transparent 1px), radial-gradient(#C9A45B 1px, #073B2E 1px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
            pointer-events: none;
            z-index: 1;
        }

        .main-container {
            position: relative;
            z-index: 2;
        }

        .gold-divider {
            height: 2px;
            background: linear-gradient(90deg, transparent, #C9A45B, #E5CF96, #C9A45B, transparent);
            margin: 1.5rem auto;
            max-width: 480px;
        }

        .brand-badge {
            display: inline-block;
            background: rgba(201, 164, 91, 0.15);
            border: 1px solid rgba(201, 164, 91, 0.4);
            color: #E5CF96;
            padding: 0.35rem 1.25rem;
            border-radius: 50px;
            font-size: 0.8rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(201, 164, 91, 0.2);
            border-radius: 16px;
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            border-color: rgba(201, 164, 91, 0.45);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.35);
            transform: translateY(-3px);
        }

        .status-indicator {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            display: inline-block;
            background-color: #28a745;
            box-shadow: 0 0 10px #28a745;
            animation: pulse-dot 2s infinite;
        }

        @keyframes pulse-dot {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 8px rgba(40, 167, 69, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(40, 167, 69, 0); }
        }

        .logo-container {
            width: 160px;
            height: 160px;
            margin: 0 auto 1.5rem auto;
            background: rgba(245, 240, 229, 0.98);
            border-radius: 50%;
            padding: 14px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4), 0 0 0 3px rgba(201, 164, 91, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-container img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .module-pill {
            background: rgba(7, 59, 46, 0.6);
            border: 1px solid rgba(201, 164, 91, 0.25);
            padding: 0.6rem 1rem;
            border-radius: 10px;
            font-size: 0.85rem;
            color: #F5F0E5;
            display: flex;
            align-items: center;
            gap: 0.6rem;
            transition: all 0.2s ease;
        }

        .module-pill:hover {
            background: rgba(11, 73, 57, 0.9);
            border-color: #C9A45B;
            color: #FFFFFF;
        }
    </style>
</head>
<body>
    <div class="islamic-pattern-overlay"></div>

    <div class="container py-5 main-container">
        <!-- Brand Header -->
        <div class="text-center mb-5">
            <div class="logo-container">
                <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Resmi Al Ghifari Haramain" id="brand-official-logo">
            </div>
            
            <span class="brand-badge mb-3">
                <i class="bi bi-shield-check me-1"></i> Sistem Manajemen Operasional Internal
            </span>

            <h1 class="display-5 fw-bold text-white mt-2 mb-1" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">
                AL GHIFARI HARAMAIN
            </h1>
            <h5 class="text-royal-gold fw-semibold" style="letter-spacing: 2px;">
                MANAGEMENT SYSTEM
            </h5>
            <p class="text-muted small text-uppercase" style="letter-spacing: 4px; color: #E5CF96 !important;">
                UMROH &amp; HAJI SPECIALIST
            </p>

            <div class="gold-divider"></div>

            <p class="mx-auto text-light opacity-75" style="max-width: 620px; font-size: 0.95rem;">
                Platform tata kelola terpadu operasional perjalanan ibadah Haji dan Umrah bagi Direksi, Admin, dan Staff Internal PT. Al Ghifari Haramain.
            </p>
        </div>

        <!-- System Initialization Status Grid -->
        <div class="row g-4 mb-5">
            <!-- PHP & Environment -->
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-royal-gold fs-4"><i class="bi bi-cpu"></i></span>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1">
                            <span class="status-indicator me-1"></span> Aktif
                        </span>
                    </div>
                    <h6 class="text-white fw-bold mb-1">Lingkungan Sistem (PHP)</h6>
                    <p class="small text-muted mb-2">PHP {{ PHP_VERSION }} (XAMPP VC19)</p>
                    <div class="small text-light opacity-75">
                        Laravel Framework <strong>{{ app()->version() }}</strong><br>
                        Zona Waktu: <strong>{{ config('app.timezone') }}</strong>
                    </div>
                </div>
            </div>

            <!-- Database Connection -->
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-royal-gold fs-4"><i class="bi bi-database-check"></i></span>
                        @php
                            $dbStatus = 'Terkoneksi';
                            $dbClass = 'success';
                            try {
                                \DB::connection()->getPdo();
                            } catch (\Exception $e) {
                                $dbStatus = 'Terputus';
                                $dbClass = 'danger';
                            }
                        @endphp
                        <span class="badge bg-{{ $dbClass }} bg-opacity-25 text-{{ $dbClass }} border border-{{ $dbClass }} border-opacity-50 px-2 py-1">
                            <span class="status-indicator me-1"></span> {{ $dbStatus }}
                        </span>
                    </div>
                    <h6 class="text-white fw-bold mb-1">Koneksi Database MySQL</h6>
                    <p class="small text-muted mb-2">{{ config('database.connections.mysql.database') }}</p>
                    <div class="small text-light opacity-75">
                        Host: <strong>{{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port') }}</strong><br>
                        Driver: <strong>MySQL / InnoDB</strong> (Migration Batch 1 OK)
                    </div>
                </div>
            </div>

            <!-- Frontend & Bundler -->
            <div class="col-md-4">
                <div class="glass-card p-4 h-100">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="text-royal-gold fs-4"><i class="bi bi-palette"></i></span>
                        <span class="badge bg-success bg-opacity-25 text-success border border-success border-opacity-50 px-2 py-1">
                            <span class="status-indicator me-1"></span> Terpasang
                        </span>
                    </div>
                    <h6 class="text-white fw-bold mb-1">Frontend Design Engine</h6>
                    <p class="small text-muted mb-2">Bootstrap 5 + SCSS + Chart.js</p>
                    <div class="small text-light opacity-75">
                        Bundler: <strong>Vite 7.x</strong><br>
                        Identitas: <strong>Forest Green &amp; Royal Gold</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Integrated Modules Architecture Grid -->
        <div class="glass-card p-4 p-md-5 mb-5">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="text-white fw-bold mb-1" style="font-family: 'Outfit', sans-serif;">
                        <i class="bi bi-grid-1x2 me-2 text-royal-gold"></i> Modul Operasional Terintegrasi
                    </h4>
                    <p class="text-light opacity-75 small mb-0">
                        10 Modul utama yang telah dirancang dalam struktur arsitektur sistem
                    </p>
                </div>
                <span class="badge bg-royal-gold text-dark fw-bold px-3 py-2 mt-2 mt-md-0">
                    Tahap 1: Inisialisasi Selesai
                </span>
            </div>

            <div class="row g-3">
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-speedometer2 text-royal-gold fs-5"></i>
                        <span>1. Dashboard Eksekutif</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-people text-royal-gold fs-5"></i>
                        <span>2. Manajemen Jamaah</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-box-seam text-royal-gold fs-5"></i>
                        <span>3. Paket Haji &amp; Umrah</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-airplane text-royal-gold fs-5"></i>
                        <span>4. Keberangkatan &amp; Manifest</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-cash-stack text-royal-gold fs-5"></i>
                        <span>5. Keuangan &amp; Kwitansi</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-file-earmark-bar-graph text-royal-gold fs-5"></i>
                        <span>6. Laporan &amp; Rekapitulasi</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-shield-lock text-royal-gold fs-5"></i>
                        <span>7. User &amp; Role Spatie</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-whatsapp text-royal-gold fs-5"></i>
                        <span>8. WhatsApp Cloud API</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-bell text-royal-gold fs-5"></i>
                        <span>9. Notifikasi &amp; Audit Log</span>
                    </div>
                </div>
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="module-pill">
                        <i class="bi bi-gear text-royal-gold fs-5"></i>
                        <span>10. Pengaturan Sistem</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="text-center pt-3 text-light opacity-50 small">
            <p class="mb-1">&copy; {{ date('Y') }} <strong>Al Ghifari Haramain</strong>. All Rights Reserved.</p>
            <p class="mb-0">Sistem Khusus Operasional Internal &bull; Akses Terbatas untuk Otoritas Terdaftar</p>
        </div>
    </div>
</body>
</html>
