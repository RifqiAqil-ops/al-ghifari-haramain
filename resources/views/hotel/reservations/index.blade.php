@extends('layouts.app')

@section('title', 'Daftar Reservasi Hotel')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-calendar-check text-primary me-2"></i>Reservasi Akomodasi Hotel</h4>
            <p class="text-muted small mb-0">Kelola Pemesanan Kamar, Hotel Mitra, dan Status Konfirmasi</p>
        </div>
        <a href="{{ route('hotel.reservations.create') }}" class="btn btn-primary shadow-sm btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Buat Reservasi Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>No Reservasi</th>
                            <th>Pelanggan</th>
                            <th>Model Bisnis</th>
                            <th>Periode Check-in</th>
                            <th>Total Kamar / Malam</th>
                            <th class="text-end">Total Jual (SAR)</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reservations as $res)
                        <tr>
                            <td>
                                <a href="{{ route('hotel.reservations.show', $res) }}" class="fw-bold text-decoration-none">
                                    {{ $res->reservation_number }}
                                </a>
                            </td>
                            <td>
                                <strong>{{ $res->customer?->name }}</strong>
                                <span class="d-block text-muted small">{{ $res->customer?->company_name }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($res->business_model) }}</span></td>
                            <td>{{ \Carbon\Carbon::parse($res->check_in_date)->format('d/m/Y') }} &rarr; {{ \Carbon\Carbon::parse($res->check_out_date)->format('d/m/Y') }}</td>
                            <td>{{ $res->total_rooms }} kamar ({{ $res->total_nights }} malam)</td>
                            <td class="text-end fw-bold text-dark">SAR {{ number_format($res->total_selling_price_sar, 2, ',', '.') }}</td>
                            <td class="text-center">
                                <span class="badge bg-info-subtle text-info border rounded-pill">{{ ucfirst($res->status) }}</span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('hotel.reservations.show', $res) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 12px;">Detail</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">Belum ada reservasi hotel terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($reservations->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $reservations->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
