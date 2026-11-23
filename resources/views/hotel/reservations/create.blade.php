@extends('layouts.app')

@section('title', 'Buat Reservasi Hotel')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Reservasi
            </a>
            <h4 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-plus text-primary me-2"></i>Formulir Reservasi Akomodasi Hotel</h4>
            <p class="text-muted small mb-0">Input data pemesanan kamar hotel (Makkah &amp; Madinah) multi-kategori dan penerbitan invoice otomatis</p>
        </div>
        <div class="badge bg-light text-dark border p-2">
            Kurs Acuan: <strong>1 SAR = Rp {{ number_format($liveRate['rate'], 2) }}</strong>
        </div>
    </div>

    <form action="{{ route('hotel.reservations.store') }}" method="POST">
        @csrf
        <div class="row g-4">
            <!-- Left Column: Master Info -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold mb-0 text-dark">Data Pelanggan &amp; Bisnis</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Pelanggan / Travel Agent <span class="text-danger">*</span></label>
                            <select name="customer_id" class="form-select" required>
                                <option value="">-- Pilih Pelanggan --</option>
                                @foreach($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->company_name }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">Model Bisnis <span class="text-danger">*</span></label>
                            <select name="business_model" class="form-select" required>
                                <option value="reseller" selected>Reseller (Beli &amp; Jual Kembali)</option>
                                <option value="commission">Komisi / Agen Keagenan</option>
                                <option value="allotment">Allotment (Kontrak Kuota Kamar)</option>
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Tgl Check-in <span class="text-danger">*</span></label>
                                <input type="date" name="check_in_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-semibold small">Tgl Check-out <span class="text-danger">*</span></label>
                                <input type="date" name="check_out_date" class="form-control" value="{{ date('Y-m-d', strtotime('+5 days')) }}" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold small">No Referensi / Konfirmasi Hotel</label>
                            <input type="text" name="confirmation_reference" class="form-control" placeholder="Contoh: CONF-MAK-9921">
                        </div>

                        <div class="mb-3 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="generate_invoice" id="genInvoice" value="1" checked>
                            <label class="form-check-label small fw-semibold" for="genInvoice">Terbitkan Invoice Otomatis</label>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Room Items (Dynamic Table) -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold mb-0 text-dark">Rincian Kamar &amp; Tarif (Maks. Sesuai Invoice Acuan)</h6>
                    </div>
                    <div class="card-body">
                        <!-- Single Room Row Example (extendable) -->
                        <div class="p-3 border rounded-3 bg-light mb-3">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Hotel <span class="text-danger">*</span></label>
                                    <select name="items[0][hotel_id]" class="form-select" required>
                                        @foreach($hotels as $h)
                                        <option value="{{ $h->id }}">{{ $h->name }} ({{ $h->city }} &bull; {{ $h->category }})</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold small">Tipe Kamar <span class="text-danger">*</span></label>
                                    <select name="items[0][room_type_id]" class="form-select" required>
                                        @foreach($hotels as $h)
                                            @foreach($h->roomTypes as $rt)
                                            <option value="{{ $rt->id }}">{{ $h->name }} - {{ $rt->name }} (Tarif Std: SAR {{ $rt->default_selling_rate }})</option>
                                            @endforeach
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Jumlah Kamar <span class="text-danger">*</span></label>
                                    <input type="number" name="items[0][room_quantity]" class="form-control" value="2" min="1" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Tarif Beli / Modal (SAR)</label>
                                    <input type="number" step="0.01" name="items[0][purchase_rate_per_night_sar]" class="form-control" value="800.00">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold small">Tarif Jual (SAR) <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" name="items[0][selling_rate_per_night_sar]" class="form-control" value="950.00" required>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-check-lg me-1"></i> Simpan Reservasi &amp; Hitung Keuangan
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
