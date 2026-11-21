<table>
    <!-- Header Perusahaan -->
    <tr>
        <th colspan="12" style="font-size: 16pt; font-weight: bold; color: #073B2E; text-align: left;">
            {{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}
        </th>
    </tr>
    <tr>
        <th colspan="12" style="font-size: 10pt; color: #C9A45B; font-weight: bold; text-align: left;">
            {{ $setting->company_tagline ?? 'UMROH &amp; HAJI SPECIALIST' }}
        </th>
    </tr>
    <tr>
        <th colspan="12" style="font-size: 9pt; color: #555555; text-align: left;">
            Laporan Laba Rugi per Kloter (Sebelum &amp; Setelah Alokasi Biaya Umum) &bull; Periode: {{ $report['start_date'] ? \Carbon\Carbon::parse($report['start_date'])->format('d/m/Y') : 'Awal' }} s/d {{ $report['end_date'] ? \Carbon\Carbon::parse($report['end_date'])->format('d/m/Y') : 'Akhir' }} &bull; Metode: {{ $report['allocation_method_label'] }} &bull; Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </th>
    </tr>
    <tr><td colspan="12"></td></tr>

    <!-- TABEL 1: RINGKASAN EKSEKUTIF -->
    <tr>
        <th colspan="12" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            RINGKASAN EKSEKUTIF KEUANGAN
        </th>
    </tr>
    <tr>
        <th colspan="6" style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">INDIKATOR KINERJA KEUANGAN</th>
        <th colspan="6" style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">NILAI (RP)</th>
    </tr>
    <tr>
        <td colspan="6" style="border: 1px solid #CCCCCC;">Total Pendapatan Kontrak Jamaah Aktif</td>
        <td colspan="6" style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #198754;">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="6" style="border: 1px solid #CCCCCC;">Total Biaya Operasional Langsung Kloter</td>
        <td colspan="6" style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #DC3545;">Rp {{ number_format($report['total_direct_expense'], 0, ',', '.') }}</td>
    </tr>
    <tr style="background-color: #E8F5E9; font-weight: bold;">
        <td colspan="6" style="border: 1px solid #CCCCCC;">Total Laba Sebelum Alokasi Biaya Umum</td>
        <td colspan="6" style="border: 1px solid #CCCCCC; text-align: right; color: #0D6EFD;">Rp {{ number_format($report['total_profit_before'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="6" style="border: 1px solid #CCCCCC;">Total Biaya Operasional Umum / Kantor (Overhead)</td>
        <td colspan="6" style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">Rp {{ number_format($report['total_general_expense'], 0, ',', '.') }}</td>
    </tr>
    <tr>
        <td colspan="6" style="border: 1px solid #CCCCCC;">Biaya Umum Kantor yang Teralokasikan</td>
        <td colspan="6" style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #FD7E14;">Rp {{ number_format($report['allocated_general_expense'], 0, ',', '.') }}</td>
    </tr>
    <tr style="background-color: #E8F5E9; font-weight: bold;">
        <td colspan="6" style="border: 1px solid #CCCCCC;">Total Laba Bersih Akhir Setelah Alokasi Biaya Umum</td>
        <td colspan="6" style="border: 1px solid #CCCCCC; text-align: right; color: {{ $report['total_profit_after'] >= 0 ? '#198754' : '#DC3545' }};">
            Rp {{ number_format($report['total_profit_after'], 0, ',', '.') }}
        </td>
    </tr>

    <!-- Pemisah -->
    <tr><td colspan="12"></td></tr>
    <tr><td colspan="12"></td></tr>

    <!-- TABEL 2: ANALISIS PER KLOTER -->
    <tr>
        <th colspan="12" style="background-color: #C9A45B; color: #000000; font-weight: bold; font-size: 11pt;">
            TABEL ANALISIS LABA RUGI PER KLOTER KEBERANGKATAN
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 40px; text-align: center;">NO</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 120px;">KODE KLOTER</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 180px;">NAMA KLOTER</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 150px;">PAKET</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 100px; text-align: center;">TGL BERANGKAT</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 80px; text-align: center;">JAMAAH AKTIF</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 140px; text-align: right;">PENDAPATAN (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 140px; text-align: right;">BIAYA LANGSUNG (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 140px; text-align: right;">LABA SBLM ALOKASI (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 140px; text-align: right;">ALOKASI BIAYA UMUM (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 140px; text-align: right;">LABA STLH ALOKASI (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 90px; text-align: center;">STATUS</th>
    </tr>

    @php $kNo = 1; @endphp
    @forelse($report['kloters'] as $item)
        @php $d = $item['departure']; @endphp
        <tr>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $kNo++ }}</td>
            <td style="border: 1px solid #CCCCCC; font-weight: bold;">{{ $d->code }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $d->name }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $d->package->name ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ \Carbon\Carbon::parse($d->departure_date)->format('d/m/Y') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center; font-weight: bold;">{{ $item['active_jamaah_count'] }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #198754; font-weight: bold;">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #DC3545; font-weight: bold;">Rp {{ number_format($item['direct_expense'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #0D6EFD; font-weight: bold;">Rp {{ number_format($item['profit_before_allocation'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">Rp {{ number_format($item['allocated_general_expense'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: {{ $item['profit_after_allocation'] >= 0 ? '#198754' : '#DC3545' }}; font-weight: bold;">
                Rp {{ number_format($item['profit_after_allocation'], 0, ',', '.') }}
            </td>
            <td style="border: 1px solid #CCCCCC; text-align: center; font-weight: bold; color: {{ $item['status_profit'] === 'untung' ? '#198754' : ($item['status_profit'] === 'rugi' ? '#DC3545' : '#6C757D') }};">
                {{ ucfirst($item['status_profit']) }}
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="12" style="border: 1px solid #CCCCCC; text-align: center; padding: 10px;">
                Tidak ada data kloter keberangkatan pada periode ini.
            </td>
        </tr>
    @endforelse

    @if($report['kloters']->isNotEmpty())
        <tr style="background-color: #E8F5E9; font-weight: bold;">
            <td colspan="5" style="border: 1px solid #CCCCCC; text-align: right;">TOTAL REKAPITULASI:</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $report['total_active_jamaah'] }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #198754;">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #DC3545;">Rp {{ number_format($report['total_direct_expense'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #0D6EFD;">Rp {{ number_format($report['total_profit_before'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right;">Rp {{ number_format($report['allocated_general_expense'], 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: {{ $report['total_profit_after'] >= 0 ? '#198754' : '#DC3545' }};">
                Rp {{ number_format($report['total_profit_after'], 0, ',', '.') }}
            </td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">
                {{ $report['total_profit_after'] >= 0 ? 'UNTUNG' : 'RUGI' }}
            </td>
        </tr>
    @endif
</table>
