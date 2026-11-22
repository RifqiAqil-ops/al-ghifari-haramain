@extends('layouts.app')

@section('title', 'Smart Document Intelligence (OCR)')

@section('content')
<div class="container-fluid py-3">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h2 class="h4 mb-1 fw-bold text-dark">
                <i class="bi bi-file-earmark-check text-warning me-2"></i>Smart Document Intelligence (OCR)
            </h2>
            <p class="text-muted small mb-0">Ekstraksi Otomatis KTP &amp; Paspor dengan Alur Verifikasi Manusia (Human-in-the-Loop)</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if($ocrProvider !== 'none')
            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2">
                <i class="bi bi-check-circle-fill me-1"></i> Engine OCR: {{ strtoupper($ocrProvider) }}
            </span>
            @else
            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning border-opacity-25 px-3 py-2">
                <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Engine OCR: Belum Dikonfigurasi
            </span>
            @endif
            <div class="badge bg-light text-dark border px-3 py-2">
                <i class="bi bi-person-check text-success me-1"></i> Human Verification Required
            </div>
        </div>
    </div>

    @if($ocrProvider === 'none')
    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 p-3" style="font-size: 13px;">
        <div class="d-flex align-items-start gap-2">
            <i class="bi bi-info-circle-fill fs-5 mt-1"></i>
            <div>
                <strong>Konfigurasi OCR Engine Diperlukan untuk Pemindaian Berkas Otomatis:</strong><br>
                Sistem tidak menghasilkan data OCR fiktif. Untuk memproses foto KTP dan Paspor asli jamaah, tambahkan <code>GEMINI_API_KEY=kunci_api_anda</code> di file <code>.env</code> hosting cPanel atau pastikan binary <code>tesseract</code> terpasang di server. Dokumen tetap dapat diunggah secara fisik dan diverifikasi manual oleh staf.
            </div>
        </div>
    </div>
    @endif

    <!-- OCR Pending Review Queue -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-inbox-fill text-primary me-2"></i>Antrean Verifikasi Hasil OCR Dokumen ({{ $pendingOcrLogs->total() }})
            </h5>
            <span class="badge bg-warning text-dark">Menunggu Persetujuan Staf</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Jamaah</th>
                            <th>Tipe Dokumen</th>
                            <th>Data Hasil Ekstraksi</th>
                            <th>Skor Keyakinan</th>
                            <th>Status Validasi</th>
                            <th class="text-end">Aksi Otorisasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pendingOcrLogs as $log)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $log->jamaah?->name ?? 'Jamaah' }}</div>
                                <small class="text-muted">{{ $log->jamaah?->registration_number }}</small>
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ strtoupper($log->document_type) }}</span></td>
                            <td>
                                @php $ext = $log->extracted_data; @endphp
                                <div style="font-size: 12px;">
                                    @if(!empty($ext['nik'])) <div><strong>NIK:</strong> {{ $ext['nik'] }}</div> @endif
                                    @if(!empty($ext['passport_number'])) <div><strong>No. Paspor:</strong> {{ $ext['passport_number'] }}</div> @endif
                                    @if(!empty($ext['name'])) <div><strong>Nama:</strong> {{ $ext['name'] }}</div> @endif
                                    @if(!empty($ext['expiry_date'])) <div><strong>Exp:</strong> {{ $ext['expiry_date'] }}</div> @endif
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->confidence_score >= 90 ? 'success' : 'warning text-dark' }}">
                                    {{ $log->confidence_score }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $log->validation_status === 'valid' ? 'success' : ($log->validation_status === 'warning' ? 'warning text-dark' : 'danger') }}">
                                    {{ strtoupper($log->validation_status) }}
                                </span>
                                @if($log->validation_notes)
                                <div class="small text-danger mt-1" style="font-size: 11px;">{{ $log->validation_notes }}</div>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <form action="{{ route('intelligence.documents.approve', $log->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm py-1 px-2" style="font-size: 12px;" onclick="return confirm('Terapkan data hasil ekstraksi ke master jamaah?')">
                                            <i class="bi bi-check-lg me-1"></i> Setujui
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-outline-danger btn-sm py-1 px-2" style="font-size: 12px;" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $log->id }}">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>

                                <!-- Reject Modal -->
                                <div class="modal fade" id="rejectModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content text-start">
                                            <form action="{{ route('intelligence.documents.reject', $log->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold">Tolak Hasil OCR Dokumen</h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <label class="form-label small fw-bold">Alasan Penolakan:</label>
                                                    <input type="text" name="reason" class="form-control form-control-sm" placeholder="Contoh: Foto buram / NIK terpotong" required>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Tolak</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="bi bi-check-circle-fill text-success fs-3 mb-2 d-block"></i>
                                Tidak ada antrean dokumen OCR yang menunggu verifikasi staf.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pendingOcrLogs->hasPages())
            <div class="p-3 border-top">
                {{ $pendingOcrLogs->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Incomplete Documents Audit Across Active Jamaahs -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="card-title mb-0 fw-bold fs-6">
                <i class="bi bi-exclamation-triangle-fill text-danger me-2"></i>Audit Berkas Belum Lengkap pada Jamaah Terdaftar
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover align-middle mb-0" style="font-size: 13px;">
                    <thead class="table-light">
                        <tr>
                            <th>Jamaah</th>
                            <th>No. Telepon</th>
                            <th>Kloter</th>
                            <th>Tanggal Berangkat</th>
                            <th>Berkas yang Belum Lengkap</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($incompleteDocuments as $inc)
                        <tr>
                            <td class="fw-bold">{{ $inc['jamaah_name'] }}</td>
                            <td>{{ $inc['phone'] ?? '-' }}</td>
                            <td><span class="badge bg-light text-dark border">{{ $inc['departure_code'] }}</span></td>
                            <td>{{ $inc['departure_date'] !== '-' ? \Carbon\Carbon::parse($inc['departure_date'])->format('d M Y') : '-' }}</td>
                            <td>
                                @foreach($inc['missing_documents'] as $m)
                                <span class="badge bg-danger">{{ $m }}</span>
                                @endforeach
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Semua jamaah terdaftar telah melengkapi dokumen wajib.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
