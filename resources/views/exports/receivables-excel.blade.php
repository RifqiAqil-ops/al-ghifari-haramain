<table>
    <!-- Header Perusahaan -->
    <tr>
        <th colspan="9" style="font-size: 16pt; font-weight: bold; color: #073B2E; text-align: left;">
            {{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}
        </th>
    </tr>
    <tr>
        <th colspan="9" style="font-size: 10pt; color: #C9A45B; font-weight: bold; text-align: left;">
            {{ $setting->company_tagline ?? 'UMROH &amp; HAJI SPECIALIST' }}
        </th>
    </tr>
    <tr>
        <th colspan="9" style="font-size: 9pt; color: #555555; text-align: left;">
            Laporan Piutang &amp; Sisa Tagihan Jamaah &bull; Diekspor pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </th>
    </tr>
    <tr><td colspan="9"></td></tr>

    <!-- TABEL 1: RINGKASAN REKAPITULASI PIUTANG -->
    <tr>
        <th colspan="4" style="background-color: #C9A45B; color: #000000; font-weight: bold; font-size: 11pt;">
            TABEL 1: REKAPITULASI TOTAL PIUTANG DAN TAGIHAN
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Parameter Keuangan</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Jumlah Berkas / Nominal</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Rasio (%)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Keterangan</th>
    </tr>
    @php
        $totalInvAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
    @endphp
    <tr>
        <td style="border: 1px solid #CCCCCC;">Total Jamaah Belum Lunas</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">{{ $invoices->count() }} Invoice</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
        <td style="border: 1px solid #CCCCCC;">Status Belum Bayar atau Sebagian</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Total Nilai Tagihan Terbit</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">Rp {{ number_format($totalInvAmount, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
        <td style="border: 1px solid #CCCCCC;">Total komitmen paket</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Uang Muka / Cicilan Diterima (DP)</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; color: #198754; font-weight: bold;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalInvAmount > 0 ? round(($totalPaid / $totalInvAmount) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Dana masuk ke rekening</td>
    </tr>
    <tr style="background-color: #E2E8F0; font-weight: bold;">
        <td style="border: 1px solid #CCCCCC;">TOTAL SISA PIUTANG TRAVEL</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; color: #DC3545; font-weight: bold;">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalInvAmount > 0 ? round(($totalReceivable / $totalInvAmount) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Potensi penerimaan piutang</td>
    </tr>

    <!-- Pemisah Antar Tabel -->
    <tr><td colspan="9"></td></tr>
    <tr><td colspan="9"></td></tr>

    <!-- TABEL 2: RINCIAN LENGKAP -->
    <tr>
        <th colspan="9" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            TABEL 2: RINCIAN TAGIHAN BELUM LUNAS PER JAMAAH
        </th>
    </tr>
    <tr>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">NO</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NO. INVOICE</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NAMA JAMAAH</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NO. WHATSAPP</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">PAKET PERJALANAN</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">TOTAL TAGIHAN (RP)</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">TERBAYAR (RP)</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">SISA PIUTANG (RP)</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">JATUH TEMPO</th>
    </tr>
    @forelse($invoices as $idx => $inv)
        <tr>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #CCCCCC; font-weight: bold;">{{ $inv->invoice_number }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $inv->registration->jamaah->name ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; mso-number-format:'\@';">{{ $inv->registration->jamaah->phone ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $inv->registration->package->name ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right;">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #198754;">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #DC3545;">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" style="border: 1px solid #CCCCCC; text-align: center; padding: 10px;">
                Alhamdulillah, tidak ada sisa piutang / seluruh jamaah telah melunasi pembayaran.
            </td>
        </tr>
    @endforelse
    <tr style="background-color: #F5F0E5; font-weight: bold;">
        <td colspan="7" style="border: 1px solid #073B2E; text-align: right;">TOTAL SISA PIUTANG:</td>
        <td style="border: 1px solid #073B2E; text-align: right; color: #DC3545;">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</td>
        <td style="border: 1px solid #073B2E;"></td>
    </tr>
</table>
