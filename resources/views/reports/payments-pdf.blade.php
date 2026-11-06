<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penerimaan Kas Pembayaran</title>
    <style>
        @page {
            margin: 20px 25px;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2D3748;
            font-size: 8.5pt;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #073B2E;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .logo-img { max-height: 48px; }
        .company-title {
            font-size: 15px;
            font-weight: bold;
            color: #073B2E;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #073B2E;
            margin-bottom: 3px;
            text-transform: uppercase;
        }
        .period-sub {
            text-align: center;
            font-size: 8.5pt;
            color: #718096;
            margin-bottom: 12px;
        }
        .section-title {
            font-size: 9.5pt;
            font-weight: bold;
            color: #073B2E;
            background-color: #F5F0E5;
            padding: 4px 8px;
            border-left: 3px solid #C9A45B;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #073B2E;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #073B2E;
            text-align: center;
        }
        .data-table td {
            padding: 4px;
            border: 1px solid #E2E8F0;
            vertical-align: middle;
        }
        .data-table tr:nth-child(even) td { background-color: #F8F9FA; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .total-row {
            background-color: #F5F0E5 !important;
            font-weight: bold;
        }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>
    @php
        $logoPath = public_path('assets/brand/logo.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }
        $transferPayments = $payments->where('payment_method', 'transfer');
        $cashPayments = $payments->where('payment_method', 'tunai');
        $transferTotal = $transferPayments->sum('amount');
        $cashTotal = $cashPayments->sum('amount');
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 25%; border: none;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @else
                    <strong style="color: #073B2E; font-size: 15px;">AL GHIFARI</strong>
                @endif
            </td>
            <td style="width: 50%; text-align: center; border: none;">
                <div class="company-title">{{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}</div>
                <div style="font-size: 8.5pt; color: #C9A45B; font-weight: bold;">{{ $setting->company_tagline ?? 'UMROH & HAJI SPECIALIST' }}</div>
                <div style="font-size: 8pt; color: #718096;">{{ $setting->company_address ?? 'Jakarta' }} | Telp: {{ $setting->company_phone ?? '-' }}</div>
            </td>
            <td style="width: 25%; text-align: right; font-size: 8pt; color: #718096; border: none;">
                Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB
            </td>
        </tr>
    </table>

    <div class="doc-title">LAPORAN PENERIMAAN KAS PEMBAYARAN JAMAAH</div>
    <div class="period-sub">Periode Transaksi: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>

    <!-- TABEL 1: RINGKASAN METODE BAYAR -->
    <div class="section-title">TABEL 1: REKAPITULASI METODE PEMBAYARAN</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%; background-color: #0B4939; text-align: left;">Metode Pembayaran</th>
                <th style="width: 20%; background-color: #0B4939;">Frekuensi Transaksi</th>
                <th style="width: 25%; background-color: #0B4939; text-align: right;">Total Kas Diterima (Rp)</th>
                <th style="width: 20%; background-color: #0B4939; text-align: right;">Kontribusi (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Transfer Bank (Rekening Resmi Travel)</strong></td>
                <td class="text-center">{{ $transferPayments->count() }} Transaksi</td>
                <td class="text-end fw-bold">Rp {{ number_format($transferTotal, 0, ',', '.') }}</td>
                <td class="text-end">{{ $totalAmount > 0 ? round(($transferTotal / $totalAmount) * 100, 1) : 0 }}%</td>
            </tr>
            <tr>
                <td><strong>Pembayaran Tunai (Cash di Kasir Kantor)</strong></td>
                <td class="text-center">{{ $cashPayments->count() }} Transaksi</td>
                <td class="text-end fw-bold">Rp {{ number_format($cashTotal, 0, ',', '.') }}</td>
                <td class="text-end">{{ $totalAmount > 0 ? round(($cashTotal / $totalAmount) * 100, 1) : 0 }}%</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL KAS MASUK TERVERIFIKASI</td>
                <td class="text-center">{{ $payments->count() }} Transaksi</td>
                <td class="text-end" style="color: #073B2E;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
                <td class="text-end">100%</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 2: RINCIAN LENGKAP -->
    <div class="section-title">TABEL 2: RINCIAN MUTASI PEMBAYARAN KAS MASUK</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 100px;">NO KWITANSI</th>
                <th style="width: 70px;">TANGGAL</th>
                <th>NAMA JAMAAH</th>
                <th>PAKET PERJALANAN</th>
                <th style="width: 65px;">TIPE</th>
                <th style="width: 65px;">METODE</th>
                <th style="width: 105px;" class="text-end">NOMINAL (RP)</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payments as $idx => $pay)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold" style="font-family: monospace;">{{ $pay->receipt_number }}</td>
                    <td class="text-center">{{ $pay->payment_date ? $pay->payment_date->format('d/m/Y') : '-' }}</td>
                    <td><strong>{{ $pay->invoice->registration->jamaah->name ?? '-' }}</strong></td>
                    <td>{{ $pay->invoice->registration->package->name ?? '-' }}</td>
                    <td class="text-center text-uppercase">{{ $pay->payment_type }}</td>
                    <td class="text-center text-uppercase">{{ $pay->payment_method }}</td>
                    <td class="text-end fw-bold">Rp {{ number_format($pay->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">Tidak ada data transaksi pembayaran pada periode ini.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="7" class="text-end">TOTAL PENERIMAAN KAS:</td>
                <td class="text-end" style="color: #073B2E;">Rp {{ number_format($totalAmount, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>