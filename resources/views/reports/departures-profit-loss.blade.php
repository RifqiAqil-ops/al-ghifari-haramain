@extends('layouts.app')

@section('title', 'Laporan Laba Rugi per Kloter - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pusat Laporan
        </a>
        <h4 class="fw-bold mb-1 text-dark">Laporan Laba Rugi per Kloter Keberangkatan</h4>
        <p class="text-muted small mb-0">Analisis kinerja laba rugi komparatif: Sebelum dan Setelah Alokasi Biaya Operasional Umum</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.profit-loss-departures', array_merge(request()->query(), ['export' => 'excel'])) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Unduh Excel
        </a>
        <a href="{{ route('reports.profit-loss-departures', array_merge(request()->query(), ['export' => 'pdf'])) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

<!-- Filter Box -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-funnel text-primary me-2"></i>Filter &amp; Parameter Laporan
            </h6>
            @if(request()->hasAny(['start_date', 'end_date', 'departure_id', 'package_id', 'status', 'allocation_method']))
                <a href="{{ route('reports.profit-loss-departures') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset Filter
                </a>
            @endif
        </div>
    </div>
    <div class="card-body p-3">
        <form action="{{ route('reports.profit-loss-departures') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Mulai Tanggal</label>
                <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Kloter / Keberangkatan</label>
                <select name="departure_id" class="form-select form-select-sm">
                    <option value="">-- Semua Kloter --</option>
                    @foreach($allDepartures as $dep)
                        <option value="{{ $dep->id }}" {{ (string)$departureId === (string)$dep->id ? 'selected' : '' }}>
                            {{ $dep->code }} - {{ $dep->name }} ({{ \Carbon\Carbon::parse($dep->departure_date)->format('d/m/Y') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Paket</label>
                <select name="package_id" class="form-select form-select-sm">
                    <option value="">-- Semua Paket --</option>
                    @foreach($packages as $pkg)
                        <option value="{{ $pkg->id }}" {{ (string)$packageId === (string)$pkg->id ? 'selected' : '' }}>
                            {{ $pkg->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label small text-muted mb-1">Status Keberangkatan</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">-- Semua Status --</option>
                    <option value="persiapan" {{ $status === 'persiapan' ? 'selected' : '' }}>Persiapan</option>
                    <option value="siap_berangkat" {{ $status === 'siap_berangkat' ? 'selected' : '' }}>Siap Berangkat</option>
                    <option value="berangkat" {{ $status === 'berangkat' ? 'selected' : '' }}>Berangkat</option>
                    <option value="dalam_perjalanan" {{ $status === 'dalam_perjalanan' ? 'selected' : '' }}>Dalam Perjalanan</option>
                    <option value="pulang" {{ $status === 'pulang' ? 'selected' : '' }}>Pulang</option>
                    <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
                </select>
            </div>
            <div class="col-md-5">
                <label class="form-label small text-muted mb-1">Metode Alokasi Beban Umum</label>
                <select name="allocation_method" class="form-select form-select-sm">
                    <option value="proporsional_jamaah" {{ $method === 'proporsional_jamaah' ? 'selected' : '' }}>
                        Proporsional Jumlah Jamaah Aktif (Standar Industri Travel)
                    </option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-filter me-1"></i> Terapkan Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Statement Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Total Pendapatan -->
    <div class="col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-success h-100">
            <span class="text-muted small fw-semibold">Total Pendapatan Kontrak</span>
            <h5 class="fw-bold text-success mb-1 mt-1">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</h5>
            <span class="text-muted small" style="font-size: 11px;">
                <i class="bi bi-people-fill me-1"></i>{{ number_format($report['total_active_jamaah']) }} Jamaah Aktif
            </span>
        </div>
    </div>
    <!-- Total Biaya Langsung -->
    <div class="col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-danger h-100">
            <span class="text-muted small fw-semibold">Total Biaya Langsung</span>
            <h5 class="fw-bold text-danger mb-1 mt-1">Rp {{ number_format($report['total_direct_expense'], 0, ',', '.') }}</h5>
            <span class="text-muted small" style="font-size: 11px;">
                <i class="bi bi-airplane-fill me-1"></i>{{ $report['total_kloter_count'] }} Kloter
            </span>
        </div>
    </div>
    <!-- Laba Sebelum Alokasi -->
    <div class="col-md-6 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 {{ $report['total_profit_before'] >= 0 ? 'border-primary' : 'border-danger' }} h-100">
            <span class="text-muted small fw-semibold">Laba Sebelum Alokasi Umum</span>
            <h5 class="fw-bold {{ $report['total_profit_before'] >= 0 ? 'text-primary' : 'text-danger' }} mb-1 mt-1">
                Rp {{ number_format($report['total_profit_before'], 0, ',', '.') }}
            </h5>
            @php
                $marginBefore = $report['total_revenue'] > 0 ? round(($report['total_profit_before'] / $report['total_revenue']) * 100, 2) : 0;
            @endphp
            <span class="badge {{ $report['total_profit_before'] >= 0 ? 'bg-primary-subtle text-primary' : 'bg-danger-subtle text-danger' }} rounded-pill" style="font-size: 11px;">
                Margin Kontribusi: {{ $marginBefore }}%
            </span>
        </div>
    </div>
    <!-- Total Biaya Umum -->
    <div class="col-md-6 col-xl-2">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 border-warning h-100">
            <span class="text-muted small fw-semibold">Biaya Operasional Umum</span>
            <h5 class="fw-bold text-dark mb-1 mt-1">Rp {{ number_format($report['total_general_expense'], 0, ',', '.') }}</h5>
            <span class="text-muted small" style="font-size: 11px;">
                Teralokasi: Rp {{ number_format($report['allocated_general_expense'], 0, ',', '.') }}
            </span>
        </div>
    </div>
    <!-- Laba Setelah Alokasi -->
    <div class="col-md-12 col-xl-3">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white border-start border-4 {{ $report['total_profit_after'] >= 0 ? 'border-success' : 'border-danger' }} h-100">
            <span class="text-muted small fw-semibold">Laba Bersih Setelah Alokasi</span>
            <h5 class="fw-bold {{ $report['total_profit_after'] >= 0 ? 'text-success' : 'text-danger' }} mb-1 mt-1">
                Rp {{ number_format($report['total_profit_after'], 0, ',', '.') }}
            </h5>
            @php
                $marginAfter = $report['total_revenue'] > 0 ? round(($report['total_profit_after'] / $report['total_revenue']) * 100, 2) : 0;
            @endphp
            <span class="badge {{ $report['total_profit_after'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill" style="font-size: 11px;">
                Margin Bersih: {{ $marginAfter }}%
            </span>
        </div>
    </div>
</div>

<!-- Informasi Kebijakan Akuntansi & Basis Periode -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-light">
    <div class="card-body p-3">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <h6 class="fw-bold mb-0 text-dark small">
                <i class="bi bi-info-circle-fill text-primary me-2"></i>Kebijakan Akuntansi &amp; Basis Penetapan Periode Laporan
            </h6>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle">
                Metode: {{ $report['allocation_method_label'] }}
            </span>
        </div>
        <div class="row g-2 text-muted small" style="font-size: 12px;">
            <div class="col-md-4">
                <strong>1. Pendapatan Kloter:</strong> Diakui berdasarkan tanggal keberangkatan (<em>departure date</em>) rombongan dan akumulasi nilai kontrak penjualan paket jamaah aktif (bukan mutasi kas piutang).
            </div>
            <div class="col-md-4">
                <strong>2. Biaya Langsung (Job-Order Costing):</strong> Seluruh beban yang terikat pada kloter (termasuk DP hotel/tiket pesawat yang dibayar sebelum keberangkatan).
            </div>
            <div class="col-md-4">
                <strong>3. Beban Umum &amp; Alokasi:</strong> Beban kantor dalam periode transaksi (<em>expense date</em>), dialokasikan proporsional dari total <strong>{{ number_format($report['total_period_active_jamaah']) }}</strong> jamaah aktif pool periode.
            </div>
        </div>
        @if(!empty($report['is_filtered_view']))
            <div class="mt-2 pt-2 border-top small text-primary d-flex align-items-center">
                <i class="bi bi-funnel-fill me-1"></i>
                <span>Tampilan difilter. Rasio alokasi kloter tetap konsisten dihitung terhadap total {{ number_format($report['total_period_active_jamaah']) }} jamaah aktif seluruh periode (tidak terdistorsi oleh filter tampilan).</span>
            </div>
        @endif
    </div>
</div>
<!-- Kloter Profit Loss Table -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <div class="d-flex align-items-center justify-content-between">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-table me-2 text-primary"></i>Tabel Analisis Laba Rugi per Kloter Keberangkatan
            </h6>
            <span class="badge bg-light text-dark border">
                Total: {{ $report['kloters']->count() }} Kloter
            </span>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 35px;" class="text-center">No</th>
                        <th>Kloter &amp; Paket</th>
                        <th>Tgl Berangkat</th>
                        <th class="text-center">Jamaah</th>
                        <th class="text-end">Pendapatan</th>
                        <th class="text-end">Biaya Langsung</th>
                        <th class="text-end">Laba Sblm Alokasi</th>
                        <th class="text-end">Alokasi Biaya Umum</th>
                        <th class="text-end">Laba Stlh Alokasi</th>
                        <th class="text-end">Laba/Jamaah</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 90px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @php $idx = 1; @endphp
                    @forelse($report['kloters'] as $item)
                        @php
                            $dep = $item['departure'];
                        @endphp
                        <tr>
                            <td class="text-center text-muted">{{ $idx++ }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $dep->code }}</div>
                                <div class="text-muted small" style="font-size: 11px;">
                                    {{ $dep->name }} &bull; {{ $dep->package->name ?? 'Paket -' }}
                                </div>
                            </td>
                            <td>
                                <div>{{ \Carbon\Carbon::parse($dep->departure_date)->format('d/m/Y') }}</div>
                                <span class="badge bg-light text-secondary border" style="font-size: 10px;">{{ ucfirst($dep->status) }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold">{{ $item['active_jamaah_count'] }}</span>
                                <div class="text-muted" style="font-size: 11px;">Aktif</div>
                            </td>
                            <td class="text-end fw-semibold text-dark">
                                Rp {{ number_format($item['revenue'], 0, ',', '.') }}
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                Rp {{ number_format($item['direct_expense'], 0, ',', '.') }}
                            </td>
                            <td class="text-end">
                                <div class="fw-bold {{ $item['profit_before_allocation'] >= 0 ? 'text-primary' : 'text-danger' }}">
                                    Rp {{ number_format($item['profit_before_allocation'], 0, ',', '.') }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $item['margin_before_allocation'] }}%
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="fw-semibold text-dark">
                                    Rp {{ number_format($item['allocated_general_expense'], 0, ',', '.') }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    Rasio: {{ $item['allocation_ratio'] }}%
                                </div>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold {{ $item['profit_after_allocation'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($item['profit_after_allocation'], 0, ',', '.') }}
                                </div>
                                <div class="text-muted" style="font-size: 11px;">
                                    {{ $item['margin_after_allocation'] }}%
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="fw-semibold {{ $item['profit_per_jamaah'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    Rp {{ number_format($item['profit_per_jamaah'], 0, ',', '.') }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($item['status_profit'] === 'untung')
                                    <span class="badge bg-success-subtle text-success border border-success px-2 py-1">Untung</span>
                                @elseif($item['status_profit'] === 'rugi')
                                    <span class="badge bg-danger-subtle text-danger border border-danger px-2 py-1">Rugi</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border px-2 py-1">Impas</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('reports.profit-loss-departures.show', $dep->id) }}" class="btn btn-sm btn-outline-primary" title="Lihat Rincian Analisis">
                                    <i class="bi bi-eye me-1"></i>Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                Tidak ada data kloter keberangkatan pada periode dan filter yang dipilih.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($report['kloters']->isNotEmpty())
                    <tfoot class="table-light fw-bold">
                        <tr style="border-top: 2px solid #dee2e6;">
                            <td colspan="3" class="text-center">TOTAL REKAPITULASI</td>
                            <td class="text-center">{{ number_format($report['total_active_jamaah']) }}</td>
                            <td class="text-end text-success">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</td>
                            <td class="text-end text-danger">Rp {{ number_format($report['total_direct_expense'], 0, ',', '.') }}</td>
                            <td class="text-end text-primary">Rp {{ number_format($report['total_profit_before'], 0, ',', '.') }}</td>
                            <td class="text-end">Rp {{ number_format($report['allocated_general_expense'], 0, ',', '.') }}</td>
                            <td class="text-end text-success">Rp {{ number_format($report['total_profit_after'], 0, ',', '.') }}</td>
                            <td class="text-end">-</td>
                            <td class="text-center">
                                @if($report['total_profit_after'] >= 0)
                                    <span class="badge bg-success text-white">Untung</span>
                                @else
                                    <span class="badge bg-danger text-white">Rugi</span>
                                @endif
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>

<!-- Section Biaya Umum Yang Dialokasikan -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
        <h6 class="fw-bold mb-0 text-dark">
            <i class="bi bi-receipt-cutoff me-2 text-warning"></i>Daftar Biaya Operasional Umum / Overhead Kantor
        </h6>
        <span class="badge bg-warning-subtle text-dark border">
            Total: Rp {{ number_format($report['total_general_expense'], 0, ',', '.') }} ({{ $report['general_expenses']->count() }} Transaksi)
        </span>
    </div>
    <div class="card-body p-0">
        @if($report['general_expenses']->isEmpty())
            <div class="p-4 text-center text-muted small">
                Tidak ada biaya operasional umum tercatat pada periode ini. Seluruh beban operasional terikat langsung pada masing-masing kloter.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 35px;" class="text-center">No</th>
                            <th>Tanggal</th>
                            <th>No. Referensi</th>
                            <th>Kategori Beban</th>
                            <th>Keterangan</th>
                            <th class="text-end">Nominal (Rp)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['general_expenses'] as $gIdx => $gExp)
                            <tr>
                                <td class="text-center text-muted">{{ $gIdx + 1 }}</td>
                                <td>{{ \Carbon\Carbon::parse($gExp->expense_date)->format('d/m/Y') }}</td>
                                <td><span class="badge bg-light text-dark border">{{ $gExp->reference_no ?: '-' }}</span></td>
                                <td class="fw-semibold text-dark">{{ $gExp->category->name ?? 'Operasional Umum' }}</td>
                                <td>{{ $gExp->description ?: '-' }}</td>
                                <td class="text-end fw-bold text-dark">Rp {{ number_format($gExp->amount, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
