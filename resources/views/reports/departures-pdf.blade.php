<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Keberangkatan Rombongan {{ $year }}</title>
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
        $totalQuota = $departures->sum('quota');
        $totalRegistered = $departures->sum('registrations_count');
        $totalRemaining = max(0, $totalQuota - $totalRegistered);
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

    <div class="doc-title">LAPORAN REKAPITULASI JADWAL KEBERANGKATAN TAHUN {{ $year }}</div>
    <div class="period-sub">Monitoring jadwal kloter, manifest penerbangan, dan okupansi kursi rombongan</div>

    <!-- TABEL 1: RINGKASAN OKUPANSI -->
    <div class="section-title">TABEL 1: REKAPITULASI KUOTA &amp; OKUPANSI SEAT</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 35%; background-color: #0B4939; text-align: left;">Indikator Keberangkatan</th>
                <th style="width: 20%; background-color: #0B4939; text-align: right;">Total / Realisasi</th>
                <th style="width: 15%; background-color: #0B4939; text-align: right;">Rasio (%)</th>
                <th style="width: 30%; background-color: #0B4939; text-align: left;">Keterangan Operasional</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Kloter Terjadwal</strong></td>
                <td class="text-end fw-bold">{{ $departures->count() }} Jadwal Kloter</td>
                <td class="text-end">-</td>
                <td>Jadwal penerbangan tahun {{ $year }}</td>
            </tr>
            <tr>
                <td>Total Kuota Kursi Dialokasikan</td>
                <td class="text-end fw-bold">{{ $totalQuota }} Pax</td>
                <td class="text-end">100%</td>
                <td>Kapasitas kursi seluruh kloter</td>
            </tr>
            <tr>
                <td>Total Jamaah Berangkat / Terdaftar</td>
                <td class="text-end fw-bold" style="color: #2E7D32;">{{ $totalRegistered }} Pax</td>
                <td class="text-end">{{ $totalQuota > 0 ? round(($totalRegistered / $totalQuota) * 100, 1) : 0 }}%</td>
                <td>Realisasi pendaftar yang siap berangkat</td>
            </tr>
            <tr class="total-row">
                <td>SISA KUOTA SEAT TERSEDIA</td>
                <td class="text-end" style="color: #073B2E;">{{ $totalRemaining }} Pax</td>
                <td class="text-end">{{ $totalQuota > 0 ? round(($totalRemaining / $totalQuota) * 100, 1) : 0 }}%</td>
                <td>Alokasi kursi yang masih dapat dipasarkan</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 2: RINCIAN KLOTER -->
    <div class="section-title">TABEL 2: RINCIAN JADWAL KLOTER &amp; REALISASI KUOTA</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 90px;">KODE KLOTER</th>
                <th>PAKET PERJALANAN</th>
                <th style="width: 85px;">MASKAPAI</th>
                <th style="width: 70px;">BERANGKAT</th>
                <th style="width: 70px;">PULANG</th>
                <th style="width: 50px;" class="text-end">KUOTA</th>
                <th style="width: 50px;" class="text-end">TERISI</th>
                <th style="width: 50px;" class="text-end">SISA</th>
                <th style="width: 75px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departures as $idx => $dep)
                @php $rem = max(0, $dep->quota - $dep->registrations_count); @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold" style="font-family: monospace;">{{ $dep->code }}</td>
                    <td><strong>{{ $dep->package->name ?? '-' }}</strong></td>
                    <td>{{ $dep->airline ?? '-' }}</td>
                    <td class="text-center">{{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $dep->return_date ? $dep->return_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-end">{{ $dep->quota }}</td>
                    <td class="text-end fw-bold" style="color: #2E7D32;">{{ $dep->registrations_count }}</td>
                    <td class="text-end">{{ $rem }}</td>
                    <td class="text-center">{{ $dep->status_label }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px;">Belum ada jadwal keberangkatan pada tahun {{ $year }}.</td>
                </tr>
            @endforelse
            <tr class="total-row">
                <td colspan="6" class="text-end">TOTAL REKAPITULASI:</td>
                <td class="text-end">{{ $totalQuota }}</td>
                <td class="text-end" style="color: #2E7D32;">{{ $totalRegistered }}</td>
                <td class="text-end">{{ $totalRemaining }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>