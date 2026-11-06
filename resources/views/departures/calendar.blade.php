@extends('layouts.app')

@section('title', 'Kalender Keberangkatan - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Kalender & Timeline Keberangkatan</h4>
        <p class="text-muted small mb-0">Visualisasi jadwal rombongan keberangkatan Haji dan Umrah</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('departures.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-list-ul me-1"></i> Tampilan Tabel
        </a>
        <a href="{{ route('departures.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Tambah Keberangkatan
        </a>
    </div>
</div>

@php
    $groupedDepartures = $departures->groupBy(function($dep) {
        return $dep->departure_date ? $dep->departure_date->format('F Y') : 'Tanpa Tanggal';
    });
@endphp

@if($departures->isEmpty())
    <div class="card border-0 shadow-sm rounded-3 py-5 text-center">
        <div class="card-body">
            <i class="bi bi-calendar-x text-muted display-4 mb-3 d-block"></i>
            <h5 class="fw-semibold text-secondary">Belum ada jadwal keberangkatan aktif.</h5>
            <p class="text-muted small">Buat jadwal rombongan pertama untuk melihat kalender operasional.</p>
            <a href="{{ route('departures.create') }}" class="btn btn-primary mt-2">
                <i class="bi bi-plus-circle me-1"></i> Buat Jadwal Baru
            </a>
        </div>
    </div>
@else
    @foreach($groupedDepartures as $monthName => $depList)
        <div class="mb-5">
            <div class="d-flex align-items-center gap-2 mb-3 border-bottom pb-2">
                <i class="bi bi-calendar2-month text-primary fs-5"></i>
                <h5 class="fw-bold mb-0 text-dark">{{ $monthName }}</h5>
                <span class="badge bg-primary-subtle text-primary rounded-pill ms-2">{{ $depList->count() }} Rombongan</span>
            </div>

            <div class="row g-3">
                @foreach($depList as $dep)
                    <div class="col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm rounded-3 h-100 hover-shadow transition">
                            <div class="card-header bg-white border-bottom pt-3 pb-2 d-flex justify-content-between align-items-center">
                                <span class="badge bg-light text-dark border fw-bold">{{ $dep->code }}</span>
                                <span class="badge 
                                    @if($dep->status == 'selesai') bg-success
                                    @elseif($dep->status == 'berangkat' || $dep->status == 'dalam_perjalanan') bg-primary
                                    @elseif($dep->status == 'siap_berangkat') bg-info text-dark
                                    @else bg-warning text-dark @endif rounded-pill px-2 py-1 text-uppercase small">
                                    {{ str_replace('_', ' ', $dep->status) }}
                                </span>
                            </div>
                            <div class="card-body">
                                <h6 class="fw-bold text-dark mb-1">{{ $dep->package->name ?? 'Paket' }}</h6>
                                <p class="text-muted small mb-3">
                                    <span class="badge bg-secondary-subtle text-secondary me-1">{{ strtoupper($dep->package->type ?? 'UMRAH') }}</span>
                                    {{ $dep->airline ?? 'Maskapai Charter' }} ({{ $dep->flight_number ?? '-' }})
                                </p>

                                <div class="bg-light p-3 rounded-3 mb-3 small">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted"><i class="bi bi-airplane-engines me-1"></i> Berangkat:</span>
                                        <strong class="text-dark">{{ $dep->departure_date ? $dep->departure_date->format('d M Y') : '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted"><i class="bi bi-house-door me-1"></i> Kembali:</span>
                                        <strong class="text-dark">{{ $dep->return_date ? $dep->return_date->format('d M Y') : '-' }}</strong>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span class="text-muted"><i class="bi bi-person-badge me-1"></i> Pembimbing:</span>
                                        <span class="text-dark">{{ $dep->leader_name ?? 'Belum ditentukan' }}</span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-between align-items-center small">
                                    <span class="text-muted">Manifest Terisi:</span>
                                    <span class="fw-bold text-dark">{{ $dep->registrations->count() ?? 0 }} / {{ $dep->quota }} Seat</span>
                                </div>
                            </div>
                            <div class="card-footer bg-white border-top py-2 d-flex justify-content-between">
                                <a href="{{ route('departures.show', $dep) }}" class="btn btn-sm btn-link text-decoration-none px-0 fw-semibold">
                                    Lihat Detail & Manifest <i class="bi bi-arrow-right"></i>
                                </a>
                                <a href="{{ route('departures.manifest', $dep) }}" target="_blank" class="btn btn-sm btn-outline-danger" title="Download PDF Manifest">
                                    <i class="bi bi-file-pdf"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endif
@endsection
