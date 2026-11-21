@extends('layouts.app')

@section('title', 'Detail Laba Rugi Kloter ' . $departure->code . ' - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('reports.profit-loss-departures') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kloter
        </a>
        <h4 class="fw-bold mb-1 text-dark">Detail Analisis Laba Rugi: {{ $departure->name }} ({{ $departure->code }})</h4>
        <p class="text-muted small mb-0">
            Paket: {{ $departure->package->name ?? '-' }} &bull;
            Keberangkatan: {{ \Carbon\Carbon::parse($departure->departure_date)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($departure->return_date)->format('d F Y') }}
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.profit-loss-departures.show', array_merge(['departure' => $departure->id], request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF Detail
        </a>
    </div>
</div>

<!-- Header Info Kloter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-3 border-end">
                <span class="text-muted small">Kode &amp; Nama Rombongan</span>
                <h5 class="fw-bold text-dark mb-1">{{ $departure->code }}</h5>
                <span class="text-muted small">{{ $departure->name }}</span>
            </div>
            <div class="col-md-3 border-end">
                <span class="text-muted small">Jadwal Keberangkatan</span>
                <div class="fw-semibold text-dark">
                    <i class="bi bi-calendar3 me-1 text-primary"></i>
                    {{ \Carbon\Carbon::parse($departure->departure_date)->format('d M Y') }}
                </div>
                <span class="badge bg-light text-secondary border mt-1">Status: {{ ucfirst($departure->status) }}</span>
            </div>
            <div class="col-md-3 border-end">
                <span class="text-muted small">Maskapai &amp; Hotel</span>
                <div class="small fw-semibold text-dark">
                    <i class="bi bi-airplane me-1 text-info"></i> {{ $departure->airline ?: '-' }}
                </div>
                <div class="small text-muted">
                    <i class="bi bi-building me-1"></i> {{ $departure->hotel_makkah ?: '-' }}
                </div>
            </div>
            <div class="col-md-3 text-md-center">
                <span class="text-muted small">Okupansi Jamaah Aktif</span>
                <h4 class="fw-bold text-primary mb-0 mt-1">
                    {{ $detail['item']['active_jamaah_count'] }} <span class="fs-6 fw-normal text-muted">/ {{ $departure->quota }} Kursi</span>
                </h4>
                <div class="small text-muted">
                    Terdaftar: {{ $detail['item']['total_registered_count'] }} jamaah
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Metric Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success h-100">
            <span class="text-muted small fw-semibold">Pendapatan Kontrak</span>
            <h5 class="fw-bold text-success mb-0 mt-1">Rp {{ number_format($detail['item']['revenue'], 0, ',', '.') }}</h5>
            <span class="text-muted" style="font-size: 11px;">Basis nilai paket</span>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-danger h-100">
            <span class="text-muted small fw-semibold">Biaya Langsung</span>
            <h5 class="fw-bold text-danger mb-0 mt-1">Rp {{ number_format($detail['item']['direct_expense'], 0, ',', '.') }}</h5>
            <span class="text-muted" style="font-size: 11px;">Pengeluaran kloter</span>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-primary h-100">
            <span class="text-muted small fw-semibold">Laba Sblm Alokasi</span>
            <h5 class="fw-bold text-primary mb-0 mt-1">Rp {{ number_format($detail['item']['profit_before_allocation'], 0, ',', '.') }}</h5>
            <span class="badge bg-primary-subtle text-primary" style="font-size: 11px;">Margin: {{ $detail['item']['margin_before_allocation'] }}%</span>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning h-100">
            <span class="text-muted small fw-semibold">Beban Umum Teralokasi</span>
            <h5 class="fw-bold text-dark mb-0 mt-1">Rp {{ number_format($detail['item']['allocated_general_expense'], 0, ',', '.') }}</h5>
            <span class="badge bg-warning-subtle text-dark" style="font-size: 11px;">Porsi: {{ $detail['item']['allocation_ratio'] }}%</span>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 {{ $detail['item']['profit_after_allocation'] >= 0 ? 'border-success' : 'border-danger' }} h-100">
            <span class="text-muted small fw-semibold">Laba Stlh Alokasi</span>
            <h5 class="fw-bold {{ $detail['item']['profit_after_allocation'] >= 0 ? 'text-success' : 'text-danger' }} mb-0 mt-1">
                Rp {{ number_format($detail['item']['profit_after_allocation'], 0, ',', '.') }}
            </h5>
            <span class="badge {{ $detail['item']['profit_after_allocation'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}" style="font-size: 11px;">
                Margin: {{ $detail['item']['margin_after_allocation'] }}%
            </span>
        </div>
    </div>
    <div class="col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-info h-100">
            <span class="text-muted small fw-semibold">Laba per Jamaah</span>
            <h5 class="fw-bold {{ $detail['item']['profit_per_jamaah'] >= 0 ? 'text-success' : 'text-danger' }} mb-0 mt-1">
                Rp {{ number_format($detail['item']['profit_per_jamaah'], 0, ',', '.') }}
            </h5>
            <span class="text-muted" style="font-size: 11px;">Biaya: Rp {{ number_format($detail['item']['cost_per_jamaah'], 0, ',', '.') }}/pax</span>
        </div>
    </div>
</div>

<!-- BAGIAN A: SEBELUM ALOKASI BIAYA OPERASIONAL UMUM -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <span class="badge bg-primary me-2">BAGIAN A</span>
            <h6 class="fw-bold d-inline-block mb-0 text-dark">Laba Rugi Kloter Sebelum Alokasi Biaya Umum (Direct Margin)</h6>
        </div>
        <span class="text-muted small">Versi 1: Pendapatan Kontrak dikurangi Beban Langsung</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            <!-- Rincian Biaya Langsung per Kategori -->
            <div class="col-lg-5">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-pie-chart text-primary me-2"></i>Proporsi Biaya Langsung per Kategori</h6>
                @if($detail['item']['direct_expenses_by_cat']->isEmpty())
                    <p class="text-muted small">Belum ada pengeluaran langsung yang dicatat untuk kloter ini.</p>
                @else
                    <div class="list-group list-group-flush border rounded-3">
                        @foreach($detail['item']['direct_expenses_by_cat'] as $cat)
                            @php
                                $catShare = $detail['item']['direct_expense'] > 0 ? round(($cat['total_amount'] / $detail['item']['direct_expense']) * 100, 1) : 0;
                            @endphp
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3">
                                <div>
                                    <div class="fw-semibold text-dark small">{{ $cat['category_name'] }}</div>
                                    <span class="text-muted" style="font-size: 11px;">{{ $cat['count'] }} transaksi</span>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-danger small">Rp {{ number_format($cat['total_amount'], 0, ',', '.') }}</div>
                                    <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $catShare }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Ringkasan Komparasi Margin Bagian A -->
                <div class="mt-4 p-3 bg-light rounded-3">
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Total Nilai Kontrak Jamaah Aktif:</span>
                        <strong class="text-success">Rp {{ number_format($detail['item']['revenue'], 0, ',', '.') }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span>Total Biaya Langsung Kloter:</span>
                        <strong class="text-danger">Rp {{ number_format($detail['item']['direct_expense'], 0, ',', '.') }}</strong>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Laba Sebelum Alokasi:</span>
                        <span class="{{ $detail['item']['profit_before_allocation'] >= 0 ? 'text-primary' : 'text-danger' }}">
                            Rp {{ number_format($detail['item']['profit_before_allocation'], 0, ',', '.') }}
                            ({{ $detail['item']['margin_before_allocation'] }}%)
                        </span>
                    </div>
                </div>
            </div>

            <!-- Tabel Transaksi Pengeluaran Langsung -->
            <div class="col-lg-7">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-list-check text-primary me-2"></i>Daftar Pengeluaran Langsung Kloter</h6>
                @if($detail['item']['direct_expenses_list']->isEmpty())
                    <div class="p-4 text-center text-muted small border rounded-3">
                        Tidak ada transaksi pengeluaran langsung tercatat.
                    </div>
                @else
                    <div class="table-responsive border rounded-3" style="max-height: 380px;">
                        <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>No. Ref</th>
                                    <th>Kategori</th>
                                    <th>Keterangan</th>
                                    <th class="text-end">Jumlah (Rp)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detail['item']['direct_expenses_list'] as $exp)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($exp->expense_date)->format('d/m/Y') }}</td>
                                        <td><span class="badge bg-light text-dark border">{{ $exp->reference_no ?: '-' }}</span></td>
                                        <td>{{ $exp->category->name ?? 'Operasional' }}</td>
                                        <td class="text-truncate" style="max-width: 150px;" title="{{ $exp->description }}">{{ $exp->description ?: '-' }}</td>
                                        <td class="text-end fw-semibold text-danger">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- BAGIAN B: SETELAH ALOKASI BIAYA OPERASIONAL UMUM -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <div>
            <span class="badge bg-success me-2">BAGIAN B</span>
            <h6 class="fw-bold d-inline-block mb-0 text-dark">Laba Rugi Kloter Setelah Alokasi Biaya Umum (Net Profit Kloter)</h6>
        </div>
        <span class="text-muted small">Versi 2: Menanggung Beban Kantor Proporsional</span>
    </div>
    <div class="card-body p-4">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-calculator text-primary me-2"></i>Formula Alokasi Biaya Umum</h6>
                    <p class="small text-muted mb-2">
                        Biaya operasional kantor/umum didistribusikan secara adil berdasarkan proporsi jamaah aktif pada kloter ini terhadap seluruh jamaah aktif pada periode yang sama:
                    </p>
                    <div class="p-2 bg-white rounded border small font-monospace mb-3">
                        Alokasi Kloter = ({{ $detail['item']['active_jamaah_count'] }} pax / {{ $detail['total_report']['total_active_jamaah'] }} pax) &times; Rp {{ number_format($detail['total_report']['total_general_expense'], 0, ',', '.') }}
                        <br>= <strong>Rp {{ number_format($detail['item']['allocated_general_expense'], 0, ',', '.') }}</strong> ({{ $detail['item']['allocation_ratio'] }}%)
                    </div>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-1"><i class="bi bi-check2 text-success me-1"></i> Beban Langsung: <strong>Rp {{ number_format($detail['item']['direct_expense'], 0, ',', '.') }}</strong></li>
                        <li class="mb-1"><i class="bi bi-plus-circle text-primary me-1"></i> Beban Umum Teralokasi: <strong>Rp {{ number_format($detail['item']['allocated_general_expense'], 0, ',', '.') }}</strong></li>
                        <li class="fw-bold text-dark"><i class="bi bi-arrow-right text-dark me-1"></i> Total Beban Hakiki: <strong>Rp {{ number_format($detail['item']['total_expense_after'], 0, ',', '.') }}</strong></li>
                    </ul>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card border-0 shadow-sm p-4 text-center {{ $detail['item']['profit_after_allocation'] >= 0 ? 'bg-success-subtle text-success border border-success' : 'bg-danger-subtle text-danger border border-danger' }} rounded-3">
                    <span class="small text-uppercase fw-bold">Laba Bersih Akhir Kloter</span>
                    <h2 class="fw-bold my-2">Rp {{ number_format($detail['item']['profit_after_allocation'], 0, ',', '.') }}</h2>
                    <div class="d-flex justify-content-center gap-3">
                        <span class="badge bg-white text-dark border">Margin Bersih: {{ $detail['item']['margin_after_allocation'] }}%</span>
                        <span class="badge bg-white text-dark border">Laba / Jamaah: Rp {{ number_format($detail['item']['profit_per_jamaah'], 0, ',', '.') }}</span>
                        <span class="badge bg-white text-dark border">Biaya / Jamaah: Rp {{ number_format($detail['item']['cost_per_jamaah'], 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BAGIAN C: DAFTAR JAMAAH AKTIF KLOTER -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-people me-2 text-primary"></i>Daftar Jamaah Aktif Kloter &amp; Nilai Kontrak Penjualan
        </h6>
        <span class="badge bg-primary-subtle text-primary border border-primary">
            Total: {{ $detail['jamaah_list']->count() }} Jamaah Aktif
        </span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35px;" class="text-center">No</th>
                        <th>No. Registrasi</th>
                        <th>Nama Jamaah</th>
                        <th>Tipe Kamar</th>
                        <th class="text-end">Nilai Kontrak (Pendapatan)</th>
                        <th class="text-end">Kas Masuk (Terbayar)</th>
                        <th class="text-end">Sisa Piutang</th>
                        <th class="text-center">Status Pembayaran</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($detail['jamaah_list'] as $jIdx => $reg)
                        @php
                            $paidAmount = $reg->invoice ? $reg->invoice->validPayments->sum('amount') : 0;
                            $remaining = $reg->invoice ? $reg->invoice->remaining_amount : max(0, $reg->final_price - $paidAmount);
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $jIdx + 1 }}</td>
                            <td><span class="fw-bold">{{ $reg->registration_number }}</span></td>
                            <td>
                                <div class="fw-semibold text-dark">{{ $reg->jamaah->name ?? '-' }}</div>
                                <span class="text-muted small" style="font-size: 11px;">NIK: {{ $reg->jamaah->nik ?? '-' }}</span>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ ucfirst($reg->room_type) }}</span></td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($reg->final_price, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold text-dark">Rp {{ number_format($paidAmount, 0, ',', '.') }}</td>
                            <td class="text-end fw-semibold {{ $remaining > 0 ? 'text-danger' : 'text-muted' }}">
                                Rp {{ number_format($remaining, 0, ',', '.') }}
                            </td>
                            <td class="text-center">
                                @if($reg->payment_status === 'lunas')
                                    <span class="badge bg-success-subtle text-success border border-success">Lunas</span>
                                @elseif($reg->payment_status === 'sebagian')
                                    <span class="badge bg-warning-subtle text-dark border border-warning">Sebagian</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger">Belum Bayar</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Tidak ada data jamaah aktif pada kloter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($detail['jamaah_list']->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-center">TOTAL NILAI KONTRAK PENDAPATAN</td>
                            <td class="text-end text-success">Rp {{ number_format($detail['item']['revenue'], 0, ',', '.') }}</td>
                            <td class="text-end">
                                Rp {{ number_format($detail['jamaah_list']->sum(fn($r) => $r->invoice ? $r->invoice->validPayments->sum('amount') : 0), 0, ',', '.') }}
                            </td>
                            <td class="text-end text-danger">
                                Rp {{ number_format($detail['jamaah_list']->sum(fn($r) => $r->invoice ? $r->invoice->remaining_amount : 0), 0, ',', '.') }}
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
