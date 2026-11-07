@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Staf - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Manajemen Pengguna</h4>
        <p class="text-muted small mb-0">Kelola akun internal travel, penetapan peran (role), dan status aktif pengguna</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('users.roles') }}" class="btn btn-outline-secondary">
            <i class="bi bi-shield-lock me-1"></i> Hak Akses & Role Matrix
        </a>
        <a href="{{ route('users.create-admin') }}" class="btn btn-forest-green">
            <i class="bi bi-person-plus-fill me-1"></i> Tambah Admin
        </a>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus me-1"></i> Tambah Pengguna Baru
        </a>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('users.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                        value="{{ $search }}" placeholder="Cari Nama Pengguna, Email, atau No Telepon...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="role" class="form-select form-select-sm">
                    <option value="">-- Semua Role Akses --</option>
                    @foreach($roles as $r)
                        <option value="{{ $r->name }}" {{ $role == $r->name ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search || $role)
                    <a href="{{ route('users.index') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Users -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>No Telepon</th>
                        <th>Hak Akses (Role)</th>
                        <th class="text-center">Status</th>
                        <th>Login Terakhir</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $idx => $u)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="avatar-circle bg-primary-subtle text-primary fw-bold" style="width: 36px; height: 36px; border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <strong class="text-dark">{{ $u->name }}</strong>
                                </div>
                            </td>
                            <td><span class="text-secondary small">{{ $u->email }}</span></td>
                            <td><span class="text-secondary small">{{ $u->phone ?? '-' }}</span></td>
                            <td>
                                @foreach($u->roles as $r)
                                    <span class="badge 
                                        @if($r->name == 'Super Admin') bg-dark
                                        @elseif($r->name == 'Admin') bg-primary
                                        @elseif($r->name == 'Finance') bg-success
                                        @elseif($r->name == 'Manager') bg-info text-dark
                                        @else bg-secondary @endif me-1">
                                        {{ $r->name }}
                                    </span>
                                @endforeach
                            </td>
                            <td class="text-center">
                                @if($u->is_active)
                                    <span class="badge bg-success-subtle text-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <span class="small text-muted">{{ $u->last_login_at ? $u->last_login_at->format('d/m/Y H:i') : 'Belum pernah' }}</span>
                            </td>
                            <td class="text-end">
                                <div class="d-flex justify-content-end gap-1">
                                    @if($u->id !== Auth::id())
                                        <form action="{{ route('users.toggle-status', $u) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                title="{{ $u->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}">
                                                <i class="bi {{ $u->is_active ? 'bi-lock' : 'bi-unlock' }}"></i>
                                            </button>
                                        </form>
                                    @endif
                                    <a href="{{ route('users.edit', $u) }}" class="btn btn-sm btn-outline-primary" title="Edit Pengguna">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    @if($u->id !== Auth::id())
                                        <form action="{{ route('users.destroy', $u) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna {{ $u->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Pengguna">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada data pengguna ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $users->links() }}
        </div>
    @endif
</div>
@endsection
