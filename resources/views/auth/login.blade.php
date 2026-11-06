@extends('layouts.auth')

@section('title', 'Masuk Internal Staff')

@section('content')
<div class="text-center mb-4">
    <h5 class="fw-bold text-dark mb-1">Masuk Portal Internal</h5>
    <p class="text-muted small mb-0">Silakan masukkan kredensial akun operasional Anda</p>
</div>

@if(session('toast_info'))
    <div class="alert alert-info py-2 small mb-3">
        {{ session('toast_info') }}
    </div>
@endif

@if(session('toast_success'))
    <div class="alert alert-success py-2 small mb-3">
        {{ session('toast_success') }}
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger py-2 small mb-3">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

<form action="{{ route('login') }}" method="POST" autocomplete="off">
    @csrf

    <div class="mb-3">
        <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
            <input type="email" name="email" id="email" class="form-control border-start-0 ps-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@alghifari.com" required autofocus autocomplete="username">
        </div>
    </div>

    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <label for="password" class="form-label small fw-semibold text-secondary mb-0">Kata Sandi</label>
            <a href="{{ route('password.request') }}" class="small text-decoration-none text-royal-gold fw-semibold">Lupa Kata Sandi?</a>
        </div>
        <div class="input-group">
            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
            <input type="password" name="password" id="password" class="form-control border-start-0 ps-0 @error('password') is-invalid @enderror" placeholder="••••••••" required autocomplete="current-password">
        </div>
    </div>

    <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
        <label class="form-check-label small text-muted" for="remember">
            Ingat saya di perangkat ini
        </label>
    </div>

    <button type="submit" class="btn btn-forest-green w-full py-2 fw-semibold rounded-pill w-100 mb-3 shadow-sm">
        <i class="bi bi-box-arrow-in-right me-1"></i> Masuk ke Dashboard
    </button>
</form>

@endsection
