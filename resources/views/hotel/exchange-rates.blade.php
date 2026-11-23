@extends('layouts.app')

@section('title', 'Panel Kurs SAR Live & Multi-Currency')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark"><i class="bi bi-currency-exchange text-warning me-2"></i>Live Exchange Rate Engine</h4>
            <p class="text-muted small mb-0">Pemantauan Kurs Real-time SAR ke IDR, Auto-Refresh, dan Otorisasi Override Manual</p>
        </div>
        <div class="d-flex gap-2">
            <form action="{{ route('hotel.rates.refresh') }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-primary shadow-sm btn-sm">
                    <i class="bi bi-arrow-repeat me-1"></i> Perbarui dari API Sekarang
                </button>
            </form>
            <button type="button" class="btn btn-outline-danger shadow-sm btn-sm" data-bs-toggle="modal" data-bs-target="#overrideModal">
                <i class="bi bi-pencil-square me-1"></i> Override Kurs Manual
            </button>
        </div>
    </div>

    <!-- Active Rate Display Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100 border-top border-4 border-primary">
                <div class="card-body p-4 text-center">
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Kurs Harian Resmi SAR / IDR</span>
                    <h2 class="display-6 fw-bold text-dark mb-2">Rp {{ number_format($currentRate['rate'], 2, ',', '.') }}</h2>
                    
                    @if($currentRate['status'] === 'daily_stable')
                        <span class="badge bg-success-subtle text-success border border-success px-3 py-1 fs-6"><i class="bi bi-calendar2-check me-1"></i> DAILY_STABLE (HARIAN RESMI)</span>
                    @elseif($currentRate['status'] === 'manual')
                        <span class="badge bg-warning text-dark px-3 py-1 fs-6"><i class="bi bi-pencil-fill me-1"></i> MANUAL OVERRIDE</span>
                    @elseif($currentRate['status'] === 'fallback')
                        <span class="badge bg-danger px-3 py-1 fs-6"><i class="bi bi-shield-slash me-1"></i> FALLBACK STANDAR (OFFLINE)</span>
                    @elseif($currentRate['status'] === 'stale')
                        <span class="badge bg-secondary px-3 py-1 fs-6"><i class="bi bi-clock-history me-1"></i> STALE (MELEWATI JADWAL)</span>
                    @else
                        <span class="badge bg-info text-dark px-3 py-1 fs-6">{{ strtoupper($currentRate['status']) }}</span>
                    @endif

                    <hr class="my-3">
                    <div class="text-start small text-muted">
                        <div class="d-flex justify-content-between mb-1">
                            <span>Sumber Penyedia:</span>
                            <strong class="text-dark">{{ $currentRate['provider'] }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Waktu Publikasi Provider:</span>
                            <strong>{{ \Carbon\Carbon::parse($currentRate['fetched_at'])->format('d M Y H:i:s') }} UTC</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-1">
                            <span>Frekuensi Update Provider:</span>
                            <strong>1 Kali / 24 Jam (Jadwal Resmi)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Cache Memori Aplikasi:</span>
                            <strong>5 Menit (Optimasi Server)</strong>
                        </div>
                        <div class="pt-2 border-top text-center" style="font-size: 11px;">
                            <a href="https://www.exchangerate-api.com" target="_blank" rel="noopener" class="text-decoration-none text-muted">
                                Rates By ExchangeRate-API
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-shield-check text-primary me-2"></i>Ketentuan Valas &amp; Transparansi Nilai Tukar</h6>
                </div>
                <div class="card-body">
                    <div class="alert alert-success border-0 bg-success-subtle py-2 small mb-3">
                        <i class="bi bi-check-circle-fill me-1 text-success"></i> <strong>100% Free Open Access:</strong> Sistem menggunakan endpoint gratis resmi tanpa biaya langganan dan tanpa API key.
                    </div>
                    <ul class="text-muted small mb-0 ps-3">
                        <li class="mb-2">
                            <strong>Status DAILY_STABLE:</strong> Kurs valas SAR/IDR diperbarui secara resmi satu kali sehari oleh bank sentral internasional melalui ExchangeRate-API Open Access.
                        </li>
                        <li class="mb-2">
                            <strong>Exchange Rate Snapshot:</strong> Invoice yang diterbitkan mengunci kurs acuan pada tanggal penerbitan. Fluktuasi kurs harian tidak akan mengubah invoice historis.
                        </li>
                        <li class="mb-2">
                            <strong>Transparansi Fallback:</strong> Apabila internet ke API terputus, sistem beralih ke kurs fallback dengan label <code>FALLBACK</code> dan peringatan yang jelas (tidak pernah menyamar sebagai kurs realtime).
                        </li>
                        <li class="mb-0">
                            <strong>Atribusi Terbuka:</strong> Mengikuti ketentuan lisensi Open Access, data nilai tukar diatribusikan secara transparan kepada ExchangeRate-API.
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Rate History Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-clock-history text-secondary me-2"></i>Log Riwayat Kurs &amp; Audit Trail</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Waktu Fetch</th>
                            <th>Mata Uang</th>
                            <th class="text-end">Nilai Tukar</th>
                            <th>Penyedia</th>
                            <th>Status</th>
                            <th>Catatan / Alasan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rateHistory as $rate)
                        <tr>
                            <td>{{ $rate->fetched_at->format('d/m/Y H:i:s') }}</td>
                            <td><strong>{{ $rate->base_currency }} &rarr; {{ $rate->target_currency }}</strong></td>
                            <td class="text-end fw-bold">Rp {{ number_format($rate->rate, 2, ',', '.') }}</td>
                            <td>{{ $rate->provider }}</td>
                            <td>
                                <span class="badge bg-{{ $rate->status === 'live' ? 'success' : ($rate->status === 'manual' ? 'danger' : 'secondary') }}-subtle text-{{ $rate->status === 'live' ? 'success' : ($rate->status === 'manual' ? 'danger' : 'secondary') }} border">
                                    {{ strtoupper($rate->status) }}
                                </span>
                            </td>
                            <td>{{ $rate->override_reason ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat kurs yang tersimpan.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rateHistory->hasPages())
        <div class="card-footer bg-white border-0 py-3">
            {{ $rateHistory->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Override Manual -->
<div class="modal fade" id="overrideModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('hotel.rates.override') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-shield-exclamation text-danger me-2"></i>Otorisasi Override Kurs Manual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-warning py-2 small mb-3">
                        Tindakan ini akan mengesampingkan API otomatis dan tercatat dalam Audit Log perusahaan.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Nilai Kurs SAR ke IDR (Rp) <span class="text-danger">*</span></label>
                        <input type="number" step="0.0001" name="rate" class="form-control" placeholder="Contoh: 4200.0000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Alasan Perubahan / Dokumen Acuan <span class="text-danger">*</span></label>
                        <input type="text" name="override_reason" class="form-control" placeholder="Contoh: Kesepakatan kurs tetap kontrak agen Syahadah" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label fw-semibold small">Durasi Berlaku (Jam)</label>
                        <input type="number" name="duration_hours" class="form-control" value="12" min="1" max="72">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-check-circle me-1"></i> Terapkan Kurs Manual
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
