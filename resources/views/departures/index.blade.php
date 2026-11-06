@extends('layouts.app')

@section('title', 'Jadwal & Manifest Keberangkatan')
@section('header_title', 'Manajemen Jadwal Keberangkatan')

@section('content')
<div class="card card-custom p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-airplane-fill text-royal-gold me-2"></i> Jadwal Kloter Keberangkatan &amp; Manifest
            </h5>
            <p class="text-muted small mb-0">Kelola jadwal penerbangan, hotel akomodasi Saudi, pembimbing, dan manifes penumpang</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('departures.calendar') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-calendar3 me-1"></i> Kalender Jadwal
            </a>
            <a href="{{ route('departures.create') }}" class="btn btn-sm btn-forest-green">
                <i class="bi bi-plus-circle me-1"></i> Tambah Jadwal Baru
            </a>
        </div>
    </div>

    <!-- Filter & Search -->
    <form action="{{ route('departures.index') }}" method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari kode keberangkatan, maskapai, tour leader..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="package_id" class="form-select form-select-sm">
                <option value="">Semua Paket</option>
                @foreach($packages as $p)
                    <option value="{{ $p->id }}" {{ $packageId == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="persiapan" {{ $status === 'persiapan' ? 'selected' : '' }}>Persiapan</option>
                <option value="siap_berangkat" {{ $status === 'siap_berangkat' ? 'selected' : '' }}>Siap Berangkat</option>
                <option value="berangkat" {{ $status === 'berangkat' ? 'selected' : '' }}>Berangkat</option>
                <option value="dalam_perjalanan" {{ $status === 'dalam_perjalanan' ? 'selected' : '' }}>Dalam Perjalanan</option>
                <option value="pulang" {{ $status === 'pulang' ? 'selected' : '' }}>Kepulangan</option>
                <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-forest-green flex-grow-1">Filter</button>
            @if($search || $packageId || $status)
                <a href="{{ route('departures.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Kode Kloter</th>
                    <th>Paket Perjalanan</th>
                    <th>Tanggal Berangkat &amp; Pulang</th>
                    <th>Penerbangan / Rute</th>
                    <th>Keterisian Kuota</th>
                    <th>Tour Leader / Muthawwif</th>
                    <th>Status Perjalanan</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departures as $dep)
                    <tr>
                        <td class="fw-bold font-monospace text-forest-green small">
                            {{ $dep->code }}
                        </td>
                        <td>
                            <strong class="text-dark small d-block">{{ $dep->package->name }}</strong>
                            <span class="badge bg-light text-muted border" style="font-size: 0.65rem;">
                                {{ ucfirst($dep->package->type) }}
                            </span>
                        </td>
                        <td class="small">
                            <div><i class="bi bi-calendar-check text-royal-gold me-1"></i><strong>{{ $dep->departure_date->translatedFormat('d M Y') }}</strong></div>
                            <small class="text-muted"><i class="bi bi-calendar-x text-muted me-1"></i>s/d {{ $dep->return_date->translatedFormat('d M Y') }}</small>
                        </td>
                        <td class="small">
                            <div class="fw-semibold text-dark">{{ $dep->airline }} ({{ $dep->flight_number ?? 'Charter' }})</div>
                            <small class="text-muted">{{ $dep->origin_airport ?? 'CGK' }} &rarr; {{ $dep->destination_airport ?? 'MED' }}</small>
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-dark border fw-bold">
                                    {{ $dep->registrations_count }} / {{ $dep->quota }} Pax
                                </span>
                            </div>
                        </td>
                        <td class="small">
                            @if($dep->leader_name)
                                <div class="fw-semibold text-dark">{{ $dep->leader_name }}</div>
                                <small class="text-muted">{{ $dep->leader_phone ?? '-' }}</small>
                            @else
                                <span class="text-muted fst-italic">Belum ditugaskan</span>
                            @endif
                        </td>
                        <td>
                            @php
                                $badgeClass = match($dep->status) {
                                    'siap_berangkat', 'selesai' => 'badge-subtle-success',
                                    'berangkat', 'dalam_perjalanan' => 'badge-subtle-info',
                                    'persiapan', 'pulang' => 'badge-subtle-warning',
                                    default => 'badge-subtle-danger',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }} px-2 py-1">
                                {{ $dep->status_label }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('departures.show', $dep) }}" class="btn btn-light border text-forest-green" title="Detail & Manifest">
                                    <i class="bi bi-card-list"></i> Manifest
                                </a>
                                <a href="{{ route('departures.edit', $dep) }}" class="btn btn-light border text-primary" title="Edit Jadwal">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('departures.manifest-pdf', $dep) }}" target="_blank" class="btn btn-light border text-danger" title="Cetak PDF">
                                    <i class="bi bi-file-earmark-pdf"></i>
                                </a>
                                @can('departure.delete')
                                <form action="{{ route('departures.destroy', $dep) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal keberangkatan {{ $dep->code }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light border text-danger" title="Hapus Keberangkatan">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-airplane fs-1 d-block mb-2 text-muted"></i>
                            Tidak ada jadwal keberangkatan yang sesuai filter.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $departures->links() }}
    </div>
</div>
@endsection
