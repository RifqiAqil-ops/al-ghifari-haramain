@extends('layouts.app')

@section('title', 'Daftar Invoice Hotel')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-receipt text-warning me-2"></i>Invoice Hotel (Multi-Currency)</h4>
            <p class="text-muted small mb-0">Daftar Tagihan Akomodasi Hotel dengan Snapshot Kurs Terkunci</p>
        </div>
        <a href="{{ route('hotel.reservations.create') }}" class="btn btn-primary shadow-sm btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Buat Reservasi &amp; Invoice
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>No Invoice</th>
                            <th>Pelanggan / Agen</th>
                            <th>Tanggal &amp; Due Date</th>
                            <th class="text-end">Total (SAR)</th>
                            <th class="text-end">Ekuivalen IDR</th>
                            <th class="text-end">Sisa (SAR)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($invoices as $inv)
                        <tr>
                            <td>
                                <a href="{{ route('hotel.invoices.show', $inv) }}" class="fw-bold text-decoration-none">
                                    {{ $inv->invoice_number }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $inv->customer?->name }}</strong>
                                <span class="d-block text-muted small">{{ $inv->customer?->company_name }}</span>
                            </td>
                            <td>
                                <span>{{ $inv->invoice_date->format('d/m/Y') }}</span>
                                <small class="d-block text-danger">Tempo: {{ $inv->due_date->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end fw-bold text-dark">SAR {{ number_format($inv->total_amount_sar, 2, ',', '.') }}</td>
                            <td class="text-end text-muted">Rp {{ number_format($inv->total_amount_idr, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">SAR {{ number_format($inv->remaining_amount_sar, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-{{ $inv->status === 'lunas' ? 'success' : ($inv->status === 'sebagian' ? 'warning' : 'danger') }}-subtle text-{{ $inv->status === 'lunas' ? 'success' : ($inv->status === 'sebagian' ? 'warning' : 'danger') }} border rounded-pill">
                                    {{ ucfirst($inv->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('hotel.invoices.show', $inv) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 12px;">
                                    Detail
                                </a>
                                <a href="{{ route('hotel.invoices.print', $inv) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 12px;">
                                    Cetak
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada invoice hotel terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($invoices->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $invoices->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
