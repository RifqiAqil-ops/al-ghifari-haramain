@extends('layouts.app')

@section('title', 'Hak Akses & Role Matrix - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengguna
        </a>
        <h4 class="fw-bold mb-1 text-dark">Matriks Hak Akses & Peran (RBAC)</h4>
        <p class="text-muted small mb-0">Atur kewenangan izin akses granular per modul menggunakan Spatie Laravel Permission</p>
    </div>
</div>

<div class="row g-4">
    <!-- Role Tabs on the left -->
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm rounded-3">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-primary me-1"></i> Peran (Roles)</h6>
            </div>
            <div class="list-group list-group-flush" id="roleTabs" role="tablist">
                @foreach($roles as $idx => $r)
                    <button class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 {{ $idx === 0 ? 'active' : '' }}" 
                        id="tab-role-{{ $r->id }}" data-bs-toggle="list" data-bs-target="#pane-role-{{ $r->id }}" type="button" role="tab">
                        <div>
                            <strong class="d-block">{{ $r->name }}</strong>
                            <span class="small {{ $idx === 0 ? 'text-white-50' : 'text-muted' }}">{{ $r->permissions->count() }} Izin Terpasang</span>
                        </div>
                        <i class="bi bi-chevron-right small"></i>
                    </button>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Role Permission Form on the right -->
    <div class="col-lg-9">
        <div class="tab-content" id="roleTabContent">
            @foreach($roles as $idx => $r)
                <div class="tab-pane fade {{ $idx === 0 ? 'show active' : '' }}" id="pane-role-{{ $r->id }}" role="tabpanel">
                    <div class="card border-0 shadow-sm rounded-3">
                        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-0 text-dark">Konfigurasi Hak Akses: {{ $r->name }}</h5>
                                <span class="text-muted small">Tentukan izin apa saja yang dapat dilakukan oleh akun dengan peran ini</span>
                            </div>
                            @if($r->name == 'Super Admin')
                                <span class="badge bg-danger rounded-pill px-3 py-1">FULL SUPERADMIN PRIVILEGES</span>
                            @endif
                        </div>
                        <div class="card-body p-4">
                            @if($r->name == 'Super Admin')
                                <div class="alert alert-info small mb-4">
                                    <i class="bi bi-info-circle me-1"></i>
                                    <strong>Super Admin</strong> memiliki bypass otorisasi otomatis terhadap seluruh modul dan izin sistem untuk pemeliharaan inti aplikasi.
                                </div>
                            @endif

                            <form action="{{ route('users.roles.update', $r) }}" method="POST">
                                @csrf
                                @method('PUT')

                                @php $rolePermNames = $r->permissions->pluck('name')->toArray(); @endphp

                                <div class="row g-4">
                                    @foreach($permissions as $groupName => $permList)
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3 h-100 bg-light-subtle">
                                                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                    <h6 class="fw-bold mb-0 text-dark text-capitalize">
                                                        <i class="bi bi-folder2-open text-primary me-1"></i> Modul {{ $groupName }}
                                                    </h6>
                                                    <span class="badge bg-secondary-subtle text-secondary small">{{ $permList->count() }} Izin</span>
                                                </div>
                                                <div class="d-flex flex-column gap-2">
                                                    @foreach($permList as $perm)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="permissions[]" 
                                                                value="{{ $perm->name }}" id="p_{{ $r->id }}_{{ $perm->id }}"
                                                                {{ in_array($perm->name, $rolePermNames) ? 'checked' : '' }}>
                                                            <label class="form-check-label small" for="p_{{ $r->id }}_{{ $perm->id }}">
                                                                <strong class="text-dark">{{ $perm->name }}</strong>
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="bi bi-save me-1"></i> Simpan Hak Akses Role {{ $r->name }}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
