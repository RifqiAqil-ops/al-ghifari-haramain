@extends('layouts.app')

@section('title', 'Edit Pengeluaran Operasional - ' . $expense->expense_number)

@section('content')
<div class="container-fluid px-0" style="max-width: 900px; margin: 0 auto;">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="{{ route('finance.expenses.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pengeluaran
            </a>
            <h4 class="fw-bold mb-1 text-dark">Edit Pengeluaran Operasional</h4>
            <p class="text-muted small mb-0 font-monospace">Nomor Transaksi: <span class="fw-semibold text-dark">{{ $expense->expense_number }}</span> &bull; Dicatat oleh: {{ $expense->recorder->name ?? '-' }}</p>
        </div>
        <div>
            <span class="badge {{ $expense->isDirect() ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-secondary-subtle text-secondary border' }} px-3 py-2 fs-6">
                {{ $expense->expense_type_label }}
            </span>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger py-2 small mb-4">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="bi bi-pencil-square text-danger me-2"></i>Formulir Perubahan Beban Pengeluaran
            </h6>
        </div>
        <div class="card-body p-4">
            <form action="{{ route('finance.expenses.update', $expense) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold small">Nama / Judul Pengeluaran <span class="text-danger">*</span></label>
                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror"
                            value="{{ old('title', $expense->title) }}" required placeholder="Contoh: Booking Tiket Saudia Group PNR Kloter November">
                        @error('title')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-semibold small">Nominal Pengeluaran (Rp) <span class="text-danger">*</span></label>
                        <input type="number" name="amount" class="form-control fw-bold text-danger @error('amount') is-invalid @enderror"
                            value="{{ old('amount', (int) $expense->amount) }}" min="1000" required placeholder="Contoh: 15000000">
                        @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Kategori Beban <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Kategori Beban --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $expense->category_id) == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Jenis Pengeluaran <span class="text-danger">*</span></label>
                        <select name="expense_type" id="edit_expense_type" class="form-select @error('expense_type') is-invalid @enderror"
                            required onchange="toggleEditDepartureField(this.value)">
                            <option value="langsung" {{ old('expense_type', $expense->expense_type ?? ($expense->departure_id ? 'langsung' : 'operasional_umum')) === 'langsung' ? 'selected' : '' }}>
                                Pengeluaran Langsung (Terkait Kloter Tertentu)
                            </option>
                            <option value="operasional_umum" {{ old('expense_type', $expense->expense_type ?? ($expense->departure_id ? 'langsung' : 'operasional_umum')) === 'operasional_umum' ? 'selected' : '' }}>
                                Pengeluaran Operasional Umum (Overhead Perusahaan)
                            </option>
                        </select>
                        @error('expense_type')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-12" id="edit_departure_container">
                        <label class="form-label fw-semibold small">
                            Alokasi Kloter Keberangkatan <span class="text-danger" id="departure_required_star">*</span>
                        </label>
                        <select name="departure_id" id="edit_departure_id" class="form-select @error('departure_id') is-invalid @enderror">
                            <option value="">-- Pilih Kloter Keberangkatan --</option>
                            @foreach($departures as $dep)
                                <option value="{{ $dep->id }}" {{ old('departure_id', $expense->departure_id) == $dep->id ? 'selected' : '' }}>
                                    {{ $dep->code }} &bull; {{ $dep->package->name ?? '-' }} (Berangkat: {{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '-' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text small text-muted">
                            Pengeluaran langsung hanya akan dibebankan pada perhitungan laba rugi kloter ini.
                        </div>
                        @error('departure_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Tanggal Transaksi <span class="text-danger">*</span></label>
                        <input type="date" name="expense_date" class="form-control @error('expense_date') is-invalid @enderror"
                            value="{{ old('expense_date', $expense->expense_date ? $expense->expense_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        @error('expense_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Metode Pembayaran <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select @error('payment_method') is-invalid @enderror" required>
                            <option value="transfer" {{ old('payment_method', $expense->payment_method) === 'transfer' ? 'selected' : '' }}>Transfer Bank</option>
                            <option value="tunai" {{ old('payment_method', $expense->payment_method) === 'tunai' ? 'selected' : '' }}>Kas Tunai / Cash</option>
                        </select>
                        @error('payment_method')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Ganti Bukti Nota / Kuitansi (Opsional)</label>
                        <input type="file" name="proof_file" class="form-control @error('proof_file') is-invalid @enderror" accept="image/*,.pdf">
                        <div class="form-text small">Kosongkan jika tidak ingin mengubah bukti. Format JPG, PNG, atau PDF (Max: 3MB)</div>
                        @if($expense->proof_file)
                            <div class="mt-2">
                                <a href="{{ asset('storage/' . $expense->proof_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                    <i class="bi bi-file-earmark-image me-1"></i> Lihat Bukti Nota Saat Ini
                                </a>
                            </div>
                        @endif
                        @error('proof_file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Catatan Tambahan</label>
                        <textarea name="notes" rows="2" class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Keterangan vendor, nomor invoice supplier, rincian keperluan, dsb.">{{ old('notes', $expense->notes) }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 border-top pt-3 mt-4">
                    <a href="{{ route('finance.expenses.index') }}" class="btn btn-light px-4 border">
                        Batal
                    </a>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan Pengeluaran
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function toggleEditDepartureField(type) {
        const container = document.getElementById('edit_departure_container');
        const select = document.getElementById('edit_departure_id');
        const star = document.getElementById('departure_required_star');

        if (type === 'langsung') {
            container.style.display = 'block';
            select.required = true;
            if (star) star.style.display = 'inline';
        } else {
            container.style.display = 'none';
            select.required = false;
            select.value = '';
            if (star) star.style.display = 'none';
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        const typeSelect = document.getElementById('edit_expense_type');
        if (typeSelect) {
            toggleEditDepartureField(typeSelect.value);
        }
    });
</script>
@endpush
