<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Formulir Pendaftaran - {{ $jamaah->name }} ({{ $jamaah->registration_number }})</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 1.5cm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            color: #222;
            line-height: 1.4;
            font-size: 11pt;
            background: #fff;
            margin: 0;
            padding: 0;
        }

        .header-table {
            width: 100%;
            border-bottom: 3px double #073B2E;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .logo-cell {
            width: 90px;
            vertical-align: middle;
            text-align: center;
        }

        .logo-img {
            max-width: 85px;
            max-height: 85px;
            object-fit: contain;
        }

        .company-cell {
            vertical-align: middle;
            padding-left: 15px;
        }

        .company-name {
            font-size: 18pt;
            font-weight: bold;
            color: #073B2E;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .company-tagline {
            font-size: 10pt;
            font-weight: bold;
            color: #C9A45B;
            margin: 2px 0 4px 0;
            letter-spacing: 2px;
        }

        .company-address {
            font-size: 8.5pt;
            color: #555;
            margin: 0;
        }

        .form-title {
            text-align: center;
            font-size: 14pt;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 6px;
            color: #073B2E;
        }

        .reg-badge {
            text-align: center;
            font-family: monospace;
            font-size: 11pt;
            font-weight: bold;
            margin-bottom: 20px;
            color: #333;
        }

        .section-title {
            background-color: #F5F0E5;
            color: #073B2E;
            padding: 6px 10px;
            font-size: 11pt;
            font-weight: bold;
            border-left: 4px solid #C9A45B;
            margin: 15px 0 10px 0;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        table.data-table td {
            padding: 5px 6px;
            vertical-align: top;
            font-size: 10pt;
        }

        table.data-table td.label {
            width: 28%;
            color: #555;
            font-weight: 600;
        }

        table.data-table td.separator {
            width: 2%;
            text-align: center;
        }

        table.data-table td.value {
            width: 70%;
            color: #111;
        }

        .terms-box {
            font-size: 8pt;
            color: #666;
            border: 1px solid #ddd;
            padding: 8px;
            margin: 15px 0 25px 0;
            background: #fafafa;
        }

        .signatures {
            width: 100%;
            margin-top: 30px;
        }

        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10pt;
        }

        .sign-line {
            margin-top: 65px;
            border-bottom: 1px solid #333;
            display: inline-block;
            width: 180px;
        }

        .print-btn-bar {
            background: #f1f1f1;
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #ccc;
            margin-bottom: 20px;
        }

        @media print {
            .print-btn-bar {
                display: none;
            }
        }
    </style>
