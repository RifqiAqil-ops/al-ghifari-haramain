<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laba Rugi Kloter {{ $departure->code }}</title>
    <style>
        @page {
            margin: 25px 30px;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2D3748;
            font-size: 8.5pt;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #073B2E;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }
        .logo-img { max-height: 42px; }
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
            margin-bottom: 12px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            background-color: #F8F9FA;
            border: 1px solid #E2E8F0;
        }
        .info-table td {
            padding: 5px 8px;
            font-size: 8pt;
            border: 1px solid #E2E8F0;
        }
        .section-title {
            font-size: 9pt;
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
            margin-bottom: 10px;
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
            padding: 4px 6px;
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
        .summary-card {
            border: 1px solid #CBD5E0;
            padding: 8px 10px;
            background-color: #F8F9FA;
            margin-bottom: 12px;
            border-radius: 4px;
        }
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

    <div class="doc-title">LAPORAN ANALISIS LABA RUGI KLOTER</div>
    <div class="period-sub">Kloter: {{ $departure->code }} - {{ $departure->name }} &bull; Paket: {{ $departure->package->name ?? '-' }}</div>

    <!-- Info Detail Kloter -->
    <table class="info-table">
        <tr>
            <td style="width: 20%;"><strong>Kode Kloter:</strong></td>
            <td style="width: 30%;">{{ $departure->code }}</td>
            <td style="width: 20%;"><strong>Tanggal Berangkat:</strong></td>
            <td style="width: 30%;">{{ \Carbon\Carbon::parse($departure->departure_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Nama Rombongan:</strong></td>
            <td>{{ $departure->name }}</td>
            <td><strong>Tanggal Kepulangan:</strong></td>
            <td>{{ \Carbon\Carbon::parse($departure->return_date)->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Paket &amp; Maskapai:</strong></td>
            <td>{{ $departure->package->name ?? '-' }} &bull; {{ $departure->airline ?: '-' }}</td>
            <td><strong>Okupansi Jamaah:</strong></td>
            <td><strong>{{ $detail['item']['active_jamaah_count'] }}</strong> Jamaah Aktif (Kuota: {{ $departure->quota }})</td>
        </tr>
    </table>

    <!-- Ringkasan Komparasi Laba Rugi -->
    <div class="summary-card">
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
                <td style="width: 33%; border: none;">
                    <div style="font-size: 7.5pt; color: #718096;">TOTAL PENDAPATAN KONTRAK:</div>
                    <div style="font-size: 11pt; font-weight: bold; color: #2E7D32;">Rp {{ number_format($detail['item']['revenue'], 0, ',', '.') }}</div>
                </td>
                <td style="width: 33%; border: none;">
                    <div style="font-size: 7.5pt; color: #718096;">LABA SBLM ALOKASI UMUM:</div>
                    <div style="font-size: 11pt; font-weight: bold; color: #1565C0;">
                        Rp {{ number_format($detail['item']['profit_before_allocation'], 0, ',', '.') }} ({{ $detail['item']['margin_before_allocation'] }}%)
                    </div>
                </td>
                <td style="width: 34%; border: none;">
                    <div style="font-size: 7.5pt; color: #718096;">LABA BERSIH STLH ALOKASI:</div>
                    <div style="font-size: 11pt; font-weight: bold; color: {{ $detail['item']['profit_after_allocation'] >= 0 ? '#2E7D32' : '#C62828' }};">
                        Rp {{ number_format($detail['item']['profit_after_allocation'], 0, ',', '.') }} ({{ $detail['item']['margin_after_allocation'] }}%)
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <!-- BAGIAN A: SEBELUM ALOKASI BIAYA OPERASIONAL UMUM -->
    <div class="section-title">BAGIAN A: LABA RUGI SEBELUM ALOKASI BIAYA UMUM (DIRECT MARGIN)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%; text-align: center;">NO</th>
                <th style="width: 15%;">TANGGAL</th>
                <th style="width: 18%;">NO. REF</th>
                <th style="width: 22%;">KATEGORI BEBAN</th>
                <th style="width: 25%;">KETERANGAN</th>
                <th style="width: 15%; text-align: right;">JUMLAH (RP)</th>
            </tr>
        </thead>
        <tbody>
            @php $eNo = 1; @endphp
            @forelse($detail['item']['direct_expenses_list'] as $exp)
                <tr>
                    <td class="text-center">{{ $eNo++ }}</td>
                    <td>{{ \Carbon\Carbon::parse($exp->expense_date)->format('d/m/Y') }}</td>
                    <td>{{ $exp->reference_no ?: '-' }}</td>
                    <td>{{ $exp->category->name ?? 'Operasional' }}</td>
                    <td>{{ $exp->description ?: '-' }}</td>
                    <td class="text-end" style="color: #C62828;">Rp {{ number_format($exp->amount, 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Tidak ada transaksi pengeluaran langsung untuk kloter ini.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="5" class="text-end">TOTAL BIAYA LANGSUNG KLOTER:</td>
                <td class="text-end" style="color: #C62828;">Rp {{ number_format($detail['item']['direct_expense'], 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #E8F5E9; font-weight: bold;">
                <td colspan="5" class="text-end">LABA OPERASIONAL SEBELUM ALOKASI UMUM:</td>
                <td class="text-end" style="color: #1565C0;">Rp {{ number_format($detail['item']['profit_before_allocation'], 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- BAGIAN B: SETELAH ALOKASI BIAYA OPERASIONAL UMUM -->
    <div class="section-title">BAGIAN B: ALOKASI BIAYA OPERASIONAL UMUM &amp; LABA BERSIH AKHIR</div>
    <table class="data-table">
        <tbody>
            <tr>
                <td style="width: 60%;">Total Biaya Operasional Umum / Overhead Kantor (Periode)</td>
                <td style="width: 40%;" class="text-end fw-bold">Rp {{ number_format($detail['total_report']['total_general_expense'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Rasio Alokasi Kloter ({{ $detail['item']['active_jamaah_count'] }} pax / {{ $detail['total_report']['total_active_jamaah'] }} pax)</td>
                <td class="text-end fw-bold">{{ $detail['item']['allocation_ratio'] }}%</td>
            </tr>
            <tr>
                <td>Beban Umum Kantor yang Dialokasikan ke Kloter Ini</td>
                <td class="text-end fw-bold" style="color: #E65100;">Rp {{ number_format($detail['item']['allocated_general_expense'], 0, ',', '.') }}</td>
            </tr>
            <tr class="total-row">
                <td>TOTAL SELURUH BEBAN KLOTER (Langsung + Alokasi Umum)</td>
                <td class="text-end" style="color: #C62828;">Rp {{ number_format($detail['item']['total_expense_after'], 0, ',', '.') }}</td>
            </tr>
            <tr style="background-color: #E8F5E9; font-weight: bold;">
                <td style="font-size: 9pt;">LABA BERSIH AKHIR KLOTER (SETELAH ALOKASI UMUM)</td>
                <td class="text-end" style="font-size: 9pt; color: {{ $detail['item']['profit_after_allocation'] >= 0 ? '#2E7D32' : '#C62828' }};">
                    Rp {{ number_format($detail['item']['profit_after_allocation'], 0, ',', '.') }} ({{ $detail['item']['margin_after_allocation'] }}%)
                </td>
            </tr>
            <tr>
                <td>Biaya per Jamaah &bull; Laba per Jamaah</td>
                <td class="text-end">
                    Biaya: Rp {{ number_format($detail['item']['cost_per_jamaah'], 0, ',', '.') }}/pax &bull;
                    Laba: <strong style="color: {{ $detail['item']['profit_per_jamaah'] >= 0 ? '#2E7D32' : '#C62828' }};">Rp {{ number_format($detail['item']['profit_per_jamaah'], 0, ',', '.') }}/pax</strong>
                </td>
            </tr>
        </tbody>
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
