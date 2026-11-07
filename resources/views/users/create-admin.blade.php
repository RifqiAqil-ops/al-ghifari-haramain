@extends('layouts.app')

@section('title', 'Tambah Admin Baru - Al Ghifari Haramain')
@section('header_title', 'Manajemen Pengguna')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Manajemen Pengguna
                </a>
                <h5 class="fw-bold text-forest-green mb-0">
                    <i class="bi bi-person-plus-fill text-royal-gold me-2"></i> Tambah Akun Admin Baru
                </h5>
                <p class="text-muted small mb-0">Buat kredensial akun administrator baru untuk pengelolaan operasional Al Ghifari Haramain</p>
            </div>
            <div>
                <span class="badge bg-forest-green text-royal-gold px-3 py-2">
                    <i class="bi bi-shield-check me-1"></i> Role: Admin
                </span>
            </div>
        </div>

        <div class="card card-custom border-0 shadow-sm rounded-3">
            <div class="card-body p-4">
                <div class="p-3 bg-light border rounded-3 mb-4 small">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-shield-check text-royal-gold fs-5"></i>
                        <div>
                            <strong>Hak Akses Otomatis:</strong> Akun yang didaftarkan melalui form ini akan otomatis mendapatkan peran <strong>Admin</strong> dan status <strong>Aktif</strong> sehingga dapat langsung digunakan untuk masuk.
                        </div>
                    </div>
                </div>

                <form action="{{ route('users.store-admin') }}" method="POST">
                    @csrf

                    <div class="row g-3">
                        <div class="col-12">
                            <label for="name" class="form-label fw-semibold small">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror"
                                value="{{ old('name') }}" required placeholder="Contoh: Muhammad Fikri, S.Kom" autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-12">
                            <label for="email" class="form-label fw-semibold small">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}" required placeholder="admin.operasional@alghifari.com">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text small text-muted">Email ini digunakan sebagai kredensial login dan harus unik di dalam sistem.</div>
                        </div>

                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold small">Kata Sandi (Password) <span class="text-danger">*</span></label>
                            <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror"
                                required placeholder="Minimal 8 karakter">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold small">Konfirmasi Password <span class="text-danger">*</span></label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                required placeholder="Ulangi kata sandi">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('users.index') }}" class="btn btn-light border px-4">Batal</a>
                        <button type="submit" class="btn btn-forest-green px-4">
                            <i class="bi bi-person-check-fill me-1"></i> Simpan Akun Admin
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
