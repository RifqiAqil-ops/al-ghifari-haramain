@extends('layouts.app')

@section('title', 'Jamaah 360: ' . $jamaah->name)

@section('content')
<div class="container-fluid py-3">
    <!-- Breadcrumb & Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('intelligence.crm.index') }}" class="text-decoration-none small text-muted mb-1 d-inline-block">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Direktori Smart CRM
            </a>
            <h2 class="h4 mb-0 fw-bold text-dark">{{ $jamaah->name }}</h2>
            <span class="text-muted small">No. Registrasi: {{ $jamaah->registration_number }} &bull; NIK: {{ $jamaah->nik }}</span>
        </div>
        <div>
            <span class="badge bg-{{ $profile360['segment']['badge'] }} fs-6 px-3 py-2">
                {{ $profile360['segment']['name'] }}
            </span>
        </div>
    </div>

    <!-- Top KPI Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Total Perjalanan</span>
                <h3 class="fw-bold mb-0 text-dark">{{ $profile360['metrics']['total_trips'] }} Kali</h3>
                <small class="text-muted">Loyalitas Jamaah</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Total Kontribusi Finansial</span>
                <h3 class="fw-bold mb-0 text-success">Rp {{ number_format($profile360['metrics']['lifetime_spent'], 0, ',', '.') }}</h3>
                <small class="text-muted">Lifetime Value</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Sisa Piutang Berjalan</span>
                <h3 class="fw-bold mb-0 {{ $profile360['metrics']['current_outstanding'] > 0 ? 'text-danger' : 'text-success' }}">
                    Rp {{ number_format($profile360['metrics']['current_outstanding'], 0, ',', '.') }}
                </h3>
                <small class="text-muted">Tagihan Aktif</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3 p-3">
                <span class="text-muted small">Kloter Aktif</span>
                <h3 class="fw-bold mb-0 text-primary">{{ $profile360['metrics']['active_departure'] }}</h3>
                <small class="text-muted">{{ $profile360['metrics']['departure_date'] ? \Carbon\Carbon::parse($profile360['metrics']['departure_date'])->format('d M Y') : 'Belum Terjadwal' }}</small>
            </div>
        </div>
    </div>

    <!-- Registration & WhatsApp History -->
    <div class="row g-4">
        <!-- Trip History -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-airplane-fill text-primary me-2"></i>Riwayat Pendaftaran &amp; Pembayaran
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($jamaah->registrations as $reg)
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <h6 class="mb-0 fw-bold">{{ $reg->package?->name ?? 'Paket' }}</h6>
                                <span class="badge bg-{{ $reg->status === 'selesai' ? 'success' : ($reg->status === 'batal' ? 'danger' : 'primary') }}">
                                    {{ strtoupper($reg->status) }}
                                </span>
                            </div>
                            <small class="text-muted d-block">
                                Kloter: {{ $reg->departure?->code ?? '-' }} &bull; Tanggal Daftar: {{ \Carbon\Carbon::parse($reg->registration_date)->format('d M Y') }}
                            </small>
                            <div class="d-flex justify-content-between mt-2 pt-2 border-top small">
                                <span>Tagihan: Rp {{ number_format($reg->final_price, 0, ',', '.') }}</span>
                                <span class="fw-semibold text-success">Terbayar: Rp {{ number_format($reg->invoice?->paid_amount ?? 0, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="p-3 text-muted text-center">Belum ada riwayat pendaftaran.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- WhatsApp Log & Equipment Distributions -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-whatsapp text-success me-2"></i>Riwayat Interaksi WhatsApp
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="font-size: 13px;">
                        @forelse($profile360['whatsapp_history'] as $wa)
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="badge bg-light text-dark border">{{ strtoupper($wa->message_type) }}</span>
                                <span class="text-muted" style="font-size: 11px;">{{ $wa->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <p class="mb-0 text-dark small" style="white-space: pre-wrap;">{{ $wa->content }}</p>
                        </div>
                        @empty
                        <div class="p-3 text-muted text-center">Belum ada pesan WhatsApp yang dikirimkan.</div>
                        @endforelse
                    </div>
                </div>
            </div>

            <!-- Equipment Distributions -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold fs-6">
                        <i class="bi bi-box-seam text-warning me-2"></i>Serah Terima Perlengkapan
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="font-size: 13px;">
                        @forelse($profile360['equipment_history'] as $eq)
                        <div class="list-group-item p-3 border-light">
                            <div class="d-flex justify-content-between">
                                <span class="fw-bold">{{ $eq->distribution_number }}</span>
                                <span class="badge bg-success">Diserahkan</span>
                            </div>
                            <small class="text-muted">Tanggal: {{ \Carbon\Carbon::parse($eq->distribution_date)->format('d M Y') }} &bull; Kloter: {{ $eq->departure?->code ?? '-' }}</small>
                        </div>
                        @empty
                        <div class="p-3 text-muted text-center">Belum ada penyerahan perlengkapan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
