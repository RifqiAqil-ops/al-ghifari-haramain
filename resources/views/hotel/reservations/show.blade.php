@extends('layouts.app')

@section('title', 'Detail Reservasi - ' . $reservation->reservation_number)

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('hotel.reservations.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Reservasi
            </a>
            <h4 class="fw-bold mb-0 text-dark">{{ $reservation->reservation_number }}</h4>
            <p class="text-muted small mb-0">Status: <span class="badge bg-info">{{ ucfirst($reservation->status) }}</span> &bull; Model: <strong>{{ ucfirst($reservation->business_model) }}</strong></p>
        </div>
        <div>
            @if($reservation->invoice)
            <a href="{{ route('hotel.invoices.show', $reservation->invoice) }}" class="btn btn-warning shadow-sm btn-sm">
                <i class="bi bi-receipt me-1"></i> Buka Invoice ({{ $reservation->invoice->invoice_number }})
            </a>
            @endif
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Rincian Kamar</h6>
                </div>
                <div class="card-body p-0">
                    <table class="table table-bordered mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Hotel</th>
                                <th>Tipe Kamar</th>
                                <th class="text-center">Stay</th>
                                <th class="text-center">Mlm</th>
                                <th class="text-center">Kmr</th>
                                <th class="text-end">Tarif Jual</th>
                                <th class="text-end">Subtotal (SAR)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($reservation->items as $item)
                            <tr>
                                <td><strong>{{ $item->hotel?->name }}</strong> ({{ $item->hotel?->city }})</td>
                                <td>{{ $item->roomType?->name }}</td>
                                <td class="text-center">{{ \Carbon\Carbon::parse($item->check_in)->format('d/m') }} - {{ \Carbon\Carbon::parse($item->check_out)->format('d/m') }}</td>
                                <td class="text-center">{{ $item->number_of_nights }}</td>
                                <td class="text-center">{{ $item->room_quantity }}</td>
                                <td class="text-end">SAR {{ number_format($item->selling_rate_per_night_sar, 2) }}</td>
                                <td class="text-end fw-bold">SAR {{ number_format($item->subtotal_selling_sar, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            <tr>
                                <th colspan="6" class="text-end">Total Penjualan:</th>
                                <th class="text-end fw-bold">SAR {{ number_format($reservation->total_selling_price_sar, 2) }}</th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark">Informasi Pemesan</h6>
                </div>
                <div class="card-body small">
                    <h6 class="fw-bold mb-1">{{ $reservation->customer?->name }}</h6>
                    <span class="text-muted d-block mb-2">{{ $reservation->customer?->company_name }}</span>
                    <div>Telepon: {{ $reservation->customer?->phone ?? '-' }}</div>
                    <div>Email: {{ $reservation->customer?->email ?? '-' }}</div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
