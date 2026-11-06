<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Tunggakan & Sisa Piutang Jamaah</title>
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
        $totalInvAmount = $invoices->sum('total_amount');
        $totalPaid = $invoices->sum('paid_amount');
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

    <div class="doc-title">LAPORAN MONITORING SISA PIUTANG &amp; TUNGGAKAN JAMAAH</div>
    <div class="period-sub">Daftar invoice aktif dengan kewajiban pelunasan paket</div>

    <!-- TABEL 1: RINGKASAN REKAPITULASI -->
    <div class="section-title">TABEL 1: REKAPITULASI TOTAL PIUTANG OPERASIONAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%; background-color: #0B4939; text-align: left;">Parameter Piutang</th>
                <th style="width: 20%; background-color: #0B4939; text-align: right;">Jumlah Berkas / Nilai</th>
                <th style="width: 15%; background-color: #0B4939; text-align: right;">Rasio</th>
                <th style="width: 30%; background-color: #0B4939; text-align: left;">Catatan Manajemen</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Invoice Belum Lunas</strong></td>
                <td class="text-end fw-bold">{{ $invoices->count() }} Berkas</td>
                <td class="text-end">100%</td>
                <td>Jamaah dalam proses cicilan / menunggu jatuh tempo</td>
            </tr>
            <tr>
                <td>Total Nilai Tagihan Terbit</td>
                <td class="text-end">Rp {{ number_format($totalInvAmount, 0, ',', '.') }}</td>
                <td class="text-end">100%</td>
                <td>Nilai total paket jamaah bersangkutan</td>
            </tr>
            <tr>
                <td>Total Uang Muka / Cicilan Diterima</td>
                <td class="text-end fw-bold" style="color: #2E7D32;">Rp {{ number_format($totalPaid, 0, ',', '.') }}</td>
                <td class="text-end">{{ $totalInvAmount > 0 ? round(($totalPaid / $totalInvAmount) * 100, 1) : 0 }}%</td>
                <td>Dana pembayaran yang telah masuk rekening</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL SISA PIUTANG JATUH TEMPO</td>
                <td class="text-end" style="color: #C62828;">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</td>
                <td class="text-end">{{ $totalInvAmount > 0 ? round(($totalReceivable / $totalInvAmount) * 100, 1) : 0 }}%</td>
                <td>Wajib ditagihkan sebelum tanggal pelunasan akhir</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 2: RINCIAN LENGKAP -->
    <div class="section-title">TABEL 2: RINCIAN TAGIHAN BELUM LUNAS PER JAMAAH</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 95px;">NO INVOICE</th>
                <th>NAMA JAMAAH</th>
                <th style="width: 85px;">WHATSAPP</th>
                <th>PAKET PERJALANAN</th>
                <th style="width: 90px;" class="text-end">TOTAL (RP)</th>
                <th style="width: 85px;" class="text-end">TERBAYAR (RP)</th>
                <th style="width: 95px;" class="text-end">SISA PIUTANG</th>
                <th style="width: 70px;">JATUH TEMPO</th>
            </tr>
        </thead>
        <tbody>
            @forelse($invoices as $idx => $inv)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold" style="font-family: monospace;">{{ $inv->invoice_number }}</td>
                    <td><strong>{{ $inv->registration->jamaah->name ?? '-' }}</strong></td>
                    <td class="text-center">{{ $inv->registration->jamaah->phone ?? '-' }}</td>
                    <td>{{ $inv->registration->package->name ?? '-' }}</td>
                    <td class="text-end">Rp {{ number_format($inv->total_amount, 0, ',', '.') }}</td>
                    <td class="text-end" style="color: #2E7D32;">Rp {{ number_format($inv->paid_amount, 0, ',', '.') }}</td>
                    <td class="text-end fw-bold" style="color: #C62828;">Rp {{ number_format($inv->remaining_amount, 0, ',', '.') }}</td>
                    <td class="text-center">{{ $inv->due_date ? $inv->due_date->format('d/m/Y') : '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px;">Alhamdulillah, seluruh jamaah telah melunasi biaya perjalanan.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="7" class="text-end">TOTAL SISA PIUTANG:</td>
                <td class="text-end" style="color: #C62828;">Rp {{ number_format($totalReceivable, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>