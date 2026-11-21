<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Laba Rugi per Kloter</title>
    <style>
        @page {
            margin: 20px 25px;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2D3748;
            font-size: 8pt;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #073B2E;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .logo-img { max-height: 40px; }
        .company-title {
            font-size: 14px;
            font-weight: bold;
            color: #073B2E;
            text-transform: uppercase;
        }
        .doc-title {
            text-align: center;
            font-size: 12px;
            font-weight: bold;
            color: #073B2E;
            margin-bottom: 2px;
            text-transform: uppercase;
        }
        .period-sub {
            text-align: center;
            font-size: 8pt;
            color: #718096;
            margin-bottom: 10px;
        }
        .summary-box {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }
        .summary-box td {
            padding: 6px 10px;
            border: 1px solid #CBD5E0;
            background-color: #F8F9FA;
            font-size: 8pt;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #073B2E;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #073B2E;
            text-align: left;
        }
        .data-table td {
            padding: 4px 4px;
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
        .badge-untung { color: #2E7D32; font-weight: bold; }
        .badge-rugi { color: #C62828; font-weight: bold; }
        .badge-impas { color: #555555; font-weight: bold; }
        thead { display: table-header-group; }
        tr { page-break-inside: avoid; }
        .signature-table {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .signature-table td {
            border: none;
            text-align: center;
            font-size: 8pt;
            vertical-align: top;
        }
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
                    <strong style="color: #073B2E; font-size: 14px;">AL GHIFARI</strong>
                @endif
            </td>
            <td style="width: 50%; text-align: center; border: none;">
                <div class="company-title">{{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}</div>
                <div style="font-size: 8pt; color: #C9A45B; font-weight: bold;">{{ $setting->company_tagline ?? 'UMROH & HAJI SPECIALIST' }}</div>
                <div style="font-size: 7.5pt; color: #718096;">{{ $setting->company_address ?? 'Jakarta' }} | Telp: {{ $setting->company_phone ?? '-' }}</div>
            </td>
            <td style="width: 25%; text-align: right; font-size: 7.5pt; color: #718096; border: none;">
                Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB
            </td>
        </tr>
    </table>

    <div class="doc-title">LAPORAN LABA RUGI PER KLOTER KEBERANGKATAN</div>
    <div class="period-sub">
        Periode: {{ $report['start_date'] ? \Carbon\Carbon::parse($report['start_date'])->format('d/m/Y') : 'Awal' }} s/d {{ $report['end_date'] ? \Carbon\Carbon::parse($report['end_date'])->format('d/m/Y') : 'Akhir' }}
        &bull; Metode Distribusi: Proporsional Jamaah Aktif
    </div>

    <!-- Ringkasan Eksekutif -->
    <table class="summary-box">
        <tr>
            <td style="width: 16%;"><strong>Total Pendapatan:</strong><br><span style="color: #2E7D32; font-weight: bold;">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</span></td>
            <td style="width: 16%;"><strong>Biaya Langsung:</strong><br><span style="color: #C62828; font-weight: bold;">Rp {{ number_format($report['total_direct_expense'], 0, ',', '.') }}</span></td>
            <td style="width: 17%;"><strong>Laba Sblm Alokasi:</strong><br><span style="font-weight: bold; color: #1565C0;">Rp {{ number_format($report['total_profit_before'], 0, ',', '.') }}</span></td>
            <td style="width: 17%;"><strong>Biaya Umum (Overhead):</strong><br><span style="font-weight: bold;">Rp {{ number_format($report['total_general_expense'], 0, ',', '.') }}</span></td>
            <td style="width: 17%;"><strong>Laba Stlh Alokasi:</strong><br><span style="font-weight: bold; color: {{ $report['total_profit_after'] >= 0 ? '#2E7D32' : '#C62828' }};">Rp {{ number_format($report['total_profit_after'], 0, ',', '.') }}</span></td>
            <td style="width: 17%;"><strong>Total Jamaah / Kloter:</strong><br><span>{{ $report['total_active_jamaah'] }} Pax / {{ $report['total_kloter_count'] }} Kloter</span></td>
        </tr>
    </table>

    <!-- Tabel Data Kloter -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px; text-align: center;">NO</th>
                <th style="width: 80px;">KODE KLOTER</th>
                <th style="width: 110px;">NAMA KLOTER</th>
                <th style="width: 90px;">PAKET</th>
                <th style="width: 65px; text-align: center;">TGL BERANGKAT</th>
                <th style="width: 45px; text-align: center;">JAMAAH</th>
                <th style="width: 85px; text-align: right;">PENDAPATAN (RP)</th>
                <th style="width: 80px; text-align: right;">BIAYA LANGSUNG</th>
                <th style="width: 80px; text-align: right;">LABA SBLM ALOKASI</th>
                <th style="width: 75px; text-align: right;">ALOKASI BIAYA UMUM</th>
                <th style="width: 80px; text-align: right;">LABA STLH ALOKASI</th>
                <th style="width: 45px; text-align: right;">MARGIN</th>
                <th style="width: 50px; text-align: center;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @php $no = 1; @endphp
            @forelse($report['kloters'] as $item)
                @php $d = $item['departure']; @endphp
                <tr>
                    <td class="text-center">{{ $no++ }}</td>
                    <td class="fw-bold">{{ $d->code }}</td>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->package->name ?? '-' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($d->departure_date)->format('d/m/Y') }}</td>
                    <td class="text-center fw-bold">{{ $item['active_jamaah_count'] }}</td>
                    <td class="text-end">Rp {{ number_format($item['revenue'], 0, ',', '.') }}</td>
                    <td class="text-end" style="color: #C62828;">Rp {{ number_format($item['direct_expense'], 0, ',', '.') }}</td>
                    <td class="text-end fw-bold" style="color: {{ $item['profit_before_allocation'] >= 0 ? '#1565C0' : '#C62828' }};">
                        Rp {{ number_format($item['profit_before_allocation'], 0, ',', '.') }}
                    </td>
                    <td class="text-end">Rp {{ number_format($item['allocated_general_expense'], 0, ',', '.') }}</td>
                    <td class="text-end fw-bold" style="color: {{ $item['profit_after_allocation'] >= 0 ? '#2E7D32' : '#C62828' }};">
                        Rp {{ number_format($item['profit_after_allocation'], 0, ',', '.') }}
                    </td>
                    <td class="text-end">{{ $item['margin_after_allocation'] }}%</td>
                    <td class="text-center">
                        @if($item['status_profit'] === 'untung')
                            <span class="badge-untung">Untung</span>
                        @elseif($item['status_profit'] === 'rugi')
                            <span class="badge-rugi">Rugi</span>
                        @else
                            <span class="badge-impas">Impas</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class="text-center" style="padding: 20px;">Tidak ada data kloter keberangkatan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
        @if($report['kloters']->isNotEmpty())
            <tfoot>
                <tr class="total-row">
                    <td colspan="5" class="text-center">TOTAL REKAPITULASI</td>
                    <td class="text-center">{{ $report['total_active_jamaah'] }}</td>
                    <td class="text-end" style="color: #2E7D32;">Rp {{ number_format($report['total_revenue'], 0, ',', '.') }}</td>
                    <td class="text-end" style="color: #C62828;">Rp {{ number_format($report['total_direct_expense'], 0, ',', '.') }}</td>
                    <td class="text-end" style="color: #1565C0;">Rp {{ number_format($report['total_profit_before'], 0, ',', '.') }}</td>
                    <td class="text-end">Rp {{ number_format($report['allocated_general_expense'], 0, ',', '.') }}</td>
                    <td class="text-end" style="color: {{ $report['total_profit_after'] >= 0 ? '#2E7D32' : '#C62828' }};">
                        Rp {{ number_format($report['total_profit_after'], 0, ',', '.') }}
                    </td>
                    <td class="text-end">
                        {{ $report['total_revenue'] > 0 ? round(($report['total_profit_after'] / $report['total_revenue']) * 100, 2) : 0 }}%
                    </td>
                    <td class="text-center">
                        <span class="{{ $report['total_profit_after'] >= 0 ? 'badge-untung' : 'badge-rugi' }}">
                            {{ $report['total_profit_after'] >= 0 ? 'Untung' : 'Rugi' }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        @endif
    </table>

    <!-- Tanda Tangan -->
    <table class="signature-table">
        <tr>
            <td style="width: 33%;">
                Dibuat Oleh,<br><br><br><br>
                <strong>( Staff Keuangan )</strong>
            </td>
            <td style="width: 33%;">
                Diperiksa Oleh,<br><br><br><br>
                <strong>( Manajer Operasional )</strong>
            </td>
            <td style="width: 34%;">
                Disetujui Oleh,<br><br><br><br>
                <strong>( Direktur Utama )</strong>
            </td>
        </tr>
    </table>
</body>
</html>
