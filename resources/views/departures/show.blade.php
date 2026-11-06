@extends('layouts.app')

@section('title', 'Detail Keberangkatan - ' . $departure->code)

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('departures.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Keberangkatan
        </a>
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0 text-dark">{{ $departure->code }}</h4>
            <span class="badge 
                @if($departure->status == 'selesai') bg-success
                @elseif($departure->status == 'berangkat' || $departure->status == 'dalam_perjalanan') bg-primary
                @elseif($departure->status == 'siap_berangkat') bg-info text-dark
                @elseif($departure->status == 'dibatalkan') bg-danger
                @else bg-warning text-dark @endif rounded-pill px-3 py-2 text-uppercase">
                {{ str_replace('_', ' ', $departure->status) }}
            </span>
        </div>
        <p class="text-muted small mb-0">
            Paket: <strong>{{ $departure->package->name ?? '-' }}</strong> | 
            Program: <span class="badge bg-light text-dark border">{{ strtoupper($departure->package->type ?? 'UMRAH') }}</span>
        </p>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a href="{{ route('departures.manifest', $departure) }}" target="_blank" class="btn btn-outline-danger shadow-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Cetak Manifest (PDF)
        </a>
        <a href="{{ route('departures.edit', $departure) }}" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-pencil me-1"></i> Edit Jadwal
        </a>
        <!-- Quick Status Modal Trigger -->
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal">
            <i class="bi bi-arrow-repeat me-1"></i> Ubah Status
        </button>
        @can('departure.delete')
        <form action="{{ route('departures.destroy', $departure) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jadwal keberangkatan {{ $departure->code }}?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger shadow-sm">
                <i class="bi bi-trash me-1"></i> Hapus Jadwal
            </button>
        </form>
        @endcan
    </div>
</div>

<!-- Header Info Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-primary-subtle text-primary rounded-3 p-3">
                    <i class="bi bi-calendar-event fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Jadwal Perjalanan</span>
                    <strong class="d-block text-dark">{{ $departure->departure_date ? $departure->departure_date->format('d M Y') : '-' }}</strong>
                    <span class="text-muted small">s/d {{ $departure->return_date ? $departure->return_date->format('d M Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-info-subtle text-info rounded-3 p-3">
                    <i class="bi bi-people fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Manifest / Kuota</span>
                    <h5 class="fw-bold mb-0 text-dark">{{ $departure->registrations->count() }} <span class="fs-6 text-muted font-normal">/ {{ $departure->quota }} Seat</span></h5>
                    <div class="progress mt-2" style="height: 6px;">
                        @php $fillPct = $departure->quota > 0 ? min(100, round(($departure->registrations->count() / $departure->quota) * 100)) : 0; @endphp
                        <div class="progress-bar @if($fillPct >= 90) bg-danger @elseif($fillPct >= 60) bg-warning @else bg-success @endif" 
                            style="width: {{ $fillPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-warning-subtle text-warning rounded-3 p-3">
                    <i class="bi bi-airplane-engines fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Penerbangan</span>
                    <strong class="d-block text-dark">{{ $departure->airline ?? 'Belum ditentukan' }}</strong>
                    <span class="text-muted small">{{ $departure->flight_number ?? '-' }} ({{ $departure->origin_airport ? Str::limit($departure->origin_airport, 15) : 'CGK' }})</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm rounded-3 p-3 bg-white h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="stat-icon bg-success-subtle text-success rounded-3 p-3">
                    <i class="bi bi-person-badge fs-4"></i>
                </div>
                <div>
                    <span class="text-muted small d-block">Tour Leader / Muthawif</span>
                    <strong class="d-block text-dark">{{ $departure->leader_name ?? 'Belum ditugaskan' }}</strong>
                    <span class="text-muted small">{{ $departure->leader_phone ?? '-' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tabs Navigation -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-header bg-white border-bottom p-0">
        <ul class="nav nav-tabs card-header-tabs m-0 px-3 border-0" id="departureTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active py-3 px-4 fw-semibold border-0 border-bottom border-3 border-primary" id="manifest-tab" data-bs-toggle="tab" data-bs-target="#manifest" type="button" role="tab">
                    <i class="bi bi-people-fill me-1"></i> Manifest Jamaah ({{ $departure->registrations->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold border-0" id="checklist-tab" data-bs-toggle="tab" data-bs-target="#checklist" type="button" role="tab">
                    <i class="bi bi-check2-square me-1"></i> Checklist Persiapan ({{ $departure->checklists->where('is_completed', true)->count() }}/{{ $departure->checklists->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold border-0" id="flights-tab" data-bs-toggle="tab" data-bs-target="#flights" type="button" role="tab">
                    <i class="bi bi-airplane me-1"></i> Penerbangan & Hotel
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link py-3 px-4 fw-semibold border-0" id="itinerary-tab" data-bs-toggle="tab" data-bs-target="#itinerary" type="button" role="tab">
                    <i class="bi bi-map me-1"></i> Itinerary Perjalanan
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-4">
        <div class="tab-content" id="departureTabsContent">
            <!-- TAB 1: MANIFEST -->
            <div class="tab-pane fade show active" id="manifest" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0 text-dark">Daftar Manifest Penumpang Rombongan</h6>
                    <a href="{{ route('jamaahs.register') }}?departure_id={{ $departure->id }}&package_id={{ $departure->package_id }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-person-plus me-1"></i> Tambah Jamaah ke Keberangkatan Ini
                    </a>
                </div>

                @if($departure->registrations->isEmpty())
                    <div class="text-center py-5">
                        <i class="bi bi-people text-muted display-4 d-block mb-3"></i>
                        <h6 class="fw-semibold text-secondary">Belum ada jamaah yang terdaftar di kloter ini.</h6>
                        <p class="text-muted small">Daftarkan jamaah baru atau pindahkan jamaah terdaftar ke kloter ini.</p>
                        <a href="{{ route('jamaahs.register') }}?departure_id={{ $departure->id }}&package_id={{ $departure->package_id }}" class="btn btn-primary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i> Daftarkan Jamaah Sekarang
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 40px;">No</th>
                                    <th>Nama Jamaah</th>
                                    <th>Nomor Paspor</th>
                                    <th>Kategori & Kamar</th>
                                    <th>Kontak / WhatsApp</th>
                                    <th>Status Pembayaran</th>
                                    <th>Dokumen</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($departure->registrations as $index => $reg)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="avatar-circle bg-primary-subtle text-primary fw-bold" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                                    {{ strtoupper(substr($reg->jamaah->name ?? 'J', 0, 1)) }}
                                                </div>
                                                <div>
                                                    <a href="{{ route('jamaahs.show', $reg->jamaah_id) }}" class="fw-bold text-dark text-decoration-none">
                                                        {{ $reg->jamaah->name ?? '-' }}
                                                    </a>
                                                    <span class="text-muted small d-block">
                                                        {{ $reg->jamaah->gender == 'L' ? 'Laki-laki' : 'Perempuan' }} 
                                                        @if($reg->jamaah->birth_date) ({{ \Carbon\Carbon::parse($reg->jamaah->birth_date)->age }} th) @endif
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($reg->jamaah->passport_number)
                                                <span class="fw-mono fw-bold text-dark">{{ $reg->jamaah->passport_number }}</span>
                                                <span class="text-muted small d-block">Exp: {{ $reg->jamaah->passport_expiry ? $reg->jamaah->passport_expiry->format('d/m/Y') : '-' }}</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">Belum ada paspor</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ strtoupper($reg->room_type) }}</span>
                                            <span class="text-muted small d-block">Reg: {{ $reg->registration_number }}</span>
                                        </td>
                                        <td>
                                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $reg->jamaah->phone ?? '')) }}" target="_blank" class="text-decoration-none text-success">
                                                <i class="bi bi-whatsapp me-1"></i>{{ $reg->jamaah->phone ?? '-' }}
                                            </a>
                                        </td>
                                        <td>
                                            @if($reg->invoice)
                                                @if($reg->invoice->status == 'lunas')
                                                    <span class="badge bg-success-subtle text-success border border-success">Lunas</span>
                                                @elseif($reg->invoice->status == 'sebagian')
                                                    <span class="badge bg-warning-subtle text-warning border border-warning">DP / Sebagian</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger">Belum Bayar</span>
                                                @endif
                                                <span class="text-muted small d-block">Sisa: Rp {{ number_format($reg->invoice->remaining_amount, 0, ',', '.') }}</span>
                                            @else
                                                <span class="badge bg-secondary">Tanpa Invoice</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $docs = $reg->jamaah->documents ?? collect();
                                                $hasKtp = $docs->where('document_type', 'ktp')->count() > 0;
                                                $hasPassport = $docs->where('document_type', 'paspor')->count() > 0;
                                            @endphp
                                            <span class="badge {{ $hasPassport ? 'bg-success' : 'bg-warning text-dark' }} me-1" title="Paspor">Paspor</span>
                                            <span class="badge {{ $hasKtp ? 'bg-success' : 'bg-secondary' }}" title="KTP">KTP</span>
                                        </td>
                                        <td class="text-end">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-light border" type="button" data-bs-toggle="dropdown">
                                                    <i class="bi bi-three-dots"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><a class="dropdown-item" href="{{ route('jamaahs.show', $reg->jamaah_id) }}"><i class="bi bi-person me-2"></i> Profil Jamaah</a></li>
                                                    <li><a class="dropdown-item" href="{{ route('registrations.show', $reg->id) }}"><i class="bi bi-card-text me-2"></i> Detail Pendaftaran</a></li>
                                                    @if($reg->invoice)
                                                        <li><a class="dropdown-item" href="{{ route('finance.invoices.show', $reg->invoice->id) }}"><i class="bi bi-receipt me-2"></i> Lihat Invoice</a></li>
                                                    @endif
                                                </ul>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <!-- TAB 2: CHECKLIST -->
            <div class="tab-pane fade" id="checklist" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Checklist Kesiapan Operasional</h6>
                        <p class="text-muted small mb-0">Pastikan seluruh berkas, visa, hotel, dan tiket telah terkonfirmasi sebelum keberangkatan.</p>
                    </div>
                </div>

                <div class="list-group">
                    @forelse($departure->checklists as $item)
                        <div class="list-group-item d-flex align-items-center justify-content-between py-3">
                            <div class="d-flex align-items-center gap-3">
                                <form action="{{ route('departures.checklist', $item) }}" method="POST" id="chkForm{{ $item->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <input type="checkbox" name="is_completed" value="1" class="form-check-input fs-5" 
                                        {{ $item->is_completed ? 'checked' : '' }} onchange="document.getElementById('chkForm{{ $item->id }}').submit();">
                                </form>
                                <div>
                                    <span class="fw-semibold {{ $item->is_completed ? 'text-decoration-line-through text-muted' : 'text-dark' }}">
                                        {{ $item->item_name }}
                                    </span>
                                    <div class="d-flex align-items-center gap-2 mt-1">
                                        <span class="badge bg-light text-dark border text-uppercase" style="font-size: 0.7rem;">{{ $item->category }}</span>
                                        @if($item->is_completed)
                                            <span class="text-success small"><i class="bi bi-check-circle-fill me-1"></i> Selesai oleh {{ $item->completedByUser->name ?? 'Staff' }} ({{ $item->completed_at ? $item->completed_at->format('d/m/Y H:i') : '-' }})</span>
                                        @else
                                            <span class="text-warning small"><i class="bi bi-clock-history me-1"></i> Menunggu tindak lanjut</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <span class="badge {{ $item->is_completed ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' }}">
                                {{ $item->is_completed ? 'Terverifikasi' : 'Pending' }}
                            </span>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">Belum ada item checklist keberangkatan.</div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 3: FLIGHTS & ACCOMMODATIONS -->
            <div class="tab-pane fade" id="flights" role="tabpanel">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <div class="card border rounded-3 h-100">
                            <div class="card-header bg-light fw-bold py-3">
                                <i class="bi bi-airplane-fill text-primary me-2"></i> Rencana Penerbangan
                            </div>
                            <div class="card-body">
                                @forelse($departure->flights as $flight)
                                    <div class="p-3 border rounded-3 mb-3 bg-light-subtle">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong class="text-dark">{{ $flight->airline }} ({{ $flight->flight_number }})</strong>
                                            <span class="badge bg-primary text-uppercase">{{ $flight->flight_type ?? 'Pergi' }}</span>
                                        </div>
                                        <div class="row g-2 small text-muted">
                                            <div class="col-6">
                                                <i class="bi bi-geo-alt me-1"></i> Asal: <strong>{{ $flight->departure_airport }}</strong>
                                                <div>{{ $flight->departure_time ? \Carbon\Carbon::parse($flight->departure_time)->format('d M Y H:i') : '-' }}</div>
                                            </div>
                                            <div class="col-6">
                                                <i class="bi bi-geo-fill me-1"></i> Tujuan: <strong>{{ $flight->arrival_airport }}</strong>
                                                <div>{{ $flight->arrival_time ? \Carbon\Carbon::parse($flight->arrival_time)->format('d M Y H:i') : '-' }}</div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 border rounded-3 bg-light-subtle">
                                        <div class="d-flex justify-content-between mb-2">
                                            <strong class="text-dark">{{ $departure->airline ?? 'Belum ditentukan' }} ({{ $departure->flight_number ?? '-' }})</strong>
                                            <span class="badge bg-primary">Utama</span>
                                        </div>
                                        <p class="small text-muted mb-1">
                                            Rute: <strong>{{ $departure->origin_airport ?? 'CGK' }}</strong> &rarr; <strong>{{ $departure->destination_airport ?? 'JED' }}</strong>
                                        </p>
                                        <p class="small text-muted mb-0">
                                            Waktu: {{ $departure->departure_time ?? '-' }} &rarr; {{ $departure->arrival_time ?? '-' }}
                                        </p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="card border rounded-3 h-100">
                            <div class="card-header bg-light fw-bold py-3">
                                <i class="bi bi-building text-success me-2"></i> Hotel & Akomodasi
                            </div>
                            <div class="card-body">
                                @forelse($departure->accommodations as $hotel)
                                    <div class="p-3 border rounded-3 mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <h6 class="fw-bold mb-0 text-dark">{{ $hotel->hotel_name }}</h6>
                                            <span class="badge bg-success-subtle text-success">{{ $hotel->city }}</span>
                                        </div>
                                        <div class="small text-warning mb-2">
                                            @for($i = 0; $i < ($hotel->star_rating ?? 5); $i++) <i class="bi bi-star-fill"></i> @endfor
                                        </div>
                                        <div class="small text-muted">
                                            Check-in: {{ $hotel->check_in_date ? $hotel->check_in_date->format('d M Y') : '-' }} | 
                                            Check-out: {{ $hotel->check_out_date ? $hotel->check_out_date->format('d M Y') : '-' }}
                                        </div>
                                    </div>
                                @empty
                                    <div class="p-3 border rounded-3 mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <h6 class="fw-bold mb-0 text-dark">{{ $departure->package->hotel_makkah ?? 'Hotel Makkah (Sesuai Paket)' }}</h6>
                                            <span class="badge bg-success-subtle text-success">Makkah</span>
                                        </div>
                                        <div class="small text-warning mb-1"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i> Bintang 5</div>
                                        <span class="small text-muted">Akomodasi standar terdaftar pada paket program.</span>
                                    </div>
                                    <div class="p-3 border rounded-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <h6 class="fw-bold mb-0 text-dark">{{ $departure->package->hotel_madinah ?? 'Hotel Madinah (Sesuai Paket)' }}</h6>
                                            <span class="badge bg-success-subtle text-success">Madinah</span>
                                        </div>
                                        <div class="small text-warning mb-1"><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i><i class="bi bi-star-fill"></i> Bintang 5</div>
                                        <span class="small text-muted">Akomodasi standar terdaftar pada paket program.</span>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TAB 4: ITINERARY -->
            <div class="tab-pane fade" id="itinerary" role="tabpanel">
                <h6 class="fw-bold mb-3 text-dark">Rundown & Jadwal Kegiatan Jamaah</h6>
                @php $allItineraries = $departure->itineraries; @endphp
                @if($allItineraries->isEmpty() && $departure->package && $departure->package->itineraries)
                    @php $allItineraries = $departure->package->itineraries; @endphp
                @endif

                @if($allItineraries->isEmpty())
                    <div class="text-center py-4 text-muted">Belum ada rundown terinci yang dimasukkan.</div>
                @else
                    <div class="timeline ps-3 border-start border-2 border-primary ms-3">
                        @foreach($allItineraries as $itin)
                            <div class="mb-4 position-relative ps-4">
                                <div class="position-absolute bg-primary rounded-circle" style="width: 14px; height: 14px; left: -24px; top: 4px;"></div>
                                <h6 class="fw-bold text-dark mb-1">Hari ke-{{ $itin->day_number }}: {{ $itin->title }}</h6>
                                <p class="text-muted small mb-2">{{ $itin->description }}</p>
                                @if($itin->items && $itin->items->count() > 0)
                                    <div class="bg-light p-3 rounded-3 small">
                                        @foreach($itin->items as $sub)
                                            <div class="d-flex gap-3 mb-1">
                                                <span class="fw-bold text-primary">{{ $sub->time_start ? \Carbon\Carbon::parse($sub->time_start)->format('H:i') : '' }}</span>
                                                <span class="text-dark">{{ $sub->activity }} ({{ $sub->location ?? '-' }})</span>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Modal Quick Update Status -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('departures.status', $departure) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">Perbarui Status Keberangkatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted">Ubah status operasional rombongan sesuai tahapan keberangkatan aktual:</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Pilih Status Baru</label>
                        <select name="status" class="form-select" required>
                            @foreach([
                                'persiapan' => 'Persiapan (Pengumpulan berkas & manasik)',
                                'siap_berangkat' => 'Siap Berangkat (Visa & Tiket lengkap)',
                                'berangkat' => 'Berangkat (Pelepasan di Bandara Asal)',
                                'dalam_perjalanan' => 'Dalam Perjalanan (Sedang di Tanah Suci)',
                                'pulang' => 'Pulang (Perjalanan Kembali ke Tanah Air)',
                                'selesai' => 'Selesai (Rombongan telah tiba di tanah air)',
                                'dibatalkan' => 'Dibatalkan'
                            ] as $sKey => $sLabel)
                                <option value="{{ $sKey }}" {{ $departure->status == $sKey ? 'selected' : '' }}>{{ $sLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
