@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('header_title', 'Profil Akun Saya')

@section('content')
<div class="row g-4">
    <div class="col-lg-4">
        <div class="card card-custom p-4 text-center">
            <div class="rounded-circle bg-forest-green text-royal-gold fw-bold mx-auto mb-3 d-flex align-items-center justify-content-center shadow" style="width: 88px; height: 88px; font-size: 2.2rem; border: 3px solid #C9A45B;">
                {{ strtoupper(substr($user->name, 0, 1)) }}
            </div>
            <h5 class="fw-bold text-dark mb-1">{{ $user->name }}</h5>
            <p class="text-muted small mb-2">{{ $user->email }}</p>
            <span class="badge bg-royal-gold text-dark fw-bold px-3 py-2 rounded-pill mx-auto mb-3">
                <i class="bi bi-shield-lock me-1"></i> {{ $user->roles->pluck('name')->first() ?? 'Staff' }}
            </span>
            <div class="border-top pt-3 text-start small text-muted">
                <div class="mb-2"><i class="bi bi-telephone text-royal-gold me-2"></i> {{ $user->phone ?? 'Belum diisi' }}</div>
                <div class="mb-2"><i class="bi bi-calendar-check text-royal-gold me-2"></i> Terdaftar sejak: {{ $user->created_at->translatedFormat('d F Y') }}</div>
                <div><i class="bi bi-clock-history text-royal-gold me-2"></i> Login terakhir: {{ $user->last_login_at ? $user->last_login_at->translatedFormat('d M Y, H:i') : '-' }}</div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card card-custom p-4 mb-4">
            <h5 class="fw-bold text-forest-green mb-3"><i class="bi bi-person-lines-fill me-2 text-royal-gold"></i> Edit Informasi Akun</h5>
            
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="name" class="form-label small fw-semibold">Nama Lengkap</label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                        <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="phone" class="form-label small fw-semibold">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}">
                        @error('phone')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-forest-green px-4 py-2 rounded-pill">
                    <i class="bi bi-check2-circle me-1"></i> Simpan Perubahan Profil
                </button>
            </form>
        </div>

        <div class="card card-custom p-4">
            <h5 class="fw-bold text-forest-green mb-3"><i class="bi bi-key-fill me-2 text-royal-gold"></i> Ubah Kata Sandi</h5>

            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label for="current_password" class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                    @error('current_password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="password" class="form-label small fw-semibold">Kata Sandi Baru</label>
                        <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="Min. 8 karakter">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-semibold">Ulangi Kata Sandi Baru</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Konfirmasi kata sandi">
                    </div>
                </div>

                <button type="submit" class="btn btn-royal-gold px-4 py-2 rounded-pill">
                    <i class="bi bi-shield-lock me-1"></i> Perbarui Kata Sandi
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
