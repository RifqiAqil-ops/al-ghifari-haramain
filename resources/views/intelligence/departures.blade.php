@extends('layouts.app')

@section('title', 'Smart Departure Readiness')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-airplane-engines text-primary me-2"></i>Smart Departure Intelligence &amp; Readiness
            </h2>
            <p class="text-muted small mb-0">Skor Kesiapan Kloter, Pemeriksaan Dokumen, Pembayaran, Logistik, dan Deteksi Kendala Operasional</p>
        </div>
        <div class="badge bg-light text-dark border px-3 py-2">
            Multi-Dimensional Weighted Readiness Index (0 - 100%)
        </div>
    </div>

    <!-- Departures Scoreboard -->
    <div class="row g-4">
        @forelse($scoreboard as $item)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 pt-3 pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Kloter {{ $item['code'] }}</h5>
                        <small class="text-muted">{{ $item['package_name'] }}</small>
                    </div>
                    <span class="badge bg-{{ $item['badge'] }} fs-6 px-2 py-1">
                        {{ $item['readiness_score'] }}%
                    </span>
                </div>

                <div class="card-body">
                    <!-- Readiness Progress Bar -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>Status: <strong>{{ $item['status_label'] }}</strong></span>
                            <span>{{ $item['days_to_departure'] }} hari lagi</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-{{ $item['badge'] }}" style="width: {{ $item['readiness_score'] }}%"></div>
                        </div>
                    </div>

                    <!-- Breakdown Metrics -->
                    <div class="bg-light p-3 rounded-2 mb-3" style="font-size: 12px;">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Keterisian Kuota (Max 20):</span>
                            <strong>{{ $item['breakdown']['quota']['score'] }} pts ({{ $item['total_registered'] }}/{{ $item['quota'] }})</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Kelengkapan Dokumen (Max 30):</span>
                            <strong>{{ $item['breakdown']['documents']['score'] }} pts ({{ $item['breakdown']['documents']['ratio'] }}%)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Pelunasan Pembayaran (Max 25):</span>
                            <strong>{{ $item['breakdown']['payments']['score'] }} pts</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Distribusi Koper (Max 15):</span>
                            <strong>{{ $item['breakdown']['equipment']['score'] }} pts</strong>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span>Checklist Kloter (Max 10):</span>
                            <strong>{{ $item['breakdown']['checklists']['score'] }} pts ({{ $item['breakdown']['checklists']['completed'] }}/{{ $item['breakdown']['checklists']['total'] }})</strong>
                        </div>
                    </div>

                    <!-- Risks Alert -->
                    @if(!empty($item['risks']))
                    <div class="mb-3">
                        <span class="small fw-bold text-danger d-block mb-1">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>Kendala Terdeteksi ({{ count($item['risks']) }}):
                        </span>
                        <ul class="list-unstyled mb-0" style="font-size: 11.5px;">
                            @foreach($item['risks'] as $risk)
                            <li class="text-danger mb-1">&bull; {{ $risk }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @else
                    <div class="text-success small mb-3">
                        <i class="bi bi-check-circle-fill me-1"></i> Tidak ada hambatan kritis terdeteksi.
                    </div>
                    @endif
                </div>

                <div class="card-footer bg-white border-0 pt-0 pb-3 d-flex justify-content-between">
                    <a href="{{ route('departures.show', $item['departure_id']) }}" class="btn btn-outline-secondary btn-sm" style="font-size: 12px;">
                        <i class="bi bi-eye me-1"></i> Manifest
                    </a>
                    <a href="{{ route('intelligence.departures.show', $item['departure_id']) }}" class="btn btn-primary btn-sm" style="font-size: 12px;">
                        <i class="bi bi-shield-check me-1"></i> Audit Kesiapan
                    </a>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 p-5 text-center text-muted">
                <i class="bi bi-airplane display-4 mb-3"></i>
                <h5>Belum Ada Kloter Keberangkatan Terdaftar</h5>
                <p class="small mb-0">Silakan buat jadwal kloter pada Modul Keberangkatan.</p>
            </div>
        </div>
        @endforelse
    </div>
</div>
@endsection
