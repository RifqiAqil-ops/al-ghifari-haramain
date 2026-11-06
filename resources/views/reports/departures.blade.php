@extends('layouts.app')

@section('title', 'Laporan Realisasi Keberangkatan - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('reports.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pusat Laporan
        </a>
        <h4 class="fw-bold mb-1 text-dark">Laporan Realisasi Keberangkatan Rombongan</h4>
        <p class="text-muted small mb-0">Tahun: {{ $year }}</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('reports.departures', ['year' => $year, 'export' => 'excel']) }}" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Unduh Excel
        </a>
        <a href="{{ route('reports.departures', ['year' => $year, 'export' => 'pdf']) }}" class="btn btn-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Unduh PDF
        </a>
    </div>
</div>

<!-- Year Filter -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('reports.departures') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-auto">
                <span class="small fw-semibold text-secondary">Pilih Tahun:</span>
            </div>
            <div class="col-md-3">
                <select name="year" class="form-select form-select-sm">
                    @for($y = date('Y') + 1; $y >= date('Y') - 3; $y--)
                        <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                    @endfor
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Tampilkan</button>
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
                        <th style="width: 40px;">No</th>
                        <th>Kode Kloter</th>
                        <th>Paket</th>
                        <th>Jadwal Tanggal</th>
                        <th>Maskapai</th>
                        <th>Pembimbing</th>
                        <th class="text-center">Kapasitas</th>
                        <th class="text-center">Terisi</th>
                        <th class="text-center">Okupansi (%)</th>
                        <th class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($departures as $idx => $dep)
                        @php
                            $occupancy = $dep->quota > 0 ? round(($dep->registrations_count / $dep->quota) * 100) : 0;
                        @endphp
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <a href="{{ route('departures.show', $dep) }}" class="fw-bold text-dark text-decoration-none">
                                    {{ $dep->code }}
                                </a>
                            </td>
                            <td>{{ $dep->package->name ?? '-' }}</td>
                            <td>{{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '-' }} s/d {{ $dep->return_date ? $dep->return_date->format('d/m/Y') : '-' }}</td>
                            <td>{{ $dep->airline ?? '-' }} ({{ $dep->flight_number ?? '-' }})</td>
                            <td>{{ $dep->leader_name ?? '-' }}</td>
                            <td class="text-center fw-semibold">{{ $dep->quota }} Seat</td>
                            <td class="text-center fw-bold text-primary">{{ $dep->registrations_count }} Jamaah</td>
                            <td class="text-center">
                                <span class="badge {{ $occupancy >= 90 ? 'bg-success' : ($occupancy >= 50 ? 'bg-primary' : 'bg-warning text-dark') }}">
                                    {{ $occupancy }}%
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.75rem;">
                                    {{ str_replace('_', ' ', $dep->status) }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                Tidak ada data keberangkatan pada tahun {{ $year }}.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
