<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi Operasional</title>
    <style>
        @page {
            margin: 25px 30px;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2D3748;
            font-size: 9pt;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #073B2E;
            padding-bottom: 8px;
            margin-bottom: 12px;
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
            margin-bottom: 14px;
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
            font-size: 8.5pt;
            margin-bottom: 14px;
        }
        .data-table th {
            background-color: #073B2E;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 6px;
            border: 1px solid #073B2E;
            text-align: left;
        }
        .data-table td {
            padding: 5px 6px;
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
        .net-profit-box {
            background-color: #073B2E;
            color: #ffffff;
            padding: 8px 10px;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 10px;
            margin-bottom: 20px;
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

    <div class="doc-title">LAPORAN LABA RUGI OPERASIONAL PERJALANAN</div>
    <div class="period-sub">Periode Pembukuan: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>

    <!-- TABEL 1: PENDAPATAN OPERASIONAL -->
    <div class="section-title">TABEL 1: PENDAPATAN OPERASIONAL (KAS MASUK)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">NO</th>
                <th style="width: 55%;">KOMPONEN PENDAPATAN</th>
                <th style="width: 25%; text-align: right;">NOMINAL (RP)</th>
                <th style="width: 15%; text-align: right;">PORSI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="text-center">1</td>
                <td>Penerimaan Kas Pembayaran Paket Jamaah (Valid)</td>
                <td class="text-end fw-bold" style="color: #2E7D32;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-end">100%</td>
            </tr>
            <tr class="total-row">
                <td colspan="2" class="text-end">TOTAL PENDAPATAN OPERASIONAL:</td>
                <td class="text-end" style="color: #2E7D32;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
                <td class="text-end">100%</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 2: BEBAN PENGELUARAN OPERASIONAL -->
    <div class="section-title">TABEL 2: BEBAN PENGELUARAN OPERASIONAL</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">NO</th>
                <th style="width: 55%;">KATEGORI BEBAN OPERASIONAL</th>
                <th style="width: 25%; text-align: right;">NOMINAL (RP)</th>
                <th style="width: 15%; text-align: right;">PORSI BEBAN</th>
            </tr>
        </thead>
        <tbody>
            @php $eNo = 1; @endphp
            @forelse($expensesByCategory as $catName => $items)
                @php $catTotal = $items->sum('amount'); @endphp
                <tr>
                    <td class="text-center">{{ $eNo++ }}</td>
                    <td>{{ $catName ?? 'Operasional Umum' }}</td>
                    <td class="text-end" style="color: #C62828;">Rp {{ number_format($catTotal, 0, ',', '.') }}</td>
                    <td class="text-end">{{ $totalExpense > 0 ? round(($catTotal / $totalExpense) * 100, 1) : 0 }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center" style="padding: 10px;">Belum ada catatan pengeluaran.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="2" class="text-end">TOTAL BEBAN OPERASIONAL:</td>
                <td class="text-end" style="color: #C62828;">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
                <td class="text-end">100%</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 3: REKAPITULASI HASIL USAHA -->
    <div class="section-title">TABEL 3: REKAPITULASI LABA / RUGI BERSIH OPERASIONAL</div>
    <table class="data-table">
        <tbody>
            <tr>
                <td style="width: 60%;"><strong>Total Pendapatan Operasional</strong></td>
                <td style="width: 40%; text-align: right; color: #2E7D32; font-weight: bold;">
                    Rp {{ number_format($totalIncome, 0, ',', '.') }}
                </td>
            </tr>
            <tr>
                <td><strong>Total Beban Operasional</strong></td>
                <td style="text-align: right; color: #C62828; font-weight: bold;">
                    (Rp {{ number_format($totalExpense, 0, ',', '.') }})
                </td>
            </tr>
            <tr style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 10pt;">
                <td style="color: #FFFFFF;">LABA / (RUGI) BERSIH OPERASIONAL:</td>
                <td style="text-align: right; color: #E5CF96;">
                    Rp {{ number_format($netProfit, 0, ',', '.') }}
                </td>
            </tr>
        </tbody>
    </table>
</body>
</html>