@extends('layouts.app')

@section('title', 'Detail Paket - ' . $package->name)
@section('header_title', 'Detail Paket Haji & Umrah')

@section('content')
<div class="row g-4 mb-4">
    <!-- Left Column: Package Overview Card -->
    <div class="col-lg-4">
        <div class="card card-custom p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="badge bg-royal-gold text-dark font-monospace fw-bold">{{ $package->code }}</span>
                <span class="badge bg-forest-green text-white fw-bold">{{ strtoupper($package->type) }}</span>
            </div>

            <h5 class="fw-bold text-dark mb-1">{{ $package->name }}</h5>
            <small class="text-muted d-block mb-3">Kategori: {{ $package->program_category ?? 'Reguler' }}</small>

            <div class="p-3 rounded-3 bg-light border mb-3">
                <span class="text-muted small d-block">Harga Dasar (Quad):</span>
                <h4 class="fw-bold text-forest-green mb-0">
                    Rp {{ number_format($package->effective_price, 0, ',', '.') }}
                </h4>
                @if($package->is_promo)
                    <small class="text-danger fw-semibold d-block mt-1">
                        <i class="bi bi-tag-fill me-1"></i>Promo Diskon: Rp {{ number_format($package->discount_amount, 0, ',', '.') }}
                    </small>
                @endif
            </div>

            <div class="row g-2 text-start small text-muted mb-3">
                <div class="col-6">
                    <span>Triple (Ber-3):</span>
                    <strong class="text-dark d-block">Rp {{ number_format($package->triple_price ?? $package->price, 0, ',', '.') }}</strong>
                </div>
                <div class="col-6">
                    <span>Double (Ber-2):</span>
                    <strong class="text-dark d-block">Rp {{ number_format($package->double_price ?? $package->price, 0, ',', '.') }}</strong>
                </div>
                <div class="col-6 mt-2">
                    <span>Durasi Perjalanan:</span>
                    <strong class="text-dark d-block">{{ $package->duration_days }} Hari</strong>
                </div>
                <div class="col-6 mt-2">
                    <span>Target Kuota:</span>
                    <strong class="text-dark d-block">{{ $package->quota }} Pax</strong>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <div class="text-start small text-muted mb-3">
                <div class="mb-2"><i class="bi bi-airplane-fill text-royal-gold me-2"></i> {{ $package->airline ?? 'Maskapai Rekanan' }}</div>
                <div class="mb-2"><i class="bi bi-building-fill text-royal-gold me-2"></i> Makkah: {{ $package->hotel_makkah ?? '-' }} ({{ $package->hotel_makkah_rating }} Bintang)</div>
                <div><i class="bi bi-building-fill text-royal-gold me-2"></i> Madinah: {{ $package->hotel_madinah ?? '-' }} ({{ $package->hotel_madinah_rating }} Bintang)</div>
            </div>

            <div class="d-grid gap-2">
                <a href="{{ route('packages.edit', $package) }}" class="btn btn-sm btn-forest-green">
                    <i class="bi bi-pencil me-1"></i> Edit Paket
                </a>
                <form action="{{ route('packages.duplicate', $package) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-light border w-100">
                        <i class="bi bi-copy me-1"></i> Duplikasi Paket Ini
                    </button>
                </form>
                @can('package.delete')
                <form action="{{ route('packages.destroy', $package) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket {{ $package->name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                        <i class="bi bi-trash me-1"></i> Hapus Paket
                    </button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <!-- Right Column: Tabs (Fasilitas, Jadwal Keberangkatan, Jamaah Terdaftar) -->
    <div class="col-lg-8">
        <div class="card card-custom p-4">
            <ul class="nav nav-tabs nav-fill mb-4" id="packageDetailTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-semibold text-forest-green" id="fac-tab" data-bs-toggle="tab" data-bs-target="#fac-pane" type="button">
                        <i class="bi bi-check2-circle me-1 text-royal-gold"></i> Fasilitas Paket ({{ $package->facilities->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold text-forest-green" id="dep-tab" data-bs-toggle="tab" data-bs-target="#dep-pane" type="button">
                        <i class="bi bi-calendar-check me-1 text-royal-gold"></i> Jadwal Kloter ({{ $package->departures->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-semibold text-forest-green" id="jam-tab" data-bs-toggle="tab" data-bs-target="#jam-pane" type="button">
                        <i class="bi bi-people me-1 text-royal-gold"></i> Jamaah Terdaftar ({{ $package->registrations->count() }})
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="packageTabContent">
                <!-- Tab 1: Fasilitas Paket -->
                <div class="tab-pane fade show active" id="fac-pane" role="tabpanel">
                    <h6 class="fw-bold text-dark mb-3">Fasilitas Termasuk dalam Biaya Paket</h6>
                    <div class="row g-2 mb-4">
                        @forelse($package->facilities as $facility)
                            <div class="col-md-6">
                                <div class="p-2 rounded bg-light border d-flex align-items-center gap-2 small">
                                    <i class="bi bi-check-circle-fill text-success fs-6"></i>
                                    <span class="fw-semibold text-dark">{{ $facility->name }}</span>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-muted small py-3">Belum ada rincian fasilitas.</div>
                        @endforelse
                    </div>

                    @if($package->description)
                        <h6 class="fw-bold text-dark mb-2">Keterangan &amp; Syarat Paket</h6>
                        <div class="p-3 rounded bg-light border small text-muted">
                            {{ $package->description }}
                        </div>
                    @endif
                </div>

                <!-- Tab 2: Jadwal Keberangkatan -->
                <div class="tab-pane fade" id="dep-pane" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0">Jadwal Keberangkatan Tersedia</h6>
                        <a href="{{ route('departures.create') }}" class="btn btn-sm btn-forest-green">
                            <i class="bi bi-calendar-plus me-1"></i> Tambah Jadwal Baru
                        </a>
                    </div>

                    @forelse($package->departures as $dep)
                        <div class="p-3 mb-2 rounded bg-light border d-flex flex-wrap justify-content-between align-items-center gap-2">
                            <div>
                                <span class="badge bg-forest-green text-royal-gold font-monospace me-1">{{ $dep->code }}</span>
                                <strong class="text-dark">{{ $dep->departure_date->translatedFormat('d F Y') }}</strong>
                                <span class="text-muted small">&bull; Kepulangan: {{ $dep->return_date->translatedFormat('d F Y') }}</span>
                                <div class="small text-muted mt-1">
                                    <i class="bi bi-airplane me-1"></i>{{ $dep->airline }} ({{ $dep->flight_number ?? 'Direct' }}) &bull; Tour Leader: {{ $dep->leader_name ?? '-' }}
                                </div>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-white text-dark border px-2 py-1">
                                    {{ $dep->registrations_count }} / {{ $dep->quota }} Pax
                                </span>
                                <a href="{{ route('departures.show', $dep) }}" class="btn btn-sm btn-outline-secondary">
                                    Manifest &rarr;
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            Belum ada jadwal keberangkatan untuk paket ini.
                        </div>
                    @endforelse
                </div>

                <!-- Tab 3: Jamaah Terdaftar -->
                <div class="tab-pane fade" id="jam-pane" role="tabpanel">
                    <h6 class="fw-bold text-dark mb-3">Daftar Jamaah yang Mendaftar Paket Ini</h6>
                    <div class="table-responsive">
                        <table class="table table-custom table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No. Registrasi</th>
                                    <th>Nama Jamaah</th>
                                    <th>Jadwal Keberangkatan</th>
                                    <th>Tipe Kamar</th>
                                    <th>Status Tagihan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($package->registrations as $reg)
                                    <tr>
                                        <td class="font-monospace small fw-bold text-forest-green">
                                            {{ $reg->registration_number }}
                                        </td>
                                        <td>
                                            <a href="{{ route('jamaah.show', $reg->jamaah_id) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $reg->jamaah->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td class="small">
                                            {{ $reg->departure->departure_date ? $reg->departure->departure_date->translatedFormat('d M Y') : '-' }}
                                        </td>
                                        <td class="small">{{ $reg->room_type_label }}</td>
                                        <td>
                                            @if($reg->invoice)
                                                <span class="badge {{ $reg->invoice->status === 'lunas' ? 'badge-subtle-success' : 'badge-subtle-danger' }}">
                                                    {{ $reg->invoice->status_label }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            Belum ada jamaah yang terdaftar dalam paket ini.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
