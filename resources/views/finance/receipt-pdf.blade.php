<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>KWITANSI PEMBAYARAN - {{ $payment->receipt_number }}</title>
    <style>
        @page {
            margin: 15px 20px;
            size: a5 landscape;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #222222;
            font-size: 11px;
            line-height: 1.35;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #073B2E;
            padding-bottom: 6px;
            margin-bottom: 12px;
        }
        .logo-img {
            max-height: 48px;
        }
        .company-title {
            font-size: 14px;
            font-weight: bold;
            color: #073B2E;
            text-transform: uppercase;
        }
        .company-sub {
            font-size: 8.5px;
            color: #555555;
        }
        .receipt-badge {
            text-align: right;
            font-size: 9px;
        }
        .receipt-title {
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            color: #073B2E;
            letter-spacing: 2px;
            margin-bottom: 14px;
            text-decoration: underline;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .content-table td {
            padding: 4px 6px;
            vertical-align: top;
            font-size: 10.5px;
        }
        .amount-box {
            background-color: #f5f0e5;
            border: 2px solid #c9a45b;
            padding: 8px 14px;
            font-size: 14px;
            font-weight: bold;
            color: #073B2E;
            display: inline-block;
            border-radius: 4px;
        }
        .terbilang-box {
            font-style: italic;
            background-color: #f8f9fa;
            border: 1px dashed #cccccc;
            padding: 4px 8px;
            color: #444444;
        }
        .signatures {
            width: 100%;
            margin-top: 15px;
            border-collapse: collapse;
        }
        .signatures td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 10px;
        }
        .sign-space {
            height: 45px;
        }
        .footer-note {
            font-size: 8px;
            color: #888888;
            margin-top: 12px;
            border-top: 1px dotted #dddddd;
            padding-top: 4px;
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

        // Helper terbilang sederhana
        function terbilangIndonesia($nilai) {
            $nilai = abs($nilai);
            $huruf = array("", "Satu", "Dua", "Tiga", "Empat", "Lima", "Enam", "Tujuh", "Delapan", "Sembilan", "Sepuluh", "Sebelas");
            $temp = "";
            if ($nilai < 12) {
                $temp = " ". $huruf[$nilai];
            } else if ($nilai < 20) {
                $temp = terbilangIndonesia($nilai - 10). " Belas";
            } else if ($nilai < 100) {
                $temp = terbilangIndonesia($nilai/10)." Puluh". terbilangIndonesia($nilai % 10);
            } else if ($nilai < 200) {
                $temp = " Seratus" . terbilangIndonesia($nilai - 100);
            } else if ($nilai < 1000) {
                $temp = terbilangIndonesia($nilai/100) . " Ratus" . terbilangIndonesia($nilai % 100);
            } else if ($nilai < 2000) {
                $temp = " Seribu" . terbilangIndonesia($nilai - 1000);
            } else if ($nilai < 1000000) {
                $temp = terbilangIndonesia($nilai/1000) . " Ribu" . terbilangIndonesia($nilai % 1000);
            } else if ($nilai < 1000000000) {
                $temp = terbilangIndonesia($nilai/1000000) . " Juta" . terbilangIndonesia($nilai % 1000000);
            } else if ($nilai < 1000000000000) {
                $temp = terbilangIndonesia($nilai/1000000000) . " Milyar" . terbilangIndonesia(fmod($nilai,1000000000));
            }
            return trim($temp);
        }

        $jamaah = $payment->invoice->registration->jamaah ?? null;
        $package = $payment->invoice->registration->package ?? null;
    @endphp

    <table class="header-table">
        <tr>
            <td style="width: 25%;">
                @if($logoBase64)
                    <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo">
                @else
                    <strong style="color: #073B2E; font-size: 16px;">AL GHIFARI</strong>
                @endif
            </td>
            <td style="width: 50%; text-align: center;">
                <div class="company-title">{{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}</div>
                <div class="company-sub">{{ $setting->company_tagline ?? 'UMROH & HAJI SPECIALIST' }}</div>
                <div class="company-sub">{{ $setting->company_address ?? 'Jl. Boulevard Raya, Jakarta' }} | Telp: {{ $setting->company_phone ?? '-' }}</div>
            </td>
            <td style="width: 25%;" class="receipt-badge">
                <strong>No. Kwitansi:</strong><br>
                <span style="font-size: 11px; font-weight: bold; color: #073B2E;">{{ $payment->receipt_number }}</span><br>
                Tgl: {{ $payment->payment_date ? $payment->payment_date->translatedFormat('d F Y') : '-' }}
            </td>
        </tr>
    </table>

    <div class="receipt-title">KWITANSI RESMI PEMBAYARAN</div>

    <table class="content-table">
        <tr>
            <td style="width: 22%; font-weight: bold;">Telah Diterima Dari</td>
            <td style="width: 3%;">:</td>
            <td style="width: 75%; font-weight: bold; font-size: 11px; color: #073B2E;">
                {{ strtoupper($payment->sender_name ?? $jamaah->name ?? 'Jamaah') }}
                @if($jamaah && $jamaah->phone) <span style="font-size: 9.5px; font-weight: normal; color: #666;">({{ $jamaah->phone }})</span> @endif
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Uang Sejumlah</td>
            <td>:</td>
            <td class="terbilang-box">
                *** {{ strtoupper(terbilangIndonesia($payment->amount)) }} RUPIAH ***
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Untuk Pembayaran</td>
            <td>:</td>
            <td>
                Pembayaran <strong>{{ strtoupper($payment->payment_type) }}</strong> paket <strong>{{ $package->name ?? 'Haji/Umrah' }}</strong>
                (No. Reg: {{ $payment->invoice->registration->registration_number ?? '-' }} | No. Invoice: {{ $payment->invoice->invoice_number }})
                @if($payment->notes) <br><span style="font-size: 9px; color: #555;">Catatan: {{ $payment->notes }}</span> @endif
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Metode Pembayaran</td>
            <td>:</td>
            <td>
                {{ strtoupper($payment->payment_method) }} @if($payment->bank_name) (Bank: {{ $payment->bank_name }}) @endif
            </td>
        </tr>
        <tr>
            <td style="font-weight: bold;">Status Tagihan</td>
            <td>:</td>
            <td>
                Total Tagihan: Rp {{ number_format($payment->invoice->total_amount, 0, ',', '.') }} | 
                Sisa Tagihan: <strong style="color: #c0392b;">Rp {{ number_format($payment->invoice->remaining_amount, 0, ',', '.') }}</strong>
                ({{ strtoupper($payment->invoice->status) }})
            </td>
        </tr>
    </table>

    <table style="width: 100%;">
        <tr>
            <td style="width: 45%; vertical-align: middle;">
                <div class="amount-box">
                    Rp {{ number_format($payment->amount, 0, ',', '.') }},-
                </div>
            </td>
            <td style="width: 55%; vertical-align: middle;">
                <table class="signatures">
                    <tr>
                        <td>
                            Penyetor / Jamaah,
                            <div class="sign-space"></div>
                            ( <strong>{{ $payment->sender_name ?? $jamaah->name ?? '........................' }}</strong> )
                        </td>
                        <td>
                            Kasir / Keuangan Travel,
                            <div class="sign-space"></div>
                            ( <strong>{{ $payment->receiver->name ?? 'Finance Staff' }}</strong> )
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        * Kwitansi ini sah apabila telah diverifikasi dan dibubuhi tanda tangan oleh manajemen Al Ghifari Haramain. Simpan kwitansi ini sebagai bukti pembayaran resmi Anda.
    </div>
</body>
</html>
