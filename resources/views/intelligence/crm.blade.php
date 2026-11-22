@extends('layouts.app')

@section('title', 'Smart CRM & Jamaah 360')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-people-fill text-primary me-2"></i>Smart CRM &amp; Jamaah 360
            </h2>
            <p class="text-muted small mb-0">Segmentasi Cerdas, Antrean Follow-Up Otomatis, dan Riwayat Komunikasi Terintegrasi</p>
        </div>
        <div class="badge bg-light text-dark border px-3 py-2">
            <i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Service Connected
        </div>
    </div>

    <!-- Smart Follow-Up Queue Alert Feed -->
    @if(!empty($followUpQueue))
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-bell-fill text-danger me-2"></i>Antrean Prioritas Tindak Lanjut (Follow-Up Queue)
            </h5>
            <span class="badge bg-danger">{{ count($followUpQueue) }} Kontak Mendesak</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Prioritas</th>
                            <th>Jamaah</th>
                            <th>Kebutuhan Tindak Lanjut</th>
                            <th>Target / Jatuh Tempo</th>
                            <th class="text-end">Tindakan Cepat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(array_slice($followUpQueue, 0, 6) as $item)
                        <tr>
                            <td><span class="badge bg-{{ $item['badge'] }}">{{ $item['priority'] }}</span></td>
                            <td>
                                <div class="fw-bold">{{ $item['jamaah_name'] }}</div>
                                <small class="text-muted">{{ $item['phone'] }}</small>
                            </td>
                            <td>
                                <div>{{ $item['title'] }}</div>
                                @if($item['amount'] > 0)
                                <small class="text-danger fw-semibold">Nominal: Rp {{ number_format($item['amount'], 0, ',', '.') }}</small>
                                @endif
                            </td>
                            <td>{{ $item['target_date'] ? \Carbon\Carbon::parse($item['target_date'])->format('d M Y') : '-' }}</td>
                            <td class="text-end">
                                <button type="button" class="btn btn-outline-success btn-sm py-1 px-2" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#waModal{{ $loop->index }}">
                                    <i class="bi bi-whatsapp me-1"></i> Hubungi WA
                                </button>

                                <!-- WhatsApp Send Modal -->
                                <div class="modal fade text-start" id="waModal{{ $loop->index }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <form action="{{ route('intelligence.crm.follow-up') }}" method="POST">
                                                @csrf
                                                <input type="hidden" name="jamaah_id" value="{{ $item['jamaah_id'] }}">
                                                <input type="hidden" name="phone" value="{{ $item['phone'] }}">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Kirim Pesan WhatsApp CRM</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-2">
                                                        <label class="small text-muted d-block">Penerima:</label>
                                                        <strong>{{ $item['jamaah_name'] }} ({{ $item['phone'] }})</strong>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">Pesan WhatsApp:</label>
                                                        <textarea name="message" class="form-control form-control-sm" rows="4" required>Assalamu'alaikum Wr. Wb. Bpk/Ibu {{ $item['jamaah_name'] }}, mengingatkan kembali terkait {{ $item['title'] }} bersama Al Ghifari Haramain. Terima kasih.</textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="bi bi-send-fill me-1"></i> Kirim WhatsApp
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Jamaah Directory & Segmentation -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-person-lines-fill text-primary me-2"></i>Direktori Jamaah &amp; Profil 360
            </h5>
            <form action="{{ route('intelligence.crm.index') }}" method="GET" class="d-flex gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Cari nama / telepon / no. reg..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-primary btn-sm px-3">Cari</button>
            </form>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>No. Registrasi</th>
                            <th>Nama Jamaah</th>
                            <th>No. Telepon</th>
                            <th>Program / Kloter Terakhir</th>
                            <th>Total Perjalanan</th>
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($jamaahs as $j)
                        <tr>
                            <td class="fw-bold">{{ $j->registration_number }}</td>
                            <td>{{ $j->name }}</td>
                            <td>{{ $j->phone ?? '-' }}</td>
                            <td>
                                @php $lastReg = $j->registrations->first(); @endphp
                                @if($lastReg)
                                <div>{{ $lastReg->package?->name ?? 'Paket' }}</div>
                                <small class="text-muted">Kloter: {{ $lastReg->departure?->code ?? '-' }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">{{ $j->registrations->count() }} Kali</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('intelligence.crm.profile', $j->id) }}" class="btn btn-outline-primary btn-sm py-1 px-2" style="font-size: 12px;">
                                    <i class="bi bi-eye-fill me-1"></i> Profil 360
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Data jamaah tidak ditemukan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($jamaahs->hasPages())
            <div class="p-3 border-top">
                {{ $jamaahs->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
