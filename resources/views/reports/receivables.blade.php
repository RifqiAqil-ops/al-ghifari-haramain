@extends('layouts.app')

@section('title', 'Laporan Piutang & Sisa Tagihan - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pusat Laporan
        </a>
        <h4 class="fw-bold mb-1 text-dark">Laporan Piutang & Sisa Tagihan Jamaah</h4>
        <p class="text-muted small mb-0">Daftar jamaah dengan status tagihan belum bayar atau sebagian</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.receivables', ['export' => 'excel']) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Unduh Excel
        </a>
        <a href="{{ route('reports.receivables', ['export' => 'pdf']) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

<!-- Total Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-warning-subtle border-start border-4 border-warning p-3">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <span class="text-warning small fw-semibold">Total Nilai Piutang Belum Tertagih:</span>
            <h3 class="fw-bold text-dark mb-0 mt-1">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</h3>
        </div>
        <span class="badge bg-warning text-dark rounded-pill px-3 py-2 fs-6">{{ $invoices->count() }} Invoice</span>
    </div>
</div>

<!-- Table -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>No Invoice</th>
                        <th>Nama Jamaah</th>
                        <th>No Telepon</th>
                        <th>Paket / Kloter</th>
                        <th>Jatuh Tempo</th>
                        <th class="text-end">Total Tagihan</th>
                        <th class="text-end">Terbayar</th>
                        <th class="text-end">Sisa Piutang</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $idx => $inv)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <a href="{{ route('finance.invoices.show', $inv) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $inv->registration->jamaah->name ?? '-' }}</strong>
                            </td>
                            <td>
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $inv->registration->jamaah->phone ?? '')) }}" target="_blank" class="text-success text-decoration-none small">
                                    <i class="bi bi-whatsapp me-1"></i>{{ $inv->registration->jamaah->phone ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <span class="small text-dark">{{ $inv->registration->package->name ?? '-' }}</span>
                                <span class="text-muted small d-block">{{ $inv->registration->departure->code ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="small {{ $inv->due_date && $inv->due_date->isPast() ? 'text-danger fw-bold' : 'text-muted' }}">
                                    {{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}
                                </span>
                            </td>
                            <td class="text-end">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
                            <td class="text-end">
                                <a href="{{ route('finance.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-check-circle fs-2 text-success d-block mb-2"></i>
                                Tidak ada tunggakan piutang jamaah saat ini. Seluruh tagihan telah lunas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
