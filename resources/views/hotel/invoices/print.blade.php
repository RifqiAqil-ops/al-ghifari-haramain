<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 20px;
            font-size: 13px;
        }
        .header {
            border-bottom: 2px solid #0B4939;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .company-name {
            font-size: 20px;
            font-weight: bold;
            color: #0B4939;
        }
        .invoice-title {
            font-size: 18px;
            font-weight: bold;
            text-align: right;
            color: #C9A45B;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .table th {
            background-color: #f8f9fa;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: bold;
        }
        .total-box {
            margin-top: 15px;
            float: right;
            width: 320px;
        }
    </style>
</head>
<body onload="window.print()">
    <table width="100%" class="header">
        <tr>
            <td>
                <div class="company-name">PT. AL GHIFARI HAJJ HARAMAIN</div>
                <div>Layanan Reservasi &amp; Akomodasi Hotel Haji Umrah</div>
                <div>Jakarta &bull; Makkah &bull; Madinah</div>
            </td>
            <td class="text-right">
                <div class="invoice-title">HOTEL INVOICE</div>
                <div><strong>No:</strong> {{ $invoice->invoice_number }}</div>
                <div><strong>Tanggal:</strong> {{ $invoice->invoice_date->format('d F Y') }}</div>
                <div><strong>Jatuh Tempo:</strong> {{ $invoice->due_date->format('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table width="100%" style="margin-bottom: 20px;">
        <tr>
            <td width="55%" style="vertical-align: top;">
                <strong>Tagihan Kepada:</strong><br>
                {{ $invoice->customer?->name }}<br>
                {{ $invoice->customer?->company_name }}<br>
                Telepon: {{ $invoice->customer?->phone ?? '-' }}
            </td>
            <td width="45%" style="vertical-align: top;" class="text-right">
                <strong>Status Pembayaran:</strong><br>
                <span class="badge" style="background-color: #e2f0d9; color: #385723;">
                    {{ strtoupper($invoice->status) }}
                </span><br>
                <small>Kurs Acuan: 1 SAR = Rp {{ number_format($invoice->exchange_rate_snapshot, 2, ',', '.') }}</small>
            </td>
        </tr>
    </table>

    <table class="table">
        <thead>
            <tr>
                <th>Hotel &amp; Tipe Kamar</th>
                <th class="text-center">Check-in</th>
                <th class="text-center">Check-out</th>
                <th class="text-center">Mlm</th>
                <th class="text-center">Kmr</th>
                <th class="text-center">Room Nights</th>
                <th class="text-right">Tarif/Mlm (SAR)</th>
                <th class="text-right">Subtotal (SAR)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($invoice->items as $item)
            <tr>
                <td>
                    <strong>{{ $item->hotel_name }}</strong><br>
                    <small>{{ $item->room_type_name }}</small>
                </td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->check_in)->format('d/m/Y') }}</td>
                <td class="text-center">{{ \Carbon\Carbon::parse($item->check_out)->format('d/m/Y') }}</td>
                <td class="text-center">{{ $item->number_of_nights }}</td>
                <td class="text-center">{{ $item->room_quantity }}</td>
                <td class="text-center font-weight-bold">{{ $item->room_nights }}</td>
                <td class="text-right">SAR {{ number_format($item->rate_per_night_sar, 2, ',', '.') }}</td>
                <td class="text-right font-weight-bold">SAR {{ number_format($item->subtotal_sar, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="total-box">
        <table class="table">
            <tr>
                <td><strong>Total Tagihan (SAR):</strong></td>
                <td class="text-right font-weight-bold">SAR {{ number_format($invoice->total_amount_sar, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Total Terbayar (SAR):</strong></td>
                <td class="text-right" style="color: green;">SAR {{ number_format($invoice->paid_amount_sar, 2, ',', '.') }}</td>
            </tr>
            <tr>
                <td><strong>Sisa Tagihan (SAR):</strong></td>
                <td class="text-right" style="color: red; font-size: 15px;"><strong>SAR {{ number_format($invoice->remaining_amount_sar, 2, ',', '.') }}</strong></td>
            </tr>
            <tr style="background-color: #f5f0e5;">
                <td><strong>Ekuivalen IDR:</strong></td>
                <td class="text-right font-weight-bold" style="color: #073b2e;">Rp {{ number_format($invoice->total_amount_idr, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div style="clear: both; margin-top: 40px; padding-top: 20px; border-top: 1px solid #ddd;">
        <strong>Syarat &amp; Ketentuan:</strong>
        <p style="font-size: 12px; color: #555;">{{ $invoice->payment_terms }}</p>
    </div>
</body>
</html>
