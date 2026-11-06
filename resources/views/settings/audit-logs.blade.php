@extends('layouts.app')

@section('title', 'Audit Log Sistem - Al Ghifari Haramain')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
    <div>
        <h4 class="fw-bold mb-1 text-dark">Audit Log & Rekam Jejak Aktivitas</h4>
        <p class="text-muted small mb-0">Catatan kronologis seluruh perubahan data, transaksi keuangan, dan aksi staf sistem</p>
    </div>
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-sm rounded-3 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('audit-logs.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-5">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                        value="{{ $search }}" placeholder="Cari Aktivitas, Nama Staf, atau IP Address...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="module" class="form-select form-select-sm">
                    <option value="">-- Semua Modul --</option>
                    @foreach($modules as $m)
                        <option value="{{ $m }}" {{ $module == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="action" class="form-select form-select-sm">
                    <option value="">-- Semua Aksi --</option>
                    @foreach($actions as $a)
                        <option value="{{ $a }}" {{ $action == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary btn-sm px-3">Filter</button>
                @if($search || $module || $action)
                    <a href="{{ route('audit-logs.index') }}" class="btn btn-light btn-sm text-secondary">Reset</a>
                @endif
            </div>
        </form>
    </div>
</div>

<!-- Table Logs -->
<div class="card border-0 shadow-sm rounded-3">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Waktu (WIB)</th>
                        <th>Pengguna / Staf</th>
                        <th>Aksi</th>
                        <th>Modul</th>
                        <th>Deskripsi Aktivitas</th>
                        <th>IP Address</th>
                        <th class="text-end">Rincian</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $idx => $l)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td>
                                <span class="small fw-semibold text-dark">{{ $l->created_at ? $l->created_at->format('d/m/Y H:i:s') : '-' }}</span>
                            </td>
                            <td>
                                <strong class="text-dark">{{ $l->user_name ?? 'Sistem / Anonim' }}</strong>
                            </td>
                            <td>
                                <span class="badge 
                                    @if(str_contains(strtoupper($l->action), 'CREATE')) bg-success-subtle text-success
                                    @elseif(str_contains(strtoupper($l->action), 'DELETE') || str_contains(strtoupper($l->action), 'CANCEL')) bg-danger-subtle text-danger
                                    @elseif(str_contains(strtoupper($l->action), 'UPDATE')) bg-primary-subtle text-primary
                                    @else bg-secondary-subtle text-secondary @endif text-uppercase">
                                    {{ $l->action }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $l->module }}</span>
                            </td>
                            <td style="max-width: 320px;">
                                <span class="small text-secondary">{{ $l->description }}</span>
                            </td>
                            <td>
                                <code class="small text-muted">{{ $l->ip_address ?? '-' }}</code>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('audit-logs.show', $l) }}" class="btn btn-sm btn-outline-primary" title="Lihat Perubahan Nilai">
                                    <i class="bi bi-eye me-1"></i> Diff
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                Tidak ada rekam jejak audit log yang cocok.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
        <div class="card-footer bg-white border-top py-3">
            {{ $logs->links() }}
        </div>
    @endif
</div>
@endsection