</head>
<body>

    <div class="print-btn-bar">
        <button onclick="window.print()" style="padding: 8px 24px; font-weight: bold; background: #073B2E; color: #fff; border: none; border-radius: 4px; cursor: pointer;">
            🖨️ Cetak Formulir Pendaftaran Sekarang
        </button>
    </div>

    <!-- Kop Surat Resmi -->
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                <img src="{{ asset('assets/brand/logo.png') }}" alt="Logo Al Ghifari Haramain" class="logo-img">
            </td>
            <td class="company-cell">
                <div class="company-name">{{ $setting->company_name ?? 'Al Ghifari Haramain' }}</div>
                <div class="company-tagline">{{ $setting->brand_tagline ?? 'UMROH & HAJI SPECIALIST' }}</div>
                <div class="company-address">
                    {{ $setting->address ?? 'Gedung Wisma Al Ghifari Lt. 2, Jl. Madinah Raya No. 99, Jakarta' }}<br>
                    Telp: {{ $setting->phone ?? '+62 21 8899 7788' }} &bull; WhatsApp: {{ $setting->whatsapp ?? '081234567890' }} &bull; Website: {{ $setting->website ?? 'alghifariharamain.com' }}
                </div>
            </td>
        </tr>
    </table>

    <div class="form-title">Formulir Pendaftaran Perjalanan Haji &amp; Umrah</div>
    <div class="reg-badge">NOMOR REGISTRASI: {{ $jamaah->registration_number }}</div>

    <!-- Data Pribadi -->
    <div class="section-title">I. DATA PRIBADI JAMAAH</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="separator">:</td>
            <td class="value"><strong>{{ $jamaah->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">Nomor Induk Kependudukan (NIK)</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->nik }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Kartu Keluarga (KK)</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->family_card_number ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Jenis Kelamin</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->gender_label }}</td>
        </tr>
        <tr>
            <td class="label">Tempat / Tanggal Lahir</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->birth_place ?? '-' }}, {{ $jamaah->birth_date ? $jamaah->birth_date->translatedFormat('d F Y') : '-' }} (Usia: {{ $jamaah->age ?? '-' }} Tahun)</td>
        </tr>
        <tr>
            <td class="label">Alamat Lengkap</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->address ?? '-' }}, Kota {{ $jamaah->city ?? '-' }}, Prov. {{ $jamaah->province ?? '-' }} (Kode Pos: {{ $jamaah->postal_code ?? '-' }})</td>
        </tr>
        <tr>
            <td class="label">Nomor WhatsApp / HP</td>
            <td class="separator">:</td>
            <td class="value"><strong>{{ $jamaah->phone }}</strong></td>
        </tr>
        <tr>
            <td class="label">Alamat Email</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->email ?? '-' }}</td>
        </tr>
    </table>

    <!-- Data Paspor -->
    <div class="section-title">II. DOKUMEN &amp; PASPOR PERJALANAN</div>
    <table class="data-table">
        <tr>
            <td class="label">Nomor Paspor</td>
            <td class="separator">:</td>
            <td class="value"><strong>{{ $jamaah->passport_number ?? 'Dalam Proses Pembuatan' }}</strong></td>
        </tr>
        <tr>
            <td class="label">Masa Berlaku Paspor</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->passport_expiry_date ? $jamaah->passport_expiry_date->translatedFormat('d F Y') : '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kantor Imigrasi Penerbit</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->passport_issued_place ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Kebutuhan Khusus / Medis</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->special_needs ?? 'Tidak ada catatan khusus (Sehat wal afiat)' }}</td>
        </tr>
    </table>

    <!-- Kontak Darurat -->
    <div class="section-title">III. KONTAK KELUARGA / DARURAT</div>
    <table class="data-table">
        <tr>
            <td class="label">Nama Kontak Darurat</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->emergency_contact_name ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Hubungan Keluarga</td>
            <td class="separator">:</td>
            <td class="value">{{ ucfirst($jamaah->emergency_contact_relation ?? '-') }}</td>
        </tr>
        <tr>
            <td class="label">Nomor Telepon Darurat</td>
            <td class="separator">:</td>
            <td class="value">{{ $jamaah->emergency_contact_phone ?? '-' }}</td>
        </tr>
    </table>

    <div class="terms-box">
        <strong>Pernyataan Calon Jamaah:</strong><br>
        1. Seluruh data identitas kependudukan dan paspor di atas adalah sah, benar, dan dapat dipertanggungjawabkan sesuai hukum.<br>
        2. Calon jamaah menyetujui ketentuan paket perjalanan ibadah, jadwal manasik, dan tata tertib yang ditetapkan oleh PT. Al Ghifari Haramain.<br>
        3. Pelunasan biaya paket wajib diselesaikan sesuai batas waktu jatuh tempo yang tertera pada dokumen Invoice.
    </div>

    <!-- Tanda Tangan -->
    <table class="signatures">
        <tr>
            <td>
                Petugas Administrasi Travel,
                <div class="sign-line"></div>
                <br>({{ auth()->user()->name ?? 'Staff Operasional' }})
            </td>
            <td>
                Jakarta, {{ Carbon\Carbon::now()->translatedFormat('d F Y') }}<br>
                Calon Jamaah / Kuasa Keluarga,
                <div class="sign-line"></div>
                <br><strong>({{ $jamaah->name }})</strong>
            </td>
        </tr>
    </table>

</body>
</html>
