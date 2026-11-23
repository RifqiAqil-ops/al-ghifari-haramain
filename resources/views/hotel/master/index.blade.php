@extends('layouts.app')

@section('title', 'Daftar Master Hotel')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-buildings text-primary me-2"></i>Master Hotel Mitra</h4>
            <p class="text-muted small mb-0">Kelola Hotel di Makkah, Madinah, dan Tipe Kamar</p>
        </div>
        <a href="{{ route('hotel.master.create') }}" class="btn btn-primary shadow-sm btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Hotel Baru
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Hotel</th>
                            <th>Kota &amp; Kategori</th>
                            <th>Bintang</th>
                            <th>Jumlah Tipe Kamar</th>
                            <th>Kontak</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($hotels as $h)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $h->code }}</span></td>
                            <td><strong>{{ $h->name }}</strong></td>
                            <td>{{ $h->city }}, {{ $h->country }} &bull; {{ $h->category }}</td>
                            <td>
                                @for($i=0; $i<$h->star_rating; $i++)
                                <i class="bi bi-star-fill text-warning"></i>
                                @endfor
                            </td>
                            <td>{{ $h->room_types_count }} tipe kamar</td>
                            <td>{{ $h->telephone ?? '-' }}</td>
                            <td class="text-center">
                                <a href="{{ route('hotel.master.show', $h) }}" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size: 12px;">Detail &amp; Kamar</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Belum ada hotel terdaftar.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($hotels->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $hotels->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
