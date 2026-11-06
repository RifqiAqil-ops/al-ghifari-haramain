@extends('layouts.app')

@section('title', 'Paket Haji & Umrah')
@section('header_title', 'Katalog Paket Haji & Umrah')

@section('content')
<div class="card card-custom p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-box-seam-fill text-royal-gold me-2"></i> Master Paket Perjalanan Ibadah
            </h5>
            <p class="text-muted small mb-0">Kelola master penawaran paket Umrah Reguler, Plus, Haji Khusus &amp; Furoda</p>
        </div>
        <a href="{{ route('packages.create') }}" class="btn btn-sm btn-forest-green">
            <i class="bi bi-plus-circle me-1"></i> Tambah Paket Baru
        </a>
    </div>

    <!-- Filter & Search -->
    <form action="{{ route('packages.index') }}" method="GET" class="row g-2 mb-4">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama paket, kode, maskapai, hotel..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="type" class="form-select form-select-sm">
                <option value="">Semua Jenis Program</option>
                <option value="umrah" {{ $type === 'umrah' ? 'selected' : '' }}>Program Umrah</option>
                <option value="haji" {{ $type === 'haji' ? 'selected' : '' }}>Program Haji</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="draft" {{ $status === 'draft' ? 'selected' : '' }}>Draf</option>
                <option value="penuh" {{ $status === 'penuh' ? 'selected' : '' }}>Penuh</option>
                <option value="selesai" {{ $status === 'selesai' ? 'selected' : '' }}>Selesai</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-forest-green flex-grow-1">Filter</button>
            @if($search || $type || $status)
                <a href="{{ route('packages.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
        </div>
    </form>

    <!-- Package Grid -->
    <div class="row g-4 mb-4">
        @forelse($packages as $pkg)
            <div class="col-md-6 col-xl-4">
                <div class="card card-custom h-100 overflow-hidden border">
                    <div class="position-relative bg-forest-green text-white p-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge bg-royal-gold text-dark font-monospace fw-bold">{{ $pkg->code }}</span>
                            <span class="badge bg-white text-forest-green fw-bold">
                                {{ strtoupper($pkg->type) }} ({{ $pkg->program_category ?? 'Reguler' }})
                            </span>
                        </div>
                        <h6 class="fw-bold mb-0 text-truncate text-white" title="{{ $pkg->name }}">{{ $pkg->name }}</h6>
                        @if($pkg->is_promo)
                            <span class="position-absolute top-0 end-0 translate-middle-y badge bg-danger me-3 shadow-sm">
                                <i class="bi bi-tag-fill me-1"></i>PROMO DISKON Rp {{ number_format($pkg->discount_amount, 0, ',', '.') }}
                            </span>
                        @endif
                    </div>

                    <div class="p-3 flex-grow-1">
                        <div class="mb-3">
                            <span class="text-muted small d-block">Harga Mulai Dari:</span>
                            <h4 class="fw-bold text-forest-green mb-0">
                                Rp {{ number_format($pkg->effective_price, 0, ',', '.') }}
                            </h4>
                            @if($pkg->is_promo)
                                <small class="text-muted text-decoration-line-through">
                                    Rp {{ number_format($pkg->price, 0, ',', '.') }}
                                </small>
                            @endif
                        </div>

                        <div class="row g-2 text-muted small mb-3">
                            <div class="col-6">
                                <i class="bi bi-clock-history me-1 text-royal-gold"></i> Durasi: <strong>{{ $pkg->duration_days }} Hari</strong>
                            </div>
                            <div class="col-6">
                                <i class="bi bi-people me-1 text-royal-gold"></i> Kuota: <strong>{{ $pkg->quota }} Pax</strong>
                            </div>
                            <div class="col-12 text-truncate">
                                <i class="bi bi-airplane me-1 text-royal-gold"></i> {{ $pkg->airline ?? 'Maskapai Terpilih' }}
                            </div>
                            <div class="col-12 text-truncate">
                                <i class="bi bi-building me-1 text-royal-gold"></i> Makkah: {{ $pkg->hotel_makkah ?? 'Bintang 5' }}
                            </div>
                            <div class="col-12 text-truncate">
                                <i class="bi bi-building me-1 text-royal-gold"></i> Madinah: {{ $pkg->hotel_madinah ?? 'Bintang 5' }}
                            </div>
                        </div>

                        <div class="p-2 rounded bg-light border d-flex justify-content-between align-items-center small mb-3">
                            <span><i class="bi bi-calendar-event me-1 text-forest-green"></i>{{ $pkg->departures_count }} Jadwal Kloter</span>
                            <span><i class="bi bi-person-check me-1 text-success"></i>{{ $pkg->registrations_count }} Terdaftar</span>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top p-2 d-flex justify-content-between align-items-center">
                        <span class="badge {{ $pkg->status === 'aktif' ? 'badge-subtle-success' : 'badge-subtle-warning' }}">
                            {{ ucfirst($pkg->status) }}
                        </span>
                        <div class="btn-group btn-group-sm">
                            <a href="{{ route('packages.show', $pkg) }}" class="btn btn-light border text-forest-green" title="Detail Paket">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            <a href="{{ route('packages.edit', $pkg) }}" class="btn btn-light border text-primary" title="Edit Paket">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('packages.duplicate', $pkg) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-light border text-secondary" title="Duplikasi Paket">
                                    <i class="bi bi-copy"></i>
                                </button>
                            </form>
                            @can('package.delete')
                            <form action="{{ route('packages.destroy', $pkg) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket {{ $pkg->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-light border text-danger" title="Hapus Paket">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5 text-muted">
                <i class="bi bi-box-seam fs-1 d-block mb-2 text-muted"></i>
                Tidak ada paket perjalanan yang sesuai filter.
            </div>
        @endforelse
    </div>

    <div class="mt-2">
        {{ $packages->links() }}
    </div>
</div>
@endsection
