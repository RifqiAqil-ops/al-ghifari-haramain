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
            Laporan Penerimaan Kas Pembayaran Jamaah &bull; Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} &bull; Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </th>
    </tr>
    <tr><td colspan="9"></td></tr>

    <!-- TABEL 1: RINGKASAN METODE PEMBAYARAN -->
    <tr>
        <th colspan="4" style="background-color: #C9A45B; color: #000000; font-weight: bold; font-size: 11pt;">
            TABEL 1: RINGKASAN REKAPITULASI METODE BAYAR
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Metode Pembayaran</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Frekuensi Transaksi</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Total Penerimaan (Rp)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Kontribusi (%)</th>
    </tr>
    @php
        $transferPayments = $payments->where('payment_method', 'transfer');
        $cashPayments = $payments->where('payment_method', 'tunai');
        $transferTotal = $transferPayments->sum('amount');
        $cashTotal = $cashPayments->sum('amount');
    @endphp
    <tr>
        <td style="border: 1px solid #CCCCCC;">Transfer Bank / Rekening Resmi</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $transferPayments->count() }} Transaksi</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">Rp {{ number_format($transferTotal, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalAmount > 0 ? round(($transferTotal / $totalAmount) * 100, 1) : 0 }}%</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Pembayaran Tunai (Cash di Kasir)</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $cashPayments->count() }} Transaksi</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">Rp {{ number_format($cashTotal, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalAmount > 0 ? round(($cashTotal / $totalAmount) * 100, 1) : 0 }}%</td>
    </tr>
    <tr style="background-color: #E2E8F0; font-weight: bold;">
        <td style="border: 1px solid #CCCCCC;">TOTAL PENERIMAAN KAS</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $payments->count() }} Transaksi</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; color: #073B2E;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
    </tr>

    <!-- Pemisah Antar Tabel -->
    <tr><td colspan="9"></td></tr>
    <tr><td colspan="9"></td></tr>

    <!-- TABEL 2: RINCIAN LENGKAP TRANSAKSI -->
    <tr>
        <th colspan="9" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            TABEL 2: RINCIAN MUTASI PEMBAYARAN KAS MASUK
        </th>
    </tr>
    <tr>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">NO</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NO. KWITANSI</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">TANGGAL</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NAMA JAMAAH</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">PAKET PERJALANAN</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">TIPE BAYAR</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">METODE</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">NOMINAL (RP)</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">STATUS</th>
    </tr>
    @forelse($payments as $idx => $pay)
        <tr>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #CCCCCC; font-weight: bold;">{{ $pay->receipt_number }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $pay->invoice->registration->jamaah->name ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $pay->invoice->registration->package->name ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center; text-transform: uppercase;">{{ $pay->payment_type }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center; text-transform: uppercase;">{{ $pay->payment_method }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ strtoupper($pay->status) }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="9" style="border: 1px solid #CCCCCC; text-align: center; padding: 10px;">
                Tidak ada data transaksi pembayaran pada periode ini.
            </td>
        </tr>
    @endforelse
    <tr style="background-color: #F5F0E5; font-weight: bold;">
        <td colspan="7" style="border: 1px solid #073B2E; text-align: right;">TOTAL PENERIMAAN:</td>
        <td style="border: 1px solid #073B2E; text-align: right; color: #073B2E;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
        <td style="border: 1px solid #073B2E;"></td>
    </tr>
</table>
