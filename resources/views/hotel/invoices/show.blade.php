@extends('layouts.app')

@section('title', 'Detail Invoice Hotel - ' . $invoice->invoice_number)

@section('content')
<div class="container-fluid py-3">
    <!-- Header with Back & Action Buttons -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <a href="{{ route('hotel.invoices.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Invoice
            </a>
            <div class="d-flex align-items-center gap-2">
                <h4 class="fw-bold mb-0 text-dark">{{ $invoice->invoice_number }}</h4>
                <span class="badge bg-{{ $invoice->status === 'lunas' ? 'success' : ($invoice->status === 'sebagian' ? 'warning' : 'danger') }} rounded-pill px-3 py-1 text-uppercase">
                    {{ $invoice->status }}
                </span>
            </div>
            <p class="text-muted small mb-0">
                Tanggal: {{ $invoice->invoice_date->format('d M Y') }} | Jatuh Tempo: {{ $invoice->due_date->format('d M Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('hotel.invoices.print', $invoice) }}" target="_blank" class="btn btn-outline-dark shadow-sm btn-sm">
                <i class="bi bi-printer me-1"></i> Cetak / PDF Invoice
            </a>
            @if($invoice->remaining_amount_sar > 0 && $invoice->status !== 'batal')
            <button type="button" class="btn btn-success shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                <i class="bi bi-cash-stack me-1"></i> Catat Pembayaran (SAR / IDR)
            </button>
            @endif
        </div>
    </div>

    <!-- Currency Snapshot Alert -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white border-start border-4 border-info">
        <div class="card-body py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <span class="text-muted small d-block">Snapshot Kurs Terkunci pada Invoice Ini:</span>
                <strong class="fs-6 text-dark">1 SAR = Rp {{ number_format($invoice->exchange_rate_snapshot, 2, ',', '.') }}</strong>
                <span class="text-muted small ms-2">({{ $invoice->rate_provider_snapshot }} &bull; {{ $invoice->rate_timestamp_snapshot?->format('d M Y H:i') }})</span>
            </div>
            <div class="text-md-end small">
                <span class="text-muted">Kurs Live Hari Ini: </span>
                <strong>Rp {{ number_format($liveRate['rate'], 2, ',', '.') }}</strong>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Line Items Table (Modeled after Excel Reference) -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check text-primary me-2"></i>Rincian Reservasi &amp; Kamar Hotel</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>Hotel &amp; Tipe Kamar</th>
                                    <th class="text-center">Periode Stay</th>
                                    <th class="text-center">Malam</th>
                                    <th class="text-center">Kamar</th>
                                    <th class="text-center">Room Nights</th>
                                    <th class="text-end">Tarif/Mlm (SAR)</th>
                                    <th class="text-end">Subtotal (SAR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($invoice->items as $item)
                                <tr>
                                    <td>
                                        <strong class="text-dark">{{ $item->hotel_name }}</strong>
                                        <span class="d-block text-muted small">{{ $item->room_type_name }}</span>
                                    </td>
                                    <td class="text-center">
                                        {{ \Carbon\Carbon::parse($item->check_in)->format('d/m/y') }} - {{ \Carbon\Carbon::parse($item->check_out)->format('d/m/y') }}
                                    </td>
                                    <td class="text-center">{{ $item->number_of_nights }}</td>
                                    <td class="text-center">{{ $item->room_quantity }}</td>
                                    <td class="text-center fw-bold">{{ $item->room_nights }}</td>
                                    <td class="text-end">SAR {{ number_format($item->rate_per_night_sar, 2, ',', '.') }}</td>
                                    <td class="text-end fw-bold">SAR {{ number_format($item->subtotal_sar, 2, ',', '.') }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="6" class="text-end">Total Tagihan (SAR):</th>
                                    <th class="text-end fw-bold text-dark fs-6">SAR {{ number_format($invoice->total_amount_sar, 2, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end text-muted">Ekuivalen IDR (Sesuai Kurs Snapshot):</th>
                                    <th class="text-end fw-bold text-primary">Rp {{ number_format($invoice->total_amount_idr, 0, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end text-success">Sudah Dibayar (Paid):</th>
                                    <th class="text-end text-success">SAR {{ number_format($invoice->paid_amount_sar, 2, ',', '.') }}</th>
                                </tr>
                                <tr>
                                    <th colspan="6" class="text-end text-danger">Sisa Tagihan (Outstanding):</th>
                                    <th class="text-end text-danger fw-bold">SAR {{ number_format($invoice->remaining_amount_sar, 2, ',', '.') }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Payment History Table -->
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-success me-2"></i>Riwayat Pembayaran &amp; Selisih Kurs</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>No Kwitansi</th>
                                    <th>Tanggal</th>
                                    <th>Nominal Disetor</th>
                                    <th>Kurs Bayar</th>
                                    <th>Ekuivalen SAR</th>
                                    <th>Selisih Kurs (IDR)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoice->payments as $pay)
                                <tr>
                                    <td class="fw-bold">{{ $pay->payment_number }}</td>
                                    <td>{{ $pay->payment_date->format('d M Y') }}</td>
                                    <td>
                                        <strong>{{ $pay->payment_currency }} {{ number_format($pay->payment_amount, 2, ',', '.') }}</strong>
                                    </td>
                                    <td>1 SAR = Rp {{ number_format($pay->payment_exchange_rate, 2, ',', '.') }}</td>
                                    <td class="fw-bold text-dark">SAR {{ number_format($pay->equivalent_amount_sar, 2, ',', '.') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $pay->fx_gain_loss_idr >= 0 ? 'success' : 'danger' }}-subtle text-{{ $pay->fx_gain_loss_idr >= 0 ? 'success' : 'danger' }}">
                                            {{ $pay->fx_gain_loss_idr >= 0 ? '+' : '' }}Rp {{ number_format($pay->fx_gain_loss_idr, 0, ',', '.') }}
                                        </span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat pembayaran untuk invoice ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Customer Info & Notes -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-person-badge text-primary me-2"></i>Data Pelanggan / Agen</h6>
                </div>
                <div class="card-body">
                    <h6 class="fw-bold mb-1">{{ $invoice->customer?->name }}</h6>
                    <span class="text-muted small d-block mb-2">{{ $invoice->customer?->company_name ?? 'Individual' }}</span>
                    <hr class="my-2">
                    <div class="small text-muted mb-1">Telepon: <strong class="text-dark">{{ $invoice->customer?->phone ?? '-' }}</strong></div>
                    <div class="small text-muted mb-1">Email: <strong class="text-dark">{{ $invoice->customer?->email ?? '-' }}</strong></div>
                    <div class="small text-muted">Syarat Pembayaran: <strong class="text-dark">{{ $invoice->customer?->payment_terms_days }} Hari</strong></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle text-muted me-2"></i>Syarat &amp; Ketentuan</h6>
                </div>
                <div class="card-body small text-muted">
                    <p class="mb-0">{{ $invoice->payment_terms }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Catat Pembayaran Mixed Currency -->
<div class="modal fade" id="paymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hotel.invoices.payments.store', $invoice) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cash-stack text-success me-2"></i>Catat Pembayaran Hotel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small mb-3">
                        Sisa tagihan: <strong>SAR {{ number_format($invoice->remaining_amount_sar, 2, ',', '.') }}</strong> 
                        (Est. Rp {{ number_format($invoice->remaining_amount_idr, 0, ',', '.') }})
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Mata Uang Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_currency" class="form-select" required>
                            <option value="SAR" selected>SAR - Saudi Riyal</option>
                            <option value="IDR">IDR - Indonesian Rupiah</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nominal yang Disetor <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="payment_amount" class="form-control" value="{{ $invoice->remaining_amount_sar }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Kurs Konversi (Opsional, Default Live)</span></label>
                        <input type="number" step="0.01" name="payment_exchange_rate" class="form-control" value="{{ $liveRate['rate'] }}">
                        <div class="form-text small">Biarkan sesuai kurs live (Rp {{ number_format($liveRate['rate'], 2) }}) jika tidak ada kesepakatan kurs khusus.</div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tanggal Bayar <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small">Tipe Pembayaran <span class="text-danger">*</span></label>
                            <select name="payment_type" class="form-select" required>
                                <option value="dp">Uang Muka (DP)</option>
                                <option value="cicilan">Cicilan</option>
                                <option value="pelunasan" selected>Pelunasan</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Metode &amp; Bank</label>
                        <input type="text" name="bank_name" class="form-control" placeholder="Contoh: Transfer BSI / Al Rajhi Bank">
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Keterangan / Catatan</label>
                        <input type="text" name="notes" class="form-control" placeholder="Referensi pembayaran...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-save me-1"></i> Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
