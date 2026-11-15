@extends('layouts.app')

@section('title', 'Distribusi Perlengkapan Baru')
@section('header_title', 'Form Distribusi Perlengkapan')

@section('content')
<div class="mb-4">
    <a href="{{ route('equipment.distributions.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Distribusi
    </a>
</div>

<form action="{{ route('equipment.distributions.store') }}" method="POST" id="distributionForm">
    @csrf

    <div class="row g-4">
        <!-- Kolom Kiri: Informasi Penerima & Jadwal -->
        <div class="col-lg-4">
            <div class="card card-custom h-100">
                <div class="card-header bg-forest-green text-royal-gold py-3">
                    <h6 class="card-title mb-0 fw-bold">
                        <i class="bi bi-person-badge me-2"></i>Informasi Penerima
                    </h6>
                </div>
                <div class="card-body p-4">
                    <!-- Pilih Jamaah -->
                    <div class="mb-3">
                        <label for="jamaah_id" class="form-label fw-semibold">Pilih Jamaah <span class="text-danger">*</span></label>
                        <select name="jamaah_id" id="jamaah_id" class="form-select @error('jamaah_id') is-invalid @enderror" required>
                            <option value="">-- Pilih Jamaah --</option>
                            @foreach($jamaahs as $j)
                                <option value="{{ $j->id }}"
                                    data-name="{{ $j->name }}"
                                    data-nik="{{ $j->nik }}"
                                    {{ (old('jamaah_id', $selectedJamaahId) == $j->id) ? 'selected' : '' }}>
                                    {{ $j->name }} (NIK: {{ $j->nik ?? '-' }})
                                </option>
                            @endforeach
                        </select>
                        @error('jamaah_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Keberangkatan Terkait (Opsional) -->
                    <div class="mb-3">
                        <label for="departure_id" class="form-label fw-semibold">Jadwal Keberangkatan <small class="text-muted fw-normal">(Opsional)</small></label>
                        <select name="departure_id" id="departure_id" class="form-select @error('departure_id') is-invalid @enderror">
                            <option value="">-- Tidak Terikat Keberangkatan Tertentu --</option>
                            @foreach($departures as $dep)
                                <option value="{{ $dep->id }}"
                                    {{ (old('departure_id', $suggestedDepartureId) == $dep->id) ? 'selected' : '' }}>
                                    {{ $dep->departure_code ?? 'DEP-'.$dep->id }} - {{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '' }} ({{ $dep->package->name ?? 'Paket' }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text">Dapat dikaitkan dengan jadwal keberangkatan aktif jamaah.</div>
                        @error('departure_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Tanggal Distribusi -->
                    <div class="mb-3">
                        <label for="distribution_date" class="form-label fw-semibold">Tanggal Distribusi <span class="text-danger">*</span></label>
                        <input type="date" name="distribution_date" id="distribution_date"
                            class="form-control @error('distribution_date') is-invalid @enderror"
                            value="{{ old('distribution_date', date('Y-m-d')) }}" required>
                        @error('distribution_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Penerima Fisik -->
                    <div class="mb-3">
                        <label for="received_by" class="form-label fw-semibold">Nama Penerima Fisik <small class="text-muted fw-normal">(Bila diwakilkan)</small></label>
                        <input type="text" name="received_by" id="received_by"
                            class="form-control @error('received_by') is-invalid @enderror"
                            placeholder="Nama yang mengambil / menerima barang"
                            value="{{ old('received_by', $selectedJamaah?->name) }}">
                        <div class="form-text">Kosongkan bila diambil langsung oleh jamaah yang bersangkutan.</div>
                        @error('received_by')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Catatan Tambahan -->
                    <div class="mb-3">
                        <label for="notes" class="form-label fw-semibold">Catatan Umum</label>
                        <textarea name="notes" id="notes" rows="3"
                            class="form-control @error('notes') is-invalid @enderror"
                            placeholder="Contoh: Pengambilan tahap 1 di kantor cabang">{{ old('notes') }}</textarea>
                        @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info py-2 px-3 small mb-0 border-0 bg-info-subtle text-dark">
                        <i class="bi bi-info-circle me-1"></i>
                        Distribusi dilakukan secara fleksibel dan bertahap. Jamaah dapat menerima sebagian barang sekarang dan sisanya di kemudian hari.
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Pilihan Barang Perlengkapan -->
        <div class="col-lg-8">
            <div class="card card-custom h-100">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="card-title mb-0 fw-bold text-dark">
                            <i class="bi bi-boxes me-2 text-forest"></i>Pilih Perlengkapan yang Diserahkan
                        </h6>
                        <small class="text-muted">Centang barang yang akan diserahkan dan tentukan jumlahnya</small>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-forest" id="btnSelectAll">
                            <i class="bi bi-check-all me-1"></i> Pilih Semua Ready
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btnUnselectAll">
                            Reset Pilihan
                        </button>
                    </div>
                </div>

                <div class="card-body p-0">
                    @error('items')
                        <div class="alert alert-danger m-3 mb-0 py-2">
                            <i class="bi bi-exclamation-triangle me-1"></i> {{ $message }}
                        </div>
                    @enderror

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 45px;" class="text-center">#</th>
                                    <th>Nama Perlengkapan</th>
                                    <th style="width: 140px;" class="text-center">Stok Tersedia</th>
                                    <th style="width: 130px;" class="text-center">Jumlah Diserahkan</th>
                                    <th style="width: 200px;">Keterangan Item (Opsional)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($equipments as $index => $item)
                                    @php
                                        $isAvailable = $item->stock > 0;
                                        $oldItem = old("items.{$index}");
                                        $isChecked = old("items.{$index}.equipment_id") == $item->id;
                                    @endphp
                                    <tr class="item-row {{ !$isAvailable ? 'table-secondary opacity-75' : '' }}" data-item-id="{{ $item->id }}">
                                        <td class="text-center">
                                            <input type="checkbox"
                                                class="form-check-input item-check"
                                                id="check_{{ $item->id }}"
                                                data-target="item_{{ $index }}"
                                                {{ !$isAvailable ? 'disabled' : '' }}
                                                {{ $isChecked ? 'checked' : '' }}>
                                        </td>
                                        <td>
                                            <label for="check_{{ $item->id }}" class="fw-semibold text-dark mb-0 d-block cursor-pointer">
                                                {{ $item->name }}
                                            </label>
                                            <div class="d-flex align-items-center gap-2 mt-1">
                                                <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                                    {{ $item->category->name ?? 'Umum' }}
                                                </span>
                                                <small class="text-muted">Kode: {{ $item->code }}</small>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($item->stock > 0)
                                                <span class="badge {{ $item->is_low_stock ? 'bg-warning-subtle text-warning border border-warning-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} px-2 py-1">
                                                    {{ $item->stock }} {{ $item->unit }}
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                    Habis (0 {{ $item->unit }})
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <!-- Hidden equipment_id yang diaktifkan hanya jika checkbox dicentang -->
                                            <input type="hidden"
                                                name="items[{{ $index }}][equipment_id]"
                                                id="equipment_id_{{ $index }}"
                                                value="{{ $item->id }}"
                                                {{ !$isChecked ? 'disabled' : '' }}>

                                            <div class="input-group input-group-sm">
                                                <input type="number"
                                                    name="items[{{ $index }}][quantity]"
                                                    id="qty_{{ $index }}"
                                                    class="form-control text-center item-qty"
                                                    value="{{ old("items.{$index}.quantity", 1) }}"
                                                    min="1"
                                                    max="{{ $item->stock }}"
                                                    {{ !$isChecked ? 'disabled' : '' }}
                                                    required>
                                                <span class="input-group-text bg-light text-muted">{{ $item->unit }}</span>
                                            </div>
                                            @error("items.{$index}.quantity")
                                                <div class="text-danger small mt-1">{{ $message }}</div>
                                            @enderror
                                        </td>
                                        <td>
                                            <input type="text"
                                                name="items[{{ $index }}][notes]"
                                                id="note_{{ $index }}"
                                                class="form-control form-control-sm item-note"
                                                placeholder="Contoh: Ukuran L, dll"
                                                value="{{ old("items.{$index}.notes") }}"
                                                {{ !$isChecked ? 'disabled' : '' }}>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">
                                            Tidak ada data perlengkapan aktif. Silakan tambahkan barang di modul Inventaris terlebih dahulu.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-light p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <span class="text-muted small">Barang dipilih:</span>
                        <span class="badge bg-forest-green text-royal-gold fs-6 ms-1" id="selectedCountBadge">0 Item</span>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ route('equipment.distributions.index') }}" class="btn btn-outline-secondary">
                            Batal
                        </a>
                        <button type="submit" class="btn btn-forest text-white fw-bold px-4" id="btnSubmit">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Transaksi Distribusi
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const jamaahSelect = document.getElementById('jamaah_id');
    const receivedByInput = document.getElementById('received_by');
    const checkboxes = document.querySelectorAll('.item-check');
    const selectedCountBadge = document.getElementById('selectedCountBadge');
    const btnSelectAll = document.getElementById('btnSelectAll');
    const btnUnselectAll = document.getElementById('btnUnselectAll');
    const form = document.getElementById('distributionForm');

    // Auto-fill nama penerima bila kosong saat pilih jamaah
    if (jamaahSelect) {
        jamaahSelect.addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const name = selectedOption.getAttribute('data-name');
            if (name && (!receivedByInput.value || receivedByInput.dataset.autofilled === 'true')) {
                receivedByInput.value = name;
                receivedByInput.dataset.autofilled = 'true';
            }
        });
    }

    if (receivedByInput) {
        receivedByInput.addEventListener('input', function () {
            this.dataset.autofilled = 'false';
        });
    }

    function updateItemState(checkbox) {
        const row = checkbox.closest('tr');
        const eqInput = row.querySelector('input[type="hidden"]');
        const qtyInput = row.querySelector('.item-qty');
        const noteInput = row.querySelector('.item-note');

        if (checkbox.checked) {
            eqInput.removeAttribute('disabled');
            qtyInput.removeAttribute('disabled');
            noteInput.removeAttribute('disabled');
            row.classList.add('table-light');
        } else {
            eqInput.setAttribute('disabled', 'disabled');
            qtyInput.setAttribute('disabled', 'disabled');
            noteInput.setAttribute('disabled', 'disabled');
            row.classList.remove('table-light');
        }
        updateCount();
    }

    function updateCount() {
        let count = 0;
        checkboxes.forEach(cb => {
            if (cb.checked) count++;
        });
        selectedCountBadge.textContent = count + ' Item';
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function () {
            updateItemState(this);
        });
        // Initial state
        if (cb.checked) {
            updateItemState(cb);
        }
    });

    if (btnSelectAll) {
        btnSelectAll.addEventListener('click', function () {
            checkboxes.forEach(cb => {
                if (!cb.disabled) {
                    cb.checked = true;
                    updateItemState(cb);
                }
            });
        });
    }

    if (btnUnselectAll) {
        btnUnselectAll.addEventListener('click', function () {
            checkboxes.forEach(cb => {
                cb.checked = false;
                updateItemState(cb);
            });
        });
    }

    if (form) {
        form.addEventListener('submit', function (e) {
            let checkedCount = 0;
            checkboxes.forEach(cb => {
                if (cb.checked) checkedCount++;
            });

            if (checkedCount === 0) {
                e.preventDefault();
                alert('Pilih minimal satu perlengkapan yang diserahkan.');
                return false;
            }
        });
    }

    updateCount();
});
</script>
@endsection
