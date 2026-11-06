@extends('layouts.app')

@section('title', 'Data Jamaah')
@section('header_title', 'Manajemen Data Jamaah')

@section('content')
<div class="card card-custom p-4 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
        <div>
            <h5 class="fw-bold text-forest-green mb-1">
                <i class="bi bi-people-fill text-royal-gold me-2"></i> Direktori Jamaah Haji &amp; Umrah
            </h5>
            <p class="text-muted small mb-0">Kelola data identitas, paspor, berkas digital, dan riwayat perjalanan jamaah</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('jamaah.export.excel') }}" class="btn btn-sm btn-outline-success">
                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
            </a>
            <a href="{{ route('jamaah.export.pdf') }}" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="{{ route('jamaah.create') }}" class="btn btn-sm btn-forest-green">
                <i class="bi bi-person-plus-fill me-1"></i> Tambah Jamaah Baru
            </a>
        </div>
    </div>

    <!-- Search & Filter Bar -->
    <form action="{{ route('jamaah.index') }}" method="GET" class="row g-2 mb-3">
        <div class="col-md-5">
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light text-muted border-end-0"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Cari nama, NIK, nomor registrasi, paspor..." value="{{ $search }}">
            </div>
        </div>
        <div class="col-md-3">
            <select name="gender" class="form-select form-select-sm">
                <option value="">Semua Jenis Kelamin</option>
                <option value="L" {{ $gender === 'L' ? 'selected' : '' }}>Laki-Laki</option>
                <option value="P" {{ $gender === 'P' ? 'selected' : '' }}>Perempuan</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="aktif" {{ $status === 'aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="nonaktif" {{ $status === 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                <option value="arsip" {{ $status === 'arsip' ? 'selected' : '' }}>Arsip</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-forest-green flex-grow-1">Filter</button>
            @if($search || $gender || $status)
                <a href="{{ route('jamaah.index') }}" class="btn btn-sm btn-light border">Reset</a>
            @endif
        </div>
    </form>

    <!-- Table -->
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>No. Registrasi</th>
                    <th>Nama Lengkap</th>
                    <th>NIK / Paspor</th>
                    <th>Jenis Kelamin / Usia</th>
                    <th>WhatsApp / Kontak</th>
                    <th>Kota</th>
                    <th>Dokumen</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($jamaahs as $jamaah)
                    <tr>
                        <td class="fw-bold font-monospace text-forest-green small">
                            {{ $jamaah->registration_number }}
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $jamaah->name }}</div>
                            @if($jamaah->special_needs)
                                <small class="text-danger d-block" style="font-size: 0.72rem;">
                                    <i class="bi bi-heart-pulse-fill me-1"></i>{{ Str::limit($jamaah->special_needs, 30) }}
                                </small>
                            @endif
                        </td>
                        <td class="small">
                            <div><i class="bi bi-card-text me-1 text-muted"></i>{{ $jamaah->nik }}</div>
                            <div class="text-muted"><i class="bi bi-passport me-1 text-muted"></i>{{ $jamaah->passport_number ?? '-' }}</div>
                        </td>
                        <td class="small">
                            {{ $jamaah->gender_label }}
                            @if($jamaah->age)
                                <span class="text-muted">({{ $jamaah->age }} thn)</span>
                            @endif
                        </td>
                        <td class="small">
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $jamaah->phone) }}" target="_blank" class="text-decoration-none text-success fw-semibold">
                                <i class="bi bi-whatsapp me-1"></i>{{ $jamaah->phone }}
                            </a>
                        </td>
                        <td class="small text-muted">{{ $jamaah->city ?? '-' }}</td>
                        <td>
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-paperclip me-1 text-royal-gold"></i>{{ $jamaah->documents_count }} Berkas
                            </span>
                        </td>
                        <td>
                            @php
                                $statusBadge = match($jamaah->status) {
                                    'aktif' => 'badge-subtle-success',
                                    'nonaktif' => 'badge-subtle-warning',
                                    default => 'badge-subtle-danger',
                                };
                            @endphp
                            <span class="badge {{ $statusBadge }} px-2 py-1">
                                {{ ucfirst($jamaah->status) }}
                            </span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('jamaah.show', $jamaah) }}" class="btn btn-light border text-forest-green" title="Detail Profil">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('jamaah.edit', $jamaah) }}" class="btn btn-light border text-primary" title="Edit Data">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('jamaah.print-form', $jamaah) }}" target="_blank" class="btn btn-light border text-dark" title="Cetak Formulir">
                                    <i class="bi bi-printer"></i>
                                </a>
                                @can('jamaah.delete')
                                <form action="{{ route('jamaah.destroy', $jamaah) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data jamaah {{ $jamaah->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-light border text-danger" title="Hapus Data Jamaah">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2 text-muted"></i>
                            Tidak ada data jamaah yang sesuai kriteria pencarian.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $jamaahs->links() }}
    </div>
</div>
@endsection
