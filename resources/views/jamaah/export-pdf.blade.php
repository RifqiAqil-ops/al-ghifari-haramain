<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Data Jamaah - {{ $setting->company_name ?? 'Al Ghifari Haramain' }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 20px 25px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #2D3748;
            line-height: 1.3;
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
        .company-tagline {
            font-size: 8.5pt;
            color: #C9A45B;
            font-weight: bold;
        }
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #073B2E;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .meta-sub {
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
            padding: 5px 8px;
            border-left: 3px solid #C9A45B;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 14px;
        }
        th {
            background-color: #073B2E;
            color: #FFFFFF;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #073B2E;
            text-align: left;
        }
        td {
            border: 1px solid #E2E8F0;
            padding: 4px;
            vertical-align: middle;
        }
        tr:nth-child(even) td { background-color: #F8F9FA; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-success { background-color: #E8F5E9; color: #2E7D32; }
        .badge-warning { background-color: #FFF8E1; color: #B78103; }
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
                <div class="company-tagline">{{ $setting->company_tagline ?? 'UMROH & HAJI SPECIALIST' }}</div>
                <div style="font-size: 8pt; color: #718096; margin-top: 2px;">
                    {{ $setting->company_address ?? 'Jakarta' }} | Telp: {{ $setting->company_phone ?? '-' }}
                </div>
            </td>
            <td style="width: 25%; text-align: right; font-size: 8pt; color: #718096; border: none;">
                Tanggal Cetak:<br>
                <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</strong>
            </td>
        </tr>
    </table>

    <div class="doc-title">DIREKTORI MASTER DATA JAMAAH</div>
    <div class="meta-sub">Rekapitulasi data jamaah operasional perjalanan haji &amp; umrah</div>

    <!-- TABEL 1: RINGKASAN DATA -->
    <div class="section-title">TABEL 1: RINGKASAN DATA &amp; DEMOGRAFI JAMAAH</div>
    @php
        $totalPax = $jamaahs->count();
        $maleCount = $jamaahs->where('gender', 'L')->count();
        $femaleCount = $jamaahs->where('gender', 'P')->count();
        $activeCount = $jamaahs->where('status', 'aktif')->count();
        $passportCount = $jamaahs->whereNotNull('passport_number')->count();
    @endphp
    <table>
        <thead>
            <tr>
                <th style="width: 25%; background-color: #0B4939;">Kategori Statistik</th>
                <th style="width: 15%; background-color: #0B4939; text-align: right;">Jumlah Pax</th>
                <th style="width: 15%; background-color: #0B4939; text-align: right;">Persentase</th>
                <th style="width: 45%; background-color: #0B4939;">Keterangan Operasional</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Jamaah Terdaftar</strong></td>
                <td class="text-end fw-bold">{{ $totalPax }} Pax</td>
                <td class="text-end">100%</td>
                <td>Total seluruh jamaah yang tersimpan dalam sistem</td>
            </tr>
            <tr>
                <td>Jamaah Laki-laki (Ikhwan)</td>
                <td class="text-end">{{ $maleCount }} Pax</td>
                <td class="text-end">{{ $totalPax > 0 ? round(($maleCount / $totalPax) * 100, 1) : 0 }}%</td>
                <td>Memerlukan pembagian mahram / kamar rombongan</td>
            </tr>
            <tr>
                <td>Jamaah Perempuan (Akhwat)</td>
                <td class="text-end">{{ $femaleCount }} Pax</td>
                <td class="text-end">{{ $totalPax > 0 ? round(($femaleCount / $totalPax) * 100, 1) : 0 }}%</td>
                <td>Kesesuaian mahram &amp; akomodasi hotel</td>
            </tr>
            <tr>
                <td>Status Paspor Valid</td>
                <td class="text-end">{{ $passportCount }} Pax</td>
                <td class="text-end">{{ $totalPax > 0 ? round(($passportCount / $totalPax) * 100, 1) : 0 }}%</td>
                <td>Jamaah dengan nomor paspor terdaftar</td>
            </tr>
            <tr>
                <td>Status Jamaah Aktif</td>
                <td class="text-end fw-bold" style="color: #073B2E;">{{ $activeCount }} Pax</td>
                <td class="text-end">{{ $totalPax > 0 ? round(($activeCount / $totalPax) * 100, 1) : 0 }}%</td>
                <td>Jamaah dalam proses aktif / siap berangkat</td>
            </tr>
        </tbody>
    </table>

    <!-- TABEL 2: RINCIAN LENGKAP MASTER DATA -->
    <div class="section-title">TABEL 2: RINCIAN LENGKAP MASTER DATA JAMAAH</div>
    <table>
        <thead>
            <tr>
                <th style="width: 3%; text-align: center;">NO</th>
                <th style="width: 13%;">NO. REGISTRASI</th>
                <th style="width: 18%;">NAMA LENGKAP</th>
                <th style="width: 13%;">NIK</th>
                <th style="width: 10%;">NO. PASPOR</th>
                <th style="width: 5%; text-align: center;">L/P</th>
                <th style="width: 11%;">WHATSAPP</th>
                <th style="width: 12%;">KOTA</th>
                <th style="width: 8%; text-align: center;">STATUS</th>
                <th style="width: 7%;">CATATAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($jamaahs as $index => $j)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="fw-bold" style="font-family: monospace;">{{ $j->registration_number }}</td>
                    <td><strong>{{ $j->name }}</strong></td>
                    <td>{{ $j->nik }}</td>
                    <td>{{ $j->passport_number ?? '-' }}</td>
                    <td class="text-center">{{ $j->gender }}</td>
                    <td>{{ $j->phone }}</td>
                    <td>{{ $j->city ?? '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $j->status === 'aktif' ? 'badge-success' : 'badge-warning' }}">
                            {{ ucfirst($j->status) }}
                        </span>
                    </td>
                    <td>{{ $j->special_needs ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px;">Belum ada data jamaah.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>