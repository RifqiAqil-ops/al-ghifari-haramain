@extends('layouts.app')

@section('title', 'Detail Pendaftaran - ' . $registration->registration_number)
@section('header_title', 'Detail Pendaftaran Paket')

@section('content')
<div class="row g-4" style="max-width: 1000px; margin: 0 auto;">
    <div class="col-12">
        <div class="card card-custom p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-3 mb-4">
                <div>
                    <span class="badge bg-forest-green text-royal-gold font-monospace mb-1">{{ $registration->registration_number }}</span>
                    <h5 class="fw-bold text-dark mb-0">{{ $registration->package?->name ?? 'Paket tidak tersedia' }}</h5>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge {{ $registration->status === 'siap_berangkat' ? 'badge-subtle-success' : 'badge-subtle-info' }} px-3 py-2">
                        Status: {{ $registration->status_label }}
                    </span>
                    <a href="{{ route('registrations.index') }}" class="btn btn-sm btn-light border">
                        <i class="bi bi-arrow-left me-1"></i> Kembali
                    </a>
                </div>
            </div>

            <div class="row g-4 mb-4">
                <div class="col-md-6 border-end">
                    <h6 class="fw-bold text-forest-green mb-3"><i class="bi bi-person-fill text-royal-gold me-1"></i> Data Jamaah</h6>
                    <table class="table table-sm table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Nama Lengkap</td>
                            <td>: <strong>{{ $registration->jamaah?->name ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">NIK</td>
                            <td>: {{ $registration->jamaah?->nik ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nomor Paspor</td>
                            <td>: {{ $registration->jamaah?->passport_number ?? '-' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">WhatsApp</td>
                            <td>: @if($registration->jamaah?->phone)<a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $registration->jamaah?->phone) }}" target="_blank" class="text-success text-decoration-none fw-semibold">{{ $registration->jamaah?->phone }}</a>@else - @endif</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Alamat</td>
                            <td>: {{ $registration->jamaah?->address ?? '-' }}{{ $registration->jamaah?->city ? ', ' . $registration->jamaah?->city : '' }}</td>
                        </tr>
                    </table>
                </div>

                <div class="col-md-6">
                    <h6 class="fw-bold text-forest-green mb-3"><i class="bi bi-airplane-fill text-royal-gold me-1"></i> Rincian Paket &amp; Jadwal</h6>
                    <table class="table table-sm table-borderless small mb-0">
                        <tr>
                            <td class="text-muted" style="width: 140px;">Program</td>
                            <td>: {{ $registration->package?->type ? ucfirst($registration->package?->type) : '-' }} ({{ $registration->package?->program_category ?? 'Reguler' }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Keberangkatan</td>
                            <td>: <strong>{{ $registration->departure?->departure_date?->translatedFormat('d F Y') ?? '-' }}</strong></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Maskapai</td>
                            <td>: {{ $registration->departure?->airline ?? '-' }} ({{ $registration->departure?->flight_number ?? '-' }})</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tipe Kamar</td>
                            <td>: <span class="badge bg-light text-dark border">{{ $registration->room_type_label }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Tour Leader</td>
                            <td>: {{ $registration->departure?->leader_name ?? '-' }} ({{ $registration->departure?->leader_phone ?? '-' }})</td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Financial Summary Box -->
            <div class="card p-3 bg-light border-0 rounded-3 mb-4">
                <div class="row g-3 align-items-center">
                    <div class="col-sm-3">
                        <small class="text-muted d-block">Harga Kesepakatan</small>
                        <strong class="text-dark">Rp {{ number_format($registration->agreed_price, 0, ',', '.') }}</strong>
                    </div>
                    <div class="col-sm-3">
                        <small class="text-muted d-block">Potongan / Diskon</small>
                        <strong class="text-success">- Rp {{ number_format($registration->discount, 0, ',', '.') }}</strong>
                    </div>
                    <div class="col-sm-3">
                        <small class="text-muted d-block">Total Tagihan Bersih</small>
                        <h6 class="text-forest-green fw-bold mb-0">Rp {{ number_format($registration->final_price, 0, ',', '.') }}</h6>
                    </div>
                    <div class="col-sm-3 text-sm-end">
                        @if($registration->invoice)
                            <a href="{{ route('finance.invoices.show', $registration->invoice) }}" class="btn btn-sm btn-forest-green">
                                <i class="bi bi-receipt me-1"></i> Buka Invoice #{{ $registration->invoice?->invoice_number }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            @if($registration->special_notes)
                <div class="p-3 bg-white border rounded-3 mb-4 small">
                    <strong class="text-dark d-block mb-1"><i class="bi bi-chat-left-text me-1 text-royal-gold"></i> Catatan Pendaftaran:</strong>
                    {{ $registration->special_notes }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
