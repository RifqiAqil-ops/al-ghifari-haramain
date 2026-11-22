@extends('layouts.app')

@section('title', 'Detail Kesiapan Kloter ' . $departure->code)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('intelligence.departures.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kesiapan Kloter
            </a>
            <h2 class="h4 mb-0 fw-bold text-dark">
                Audit Kesiapan Kloter {{ $departure->code }}
            </h2>
            <span class="text-muted small">{{ $departure->package?->name ?? 'Paket' }} &bull; Berangkat: {{ \Carbon\Carbon::parse($departure->departure_date)->format('d F Y') }}</span>
        </div>
        <div>
            <span class="badge bg-{{ $readiness['badge'] }} fs-5 px-3 py-2">
                Skor Kesiapan: {{ $readiness['readiness_score'] }}%
            </span>
        </div>
    </div>

    <!-- 5 Dimension Breakdown Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <small class="text-muted">1. Kuota Kursi</small>
                <h4 class="fw-bold mb-0 text-dark">{{ $readiness['breakdown']['quota']['score'] }} / 20</h4>
                <small class="text-muted">{{ $readiness['total_registered'] }} / {{ $readiness['quota'] }} seat</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <small class="text-muted">2. Dokumen Paspor</small>
                <h4 class="fw-bold mb-0 text-dark">{{ $readiness['breakdown']['documents']['score'] }} / 30</h4>
                <small class="text-danger">{{ $readiness['breakdown']['documents']['missing'] }} belum lengkap</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <small class="text-muted">3. Pelunasan Biaya</small>
                <h4 class="fw-bold mb-0 text-dark">{{ $readiness['breakdown']['payments']['score'] }} / 25</h4>
                <small class="text-danger">Sisa: Rp {{ number_format($readiness['breakdown']['payments']['unpaid_amount'], 0, ',', '.') }}</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <small class="text-muted">4. Distribusi Koper</small>
                <h4 class="fw-bold mb-0 text-dark">{{ $readiness['breakdown']['equipment']['score'] }} / 15</h4>
                <small class="text-muted">{{ $readiness['breakdown']['equipment']['distributed'] }} jamaah menerima</small>
            </div>
        </div>
        <div class="col-md">
            <div class="card border-0 shadow-sm rounded-3 p-3 text-center">
                <small class="text-muted">5. Checklist Kloter</small>
                <h4 class="fw-bold mb-0 text-dark">{{ $readiness['breakdown']['checklists']['score'] }} / 10</h4>
                <small class="text-muted">{{ $readiness['breakdown']['checklists']['completed'] }} / {{ $readiness['breakdown']['checklists']['total'] }} item</small>
            </div>
        </div>
    </div>

    <!-- Jamaah Readiness Table -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-people-fill text-primary me-2"></i>Daftar Jamaah &amp; Status Kelengkapan Persyaratan
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Jamaah</th>
                            <th>No. Registrasi</th>
                            <th>KTP</th>
                            <th>Paspor</th>
                            <th>Status Tagihan</th>
                            <th>Sisa Tagihan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($departure->registrations->where('status', '!=', 'batal') as $reg)
                        @php
                            $hasKtp = $reg->jamaah?->documents->where('document_type', 'ktp')->where('status', 'diverifikasi')->isNotEmpty();
                            $hasPaspor = $reg->jamaah?->documents->where('document_type', 'paspor')->where('status', 'diverifikasi')->isNotEmpty();
                            $inv = $reg->invoice;
                        @endphp
                        <tr>
                            <td class="fw-bold">{{ $reg->jamaah?->name ?? 'Jamaah' }}</td>
                            <td>{{ $reg->registration_number }}</td>
                            <td>
                                @if($hasKtp)
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Valid</span>
                                @else
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Kurang</span>
                                @endif
                            </td>
                            <td>
                                @if($hasPaspor)
                                <span class="badge bg-success"><i class="bi bi-check-lg"></i> Valid</span>
                                @else
                                <span class="badge bg-danger"><i class="bi bi-x-lg"></i> Kurang</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $inv?->status === 'lunas' ? 'success' : 'warning text-dark' }}">
                                    {{ strtoupper($inv?->status ?? 'BELUM BAYAR') }}
                                </span>
                            </td>
                            <td class="text-danger fw-semibold">
                                Rp {{ number_format($inv?->remaining_amount ?? 0, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Belum ada jamaah terdaftar di kloter ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
