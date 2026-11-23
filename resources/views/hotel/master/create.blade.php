@extends('layouts.app')

@section('title', 'Tambah Hotel Baru')

@section('content')
<div class="container-fluid py-3">
    <div class="mb-4">
        <a href="{{ route('hotel.master.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Hotel
        </a>
        <h4 class="fw-bold mb-0 text-dark">Tambah Hotel Mitra Baru</h4>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-4">
            <form action="{{ route('hotel.master.store') }}" method="POST">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nama Hotel <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Hotel Syahadah Makkah" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Kota <span class="text-danger">*</span></label>
                        <select name="city" class="form-select" required>
                            <option value="Makkah" selected>Makkah Al-Mukarramah</option>
                            <option value="Madinah">Madinah Al-Munawwarah</option>
                            <option value="Jeddah">Jeddah</option>
                            <option value="Riyadh">Riyadh</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Kategori <span class="text-danger">*</span></label>
                        <select name="category" class="form-select" required>
                            <option value="Bintang 5">Bintang 5</option>
                            <option value="Bintang 4">Bintang 4</option>
                            <option value="Bintang 3">Bintang 3</option>
                            <option value="Apartemen / Butik">Apartemen / Butik</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Peringkat Bintang <span class="text-danger">*</span></label>
                        <input type="number" name="star_rating" class="form-control" value="5" min="1" max="5" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Contact Person</label>
                        <input type="text" name="contact_person" class="form-control" placeholder="Nama pengelola">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Nomor Telepon</label>
                        <input type="text" name="telephone" class="form-control" placeholder="+966 ...">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Alamat Lengkap</label>
                        <input type="text" name="address" class="form-control" placeholder="Contoh: Ajyad Street, Makkah">
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-primary px-4"><i class="bi bi-save me-1"></i> Simpan Hotel</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
