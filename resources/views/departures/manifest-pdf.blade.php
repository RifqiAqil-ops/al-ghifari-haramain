<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>MANIFEST PENUMPANG - {{ $departure->code }}</title>
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
        .company-sub {
            font-size: 8pt;
            color: #718096;
        }
        .doc-title {
            text-align: center;
            font-size: 13px;
            font-weight: bold;
            color: #073B2E;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section-title {
            font-size: 9pt;
            font-weight: bold;
            color: #073B2E;
            background-color: #F5F0E5;
            padding: 4px 8px;
            border-left: 3px solid #C9A45B;
            margin-top: 8px;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
            font-size: 8.5pt;
        }
        .info-table td {
            padding: 4px 6px;
            vertical-align: top;
            border: 1px solid #E2E8F0;
        }
        .manifest-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }
        .manifest-table th {
            background-color: #073B2E;
            color: #ffffff;
            font-weight: bold;
            padding: 5px 4px;
            border: 1px solid #073B2E;
            text-align: center;
        }
        .manifest-table td {
            padding: 4px;
            border: 1px solid #E2E8F0;
            vertical-align: middle;
        }
        .manifest-table tr:nth-child(even) td { background-color: #F8F9FA; }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .signatures {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 33%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
            border: none;
        }
        .sign-box {
            height: 45px;
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
        $paxCount = $departure->registrations->count();
        $malePax = $departure->registrations->filter(fn($r) => optional($r->jamaah)->gender === 'L')->count();
        $femalePax = $departure->registrations->filter(fn($r) => optional($r->jamaah)->gender === 'P')->count();
        $quadPax = $departure->registrations->where('room_type', 'quad')->count();
        $triplePax = $departure->registrations->where('room_type', 'triple')->count();
        $doublePax = $departure->registrations->where('room_type', 'double')->count();
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 25%; border: none;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @else
                    <strong style="color: #073B2E; font-size: 16px;">AL GHIFARI</strong>
                @endif
            </td>
            <td style="width: 50%; text-align: center; border: none;">
                <div class="company-title">{{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}</div>
                <div style="font-size: 8.5pt; color: #C9A45B; font-weight: bold;">{{ $setting->company_tagline ?? 'UMROH & HAJI SPECIALIST' }}</div>
                <div class="company-sub">{{ $setting->company_address ?? 'Jakarta' }} | Telp: {{ $setting->company_phone ?? '-' }}</div>
            </td>
            <td style="width: 25%; text-align: right; font-size: 8pt; color: #718096; border: none;">
                Tanggal Cetak:<br>
                <strong>{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</strong><br>
                Status: <strong>{{ strtoupper(str_replace('_', ' ', $departure->status)) }}</strong>
            </td>
        </tr>
    </table>

    <div class="doc-title">OFFICIAL PASSENGER MANIFEST &amp; GROUP FLIGHT LIST</div>

    <!-- TABEL 1: INFORMASI KLOTER & PENERBANGAN -->
    <div class="section-title">TABEL 1: INFORMASI PENERBANGAN &amp; AKOMODASI ROMBONGAN</div>
    <table class="info-table" style="background-color: #FBF9F4;">
        <tr>
            <td style="width: 15%;" class="fw-bold">Kode Rombongan</td>
            <td style="width: 35%;">: <strong>{{ $departure->code }}</strong></td>
            <td style="width: 18%;" class="fw-bold">Maskapai / No Penerbangan</td>
            <td style="width: 32%;">: {{ $departure->airline ?? '-' }} ({{ $departure->flight_number ?? '-' }})</td>
        </tr>
        <tr>
            <td class="fw-bold">Paket Perjalanan</td>
            <td>: {{ $departure->package->name ?? '-' }} ({{ strtoupper($departure->package->type ?? 'UMRAH') }})</td>
            <td class="fw-bold">Rute Penerbangan</td>
            <td>: {{ $departure->origin_airport ?? 'CGK' }} &rarr; {{ $departure->destination_airport ?? 'JED' }}</td>
        </tr>
        <tr>
            <td class="fw-bold">Tgl Berangkat &amp; Pulang</td>
            <td>: {{ $departure->departure_date ? $departure->departure_date->format('d/m/Y') : '-' }} s/d {{ $departure->return_date ? $departure->return_date->format('d/m/Y') : '-' }}</td>
            <td class="fw-bold">Tour Leader / Muthawif</td>
            <td>: <strong>{{ $departure->leader_name ?? 'Belum Ditugaskan' }}</strong> ({{ $departure->leader_phone ?? '-' }})</td>
        </tr>
        <tr>
            <td class="fw-bold">Hotel Makkah / Madinah</td>
            <td>: {{ $departure->package->hotel_makkah ?? '-' }} / {{ $departure->package->hotel_madinah ?? '-' }}</td>
            <td class="fw-bold">Realisasi Kuota Seat</td>
            <td>: <strong>{{ $paxCount }} Pax</strong> dari Kapasitas {{ $departure->quota }} Seat</td>
        </tr>
    </table>

    <!-- TABEL 2: REKAPITULASI KAMAR & DEMOGRAFI -->
    <div class="section-title">TABEL 2: REKAPITULASI PENUMPANG &amp; DISTRIBUSI KAMAR</div>
    <table class="info-table">
        <tr style="background-color: #F8F9FA;">
            <td class="text-center" style="width: 16%;"><strong>Total Penumpang</strong><br><span style="font-size: 11pt; font-weight: bold; color: #073B2E;">{{ $paxCount }} Pax</span></td>
            <td class="text-center" style="width: 16%;"><strong>Pria (Ikhwan)</strong><br><span style="font-size: 11pt; font-weight: bold;">{{ $malePax }} Pax</span></td>
            <td class="text-center" style="width: 16%;"><strong>Wanita (Akhwat)</strong><br><span style="font-size: 11pt; font-weight: bold;">{{ $femalePax }} Pax</span></td>
            <td class="text-center" style="width: 17%;"><strong>Kamar Quad (Ber-4)</strong><br><span style="font-size: 11pt; font-weight: bold;">{{ $quadPax }} Pax</span></td>
            <td class="text-center" style="width: 17%;"><strong>Kamar Triple (Ber-3)</strong><br><span style="font-size: 11pt; font-weight: bold;">{{ $triplePax }} Pax</span></td>
            <td class="text-center" style="width: 18%;"><strong>Kamar Double (Ber-2)</strong><br><span style="font-size: 11pt; font-weight: bold;">{{ $doublePax }} Pax</span></td>
        </tr>
    </table>

    <!-- TABEL 3: MANIFEST PENUMPANG -->
    <div class="section-title">TABEL 3: DAFTAR NAMA PENUMPANG SESUAI PASPOR</div>
    <table class="manifest-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 75px;">NO REG</th>
                <th>NAMA LENGKAP (SESUAI PASPOR)</th>
                <th style="width: 30px;">L/P</th>
                <th style="width: 35px;">USIA</th>
                <th style="width: 80px;">NO PASPOR</th>
                <th style="width: 65px;">EXP PASPOR</th>
                <th style="width: 70px;">KAMAR</th>
                <th style="width: 85px;">NO TELEPON</th>
                <th style="width: 65px;">STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departure->registrations as $idx => $reg)
                @php
                    $jamaah = $reg->jamaah;
                    $age = $jamaah && $jamaah->birth_date ? \Carbon\Carbon::parse($jamaah->birth_date)->age : '-';
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center fw-bold" style="font-family: monospace;">{{ $reg->registration_number }}</td>
                    <td><strong>{{ strtoupper($jamaah->name ?? '-') }}</strong></td>
                    <td class="text-center">{{ $jamaah->gender ?? '-' }}</td>
                    <td class="text-center">{{ $age }} th</td>
                    <td class="text-center fw-bold">{{ $jamaah->passport_number ?? 'BELUM ADA' }}</td>
                    <td class="text-center">{{ $jamaah && $jamaah->passport_expiry_date ? $jamaah->passport_expiry_date->format('d/m/Y') : ($jamaah && $jamaah->passport_expiry ? \Carbon\Carbon::parse($jamaah->passport_expiry)->format('d/m/Y') : '-') }}</td>
                    <td class="text-center text-uppercase">{{ $reg->room_type }}</td>
                    <td class="text-center">{{ $jamaah->phone ?? '-' }}</td>
                    <td class="text-center">
                        @if($reg->invoice && $reg->invoice->status == 'lunas')
                            <span style="color: #2E7D32; font-weight: bold;">LUNAS</span>
                        @elseif($reg->invoice && $reg->invoice->status == 'sebagian')
                            <span style="color: #B78103; font-weight: bold;">DP</span>
                        @else
                            <span style="color: #C62828;">BELUM</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 15px;">Belum ada jamaah yang terdaftar dalam manifest ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="signatures">
        <tr>
            <td>
                Dibuat Oleh,<br>
                <strong>Staff Operasional &amp; Visa</strong>
                <div class="sign-box"></div>
                ( _________________________ )
            </td>
            <td>
                Mengetahui &amp; Membimbing,<br>
                <strong>Tour Leader / Muthawif</strong>
                <div class="sign-box"></div>
                ( <strong>{{ $departure->leader_name ?? '..................................' }}</strong> )
            </td>
            <td>
                Disetujui Oleh,<br>
                <strong>Direktur Operasional Travel</strong>
                <div class="sign-box"></div>
                ( _________________________ )
            </td>
        </tr>
    </table>
</body>
</html>