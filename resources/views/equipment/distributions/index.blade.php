@extends('layouts.app')

@section('title', 'Distribusi Perlengkapan Jamaah')
@section('header_title', 'Distribusi Perlengkapan Jamaah')

@section('content')
<!-- Metrics Summary -->
<div class="row g-3 mb-4">
    <!-- Stat 1: Total Distribusi -->
    <div class="col-sm-6 col-xl-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-forest-green text-royal-gold rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-box-arrow-right fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Transaksi Distribusi</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalDistributions, 0, ',', '.') }} <small class="text-muted fs-6 fw-normal">Transaksi</small></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 2: Total Barang Diserahkan -->
    <div class="col-sm-6 col-xl-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-bag-check fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Total Perlengkapan Diserahkan</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalItemsGiven, 0, ',', '.') }} <small class="text-muted fs-6 fw-normal">Item Fisik</small></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Stat 3: Total Jamaah Terlayani -->
    <div class="col-sm-6 col-xl-4">
        <div class="card card-custom p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success rounded-3 p-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                    <i class="bi bi-people-fill fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Jamaah Telah Menerima</span>
                    <h4 class="fw-bold mb-0 text-dark">{{ number_format($totalJamaahServed, 0, ',', '.') }} <small class="text-muted fs-6 fw-normal">Jamaah</small></h4>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card card-custom">
    <div class="card-body p-4">
        <!-- Header & Action Button -->
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
            <div>
                <h5 class="fw-bold mb-1 text-dark">Riwayat Transaksi Distribusi Perlengkapan</h5>
                <p class="text-muted small mb-0">Catatan penyerahan perlengkapan kepada jamaah secara bertahap atau sekaligus</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('equipment.mutations') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-clock-history me-1"></i> Audit Mutasi Stok
                </a>
                @can('equipment.distribute')
                <a href="{{ route('equipment.distributions.create') }}" class="btn btn-gold text-dark fw-semibold">
                    <i class="bi bi-plus-circle me-1"></i> + Distribusikan Perlengkapan
                </a>
                @endcan
            </div>
        </div>

        <!-- Filter Form -->
        <form action="{{ route('equipment.distributions.index') }}" method="GET" class="row g-2 mb-4">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control" placeholder="Cari no. distribusi / nama jamaah..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="departure_id" class="form-select">
                    <option value="">-- Semua Keberangkatan --</option>
                    @foreach($departures as $dep)
                        <option value="{{ $dep->id }}" {{ $departureId == $dep->id ? 'selected' : '' }}>
                            {{ $dep->departure_code ?? 'DEP-'.$dep->id }} - {{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '-' }} ({{ $dep->package->name ?? 'Paket' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">-- Semua Status --</option>
                    <option value="diserahkan" {{ $status == 'diserahkan' ? 'selected' : '' }}>Diserahkan</option>
                    <option value="dibatalkan" {{ $status == 'dibatalkan' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="date" name="start_date" class="form-control" title="Dari Tanggal" value="{{ $startDate }}">
                    <span class="input-group-text bg-light text-muted">-</span>
                    <input type="date" name="end_date" class="form-control" title="Sampai Tanggal" value="{{ $endDate }}">
                </div>
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-forest text-white w-100" title="Terapkan Filter">
                    <i class="bi bi-funnel"></i>
                </button>
                @if($search || $departureId || $status || $startDate || $endDate)
                    <a href="{{ route('equipment.distributions.index') }}" class="btn btn-outline-danger" title="Reset Filter">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </form>

        <!-- Table -->
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th style="width: 130px;">No. Distribusi</th>
                        <th style="width: 110px;">Tanggal</th>
                        <th>Nama Jamaah</th>
                        <th>Keberangkatan / Paket</th>
                        <th>Rincian Barang Diterima</th>
                        <th style="width: 140px;">Penerima / Petugas</th>
                        <th style="width: 100px;">Status</th>
                        <th style="width: 90px;" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($distributions as $dist)
                        <tr>
                            <td>
                                <a href="{{ route('equipment.distributions.show', $dist) }}" class="fw-bold text-forest text-decoration-none">
                                    {{ $dist->distribution_number }}
                                </a>
                            </td>
                            <td>
                                <span class="small">{{ $dist->distribution_date ? $dist->distribution_date->format('d/m/Y') : '-' }}</span>
                            </td>
                            <td>
                                @if($dist->jamaah)
                                    <div class="fw-semibold text-dark">
                                        <a href="{{ route('jamaah.show', $dist->jamaah) }}" class="text-decoration-none text-dark">
                                            {{ $dist->jamaah->name }}
                                        </a>
                                    </div>
                                    <small class="text-muted d-block">NIK: {{ $dist->jamaah->nik ?? '-' }}</small>
                                @else
                                    <span class="text-muted fst-italic">Data jamaah tidak tersedia</span>
                                @endif
                            </td>
                            <td>
                                @if($dist->departure)
                                    <span class="badge bg-light text-dark border">
                                        {{ $dist->departure->departure_code ?? 'DEP-'.$dist->departure->id }}
                                    </span>
                                    <small class="d-block text-muted">
                                        {{ $dist->departure->departure_date ? $dist->departure->departure_date->format('d/m/Y') : '' }}
                                    </small>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($dist->items as $item)
                                        <span class="badge bg-forest-green text-royal-gold py-1 px-2 border" style="font-size: 0.75rem;">
                                            {{ $item->equipment->name ?? 'Barang' }}: <strong>{{ $item->quantity }}</strong> {{ $item->equipment->unit ?? 'pcs' }}
                                        </span>
                                    @endforeach
                                </div>
                                @if($dist->notes)
                                    <small class="text-muted d-block mt-1 fst-italic text-truncate" style="max-width: 250px;" title="{{ $dist->notes }}">
                                        Catatan: {{ $dist->notes }}
                                    </small>
                                @endif
                            </td>
                            <td>
                                <div class="small fw-semibold text-dark">{{ $dist->received_by ?? '-' }}</div>
                                <small class="text-muted d-block">Oleh: {{ $dist->officer->name ?? 'Petugas' }}</small>
                            </td>
                            <td>
                                @if($dist->status === 'diserahkan')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Diserahkan
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <i class="bi bi-x-circle me-1"></i> Dibatalkan
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <a href="{{ route('equipment.distributions.show', $dist) }}" class="btn btn-sm btn-outline-primary" title="Lihat Detail Transaksi">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                <p class="mb-2">Belum ada transaksi distribusi perlengkapan yang sesuai.</p>
                                @can('equipment.distribute')
                                    <a href="{{ route('equipment.distributions.create') }}" class="btn btn-sm btn-gold text-dark fw-semibold mt-1">
                                        <i class="bi bi-plus-circle me-1"></i> Mulai Distribusi Baru
                                    </a>
                                @endcan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($distributions->hasPages())
            <div class="mt-4 d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Menampilkan {{ $distributions->firstItem() }} - {{ $distributions->lastItem() }} dari total {{ $distributions->total() }} data
                </small>
                <div>
                    {{ $distributions->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
