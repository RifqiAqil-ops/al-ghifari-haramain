@extends('layouts.app')

@section('title', 'Riwayat Pembayaran Jamaah - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Riwayat Pembayaran & Kwitansi</h4>
        <p class="text-muted small mb-0">Catatan mutasi kas masuk pelunasan paket Haji dan Umrah</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('finance.invoices') }}" class="btn btn-outline-secondary">
            <i class="bi bi-receipt me-1"></i> Daftar Invoice
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('finance.payments') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                        value="{{ $search }}" placeholder="Cari No Kwitansi, No Transaksi, atau Nama Jamaah...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select form-select-sm">
                    <option value="">-- Semua Tipe --</option>
                    <option value="dp" {{ $type == 'dp' ? 'selected' : '' }}>Uang Muka (DP)</option>
                    <option value="cicilan" {{ $type == 'cicilan' ? 'selected' : '' }}>Cicilan / Angsuran</option>
                    <option value="pelunasan" {{ $type == 'pelunasan' ? 'selected' : '' }}>Pelunasan</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="method" class="form-select form-select-sm">
                    <option value="">-- Metode --</option>
                    <option value="transfer" {{ $method == 'transfer' ? 'selected' : '' }}>Transfer</option>
                    <option value="tunai" {{ $method == 'tunai' ? 'selected' : '' }}>Tunai</option>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search || $type || $method)
                    <a href="{{ route('finance.payments') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Card -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No Kwitansi</th>
                        <th>Jamaah & Paket</th>
                        <th>Tipe / Metode</th>
                        <th>Tanggal Bayar</th>
                        <th>Penerima</th>
                        <th class="text-end">Nominal</th>
                        <th class="text-center">Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $pay)
                        <tr>
                            <td>
                                <a href="{{ route('finance.receipt', $pay) }}" target="_blank" class="fw-bold text-dark text-decoration-none">
                                    {{ $pay->receipt_number }}
                                </a>
                                <span class="text-muted small d-block">{{ $pay->transaction_number }}</span>
                            </td>
                            <td>
                                <strong class="text-dark d-block">{{ $pay->invoice->registration->jamaah->name ?? '-' }}</strong>
                                <span class="text-muted small">{{ $pay->invoice->registration->package->name ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">{{ $pay->payment_type }}</span>
                                <span class="text-muted small d-block">{{ strtoupper($pay->payment_method) }} ({{ $pay->bank_name ?? 'Tunai' }})</span>
                            </td>
                            <td>
                                {{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}
                            </td>
                            <td>
                                <span class="small text-secondary">{{ $pay->receiver->name ?? 'Staff' }}</span>
                            </td>
                            <td class="text-end fw-bold {{ $pay->status == 'valid' ? 'text-success' : 'text-decoration-line-through text-muted' }}">
                                Rp {{ number_format($pay->amount, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($pay->status == 'valid')
                                    <span class="badge bg-success-subtle text-success">Valid</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger" title="{{ $pay->cancellation_reason }}">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    <a href="{{ route('finance.invoices.show', $pay->invoice_id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Invoice">
                                        <i class="bi bi-receipt"></i>
                                    </a>
                                    @if($pay->status == 'valid')
                                        <a href="{{ route('finance.receipt', $pay) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Cetak Kwitansi PDF">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#cancelModal{{ $pay->id }}" title="Batalkan Pembayaran">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    @endif
                                </div>

                                <!-- Cancellation Modal -->
                                @if($pay->status == 'valid')
                                    <div class="modal fade text-start" id="cancelModal{{ $pay->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <form action="{{ route('finance.payments.cancel', $pay) }}" method="POST">
                                                    @csrf
                                                    @method('PATCH')
                                                    <div class="modal-header">
                                                        <h5 class="modal-title text-danger fw-bold"><i class="bi bi-exclamation-triangle me-1"></i> Batalkan Kwitansi {{ $pay->receipt_number }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="small text-muted">
                                                            Nominal yang akan dibatalkan: <strong>Rp {{ number_format($pay->amount, 0, ',', '.') }}</strong>.
                                                            Sisa tagihan invoice akan disesuaikan secara otomatis.
                                                        </p>
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold small">Alasan Pembatalan <span class="text-danger">*</span></label>
                                                            <textarea name="cancellation_reason" class="form-control" rows="3" required placeholder="Jelaskan alasan pembatalan transaksi ini..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                                                        <button type="submit" class="btn btn-danger">Konfirmasi Pembatalan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-cash-stack display-4 mb-3 d-block"></i>
                                <h6 class="fw-semibold">Belum ada transaksi pembayaran.</h6>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($payments->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $payments->links() }}
        </div>
    @endif
</div>
@endsection
