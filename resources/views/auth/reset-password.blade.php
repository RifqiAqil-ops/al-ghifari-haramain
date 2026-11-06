@extends('layouts.auth')

@section('title', 'Atur Ulang Kata Sandi')

@section('content')
<div class="text-center mb-4">
    <h5 class="fw-bold text-dark mb-1">Atur Kata Sandi Baru</h5>
    <p class="text-muted small mb-0">Silakan buat kata sandi baru untuk akun Anda</p>
</div>

@if ($errors->any())
    <div class="alert alert-danger py-2 small mb-3">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('password.update') }}" method="POST">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control border-start-0 ps-0" value="{{ old('email', $email) }}" required readonly>
        </div>
    </div>

    <div class="mb-3">
        <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi Baru</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control border-start-0 ps-0" placeholder="Minimal 8 karakter" required autofocus>
        </div>
    </div>

    <div class="mb-4">
        <label for="password_confirmation" class="form-label small fw-semibold text-secondary">Konfirmasi Kata Sandi</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-shield-lock"></i></span>
            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control border-start-0 ps-0" placeholder="Ulangi kata sandi baru" required>
        </div>
    </div>

    <button type="submit" class="btn btn-forest-green w-100 py-2 fw-semibold rounded-pill mb-3 shadow-sm">
        <i class="bi bi-check-circle me-1"></i> Simpan Kata Sandi Baru
    </button>
</form>
@endsection
