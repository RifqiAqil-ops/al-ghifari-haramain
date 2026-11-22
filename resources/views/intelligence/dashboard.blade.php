@extends('layouts.app')

@section('title', 'Al Ghifari Intelligence System')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-stars text-warning me-2"></i>Al Ghifari Intelligence System
            </h2>
            <p class="text-muted small mb-0">Pusat Kecerdasan Operasional, Analitik Prediktif, dan Otomasi Terintegrasi</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('intelligence.alerts.scan') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-success btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Pindai Indikator (Scan Now)
                </button>
            </form>
            @can('intelligence.chat')
            <a href="{{ route('intelligence.assistant.index') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-chat-dots-fill me-1"></i> Buka AI Assistant
            </a>
            @endcan
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="row g-3 mb-4">
        <!-- Business Health Index -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Indeks Kesehatan Bisnis</span>
                        <span class="badge bg-{{ $businessHealthScore >= 75 ? 'success' : ($businessHealthScore >= 50 ? 'warning' : 'danger') }} rounded-pill">
                            {{ $businessHealthScore >= 75 ? 'Sangat Prima' : ($businessHealthScore >= 50 ? 'Cukup' : 'Perhatian') }}
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bold mb-0 text-dark">{{ $businessHealthScore }}</h2>
                        <span class="text-muted ms-1">/100</span>
                    </div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $businessHealthScore }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">Agregasi performa finansial & kesiapan kloter</small>
                </div>
            </div>
        </div>

        <!-- Financial Collection Rate -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Realisasi Kas Masuk</span>
                        <span class="badge bg-info rounded-pill">Koleksi Piutang</span>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bold mb-0 text-dark">{{ $financialHealth['collection_rate'] }}%</h2>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Paid: <strong>Rp {{ number_format($financialHealth['total_paid'], 0, ',', '.') }}</strong>
                    </small>
                    <small class="text-danger d-block">
                        Sisa Piutang: Rp {{ number_format($financialHealth['total_outstanding'], 0, ',', '.') }}
                    </small>
                </div>
            </div>
        </div>

        <!-- Active Departures -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Kloter Mendatang</span>
                        <span class="badge bg-primary rounded-pill">Operasional</span>
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bold mb-0 text-dark">{{ $upcomingDeparturesCount }}</h2>
                        <span class="text-muted ms-1">Kloter</span>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        Total Jamaah Aktif: <strong>{{ number_format($totalActiveJamaah, 0, ',', '.') }}</strong> orang
                    </small>
                    <a href="{{ route('intelligence.departures.index') }}" class="small text-decoration-none mt-1 d-inline-block">
                        Lihat Kesiapan Kloter <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <!-- Active Alerts -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="text-muted small fw-semibold">Peringatan Cerdas</span>
                        @if($criticalCount > 0)
                        <span class="badge bg-danger rounded-pill">{{ $criticalCount }} Kritis</span>
                        @else
                        <span class="badge bg-success rounded-pill">Aman</span>
                        @endif
                    </div>
                    <div class="d-flex align-items-baseline">
                        <h2 class="fw-bold mb-0 text-dark">{{ $activeAlerts->count() }}</h2>
                        <span class="text-muted ms-1">Alert Aktif</span>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ $warningCount }} Peringatan Waspada
                    </small>
                    <a href="{{ route('intelligence.alerts.index') }}" class="small text-danger text-decoration-none mt-1 d-inline-block">
                        Buka Alert Center <i class="bi bi-chevron-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Section: Alert Center & Departures Readiness -->
    <div class="row g-4 mb-4">
        <!-- Alert Center Feed -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-bell-fill text-danger me-2"></i>Peringatan Prioritas (Smart Alert Center)
                    </h5>
                    <a href="{{ route('intelligence.alerts.index') }}" class="btn btn-light btn-sm text-muted">
                        Lihat Semua ({{ $activeAlerts->count() }})
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($activeAlerts->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-shield-check text-success display-6 mb-2"></i>
                        <p class="mb-0">Tidak ada peringatan kritis atau kendala operasional yang terdeteksi saat ini.</p>
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach($activeAlerts as $alert)
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <span class="badge bg-{{ $alert->level === 'critical' ? 'danger' : ($alert->level === 'warning' ? 'warning text-dark' : 'info') }} me-1">
                                        {{ strtoupper($alert->level) }}
                                    </span>
                                    <span class="badge bg-light text-secondary border me-2">{{ ucfirst($alert->category) }}</span>
                                    <h6 class="mb-1 fw-bold text-dark mt-1">{{ $alert->title }}</h6>
                                    <p class="mb-1 text-muted small">{{ $alert->summary }}</p>
                                </div>
                                <div class="text-end">
                                    @if($alert->action_url)
                                    <a href="{{ $alert->action_url }}" class="btn btn-outline-primary btn-sm py-0 px-2" style="font-size: 11px;">
                                        {{ $alert->action_text ?? 'Detail' }}
                                    </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upcoming Departures Readiness -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-airplane-engines text-primary me-2"></i>Kesiapan Kloter Terdekat
                    </h5>
                    <a href="{{ route('intelligence.departures.index') }}" class="btn btn-light btn-sm text-muted">
                        Semua Kloter
                    </a>
                </div>
                <div class="card-body p-0">
                    @if(empty($upcomingReadiness))
                    <div class="p-4 text-center text-muted">
                        <p class="mb-0">Belum ada data keberangkatan aktif.</p>
                    </div>
                    @else
                    <div class="list-group list-group-flush">
                        @foreach(array_slice($upcomingReadiness, 0, 4) as $kloter)
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div>
                                    <span class="fw-bold text-dark">Kloter {{ $kloter['code'] }}</span>
                                    <span class="text-muted small">({{ \Carbon\Carbon::parse($kloter['departure_date'])->format('d M Y') }})</span>
                                </div>
                                <span class="badge bg-{{ $kloter['badge'] }}">
                                    {{ $kloter['readiness_score'] }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 6px;">
                                <div class="progress-bar bg-{{ $kloter['badge'] }}" style="width: {{ $kloter['readiness_score'] }}%"></div>
                            </div>
                            <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 11px;">
                                <span>Terisi: {{ $kloter['total_registered'] }}/{{ $kloter['quota'] }} seat</span>
                                <span>{{ $kloter['days_to_departure'] }} hari lagi</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Modules -->
    <div class="row g-3">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 hover-shadow transition">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle">
                        <i class="bi bi-graph-up-arrow fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Smart Financial</h6>
                        <small class="text-muted">Anomali & Arus Kas</small>
                    </div>
                </div>
                <a href="{{ route('intelligence.financial.index') }}" class="stretched-link"></a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 hover-shadow transition">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle">
                        <i class="bi bi-clock-history fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Predictive Analytics</h6>
                        <small class="text-muted">Proyeksi Okupansi & Stok</small>
                    </div>
                </div>
                <a href="{{ route('intelligence.predictive.index') }}" class="stretched-link"></a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 hover-shadow transition">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-warning bg-opacity-10 text-warning p-3 rounded-circle">
                        <i class="bi bi-file-earmark-check fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Document OCR</h6>
                        <small class="text-muted">Ekstraksi KTP & Paspor</small>
                    </div>
                </div>
                <a href="{{ route('intelligence.documents.index') }}" class="stretched-link"></a>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3 h-100 hover-shadow transition">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-info bg-opacity-10 text-info p-3 rounded-circle">
                        <i class="bi bi-clipboard2-data fs-4"></i>
                    </div>
                    <div>
                        <h6 class="mb-0 fw-bold">Executive Reports</h6>
                        <small class="text-muted">Laporan Analitis Bisnis</small>
                    </div>
                </div>
                <a href="{{ route('intelligence.reports.index') }}" class="stretched-link"></a>
            </div>
        </div>
    </div>
</div>
@endsection
