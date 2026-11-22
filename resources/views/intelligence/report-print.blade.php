<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Executive Intelligence Summary &bull; {{ $setting->company_name ?? 'Al Ghifari Haramain' }}</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; color: #333; line-height: 1.4; margin: 30px; }
        .header { text-align: center; border-bottom: 2px solid #073B2E; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { margin: 0; font-size: 20px; color: #073B2E; }
        .header p { margin: 3px 0; color: #666; font-size: 11px; }
        .kpi-grid { display: flex; gap: 10px; margin-bottom: 20px; }
        .kpi-card { flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 4px; background: #fafafa; }
        .kpi-card .title { font-size: 10px; color: #666; text-transform: uppercase; }
        .kpi-card .value { font-size: 18px; font-weight: bold; margin: 5px 0; color: #073B2E; }
        h3 { font-size: 14px; border-bottom: 1px solid #ccc; padding-bottom: 5px; margin-top: 20px; color: #073B2E; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 20px; }
        table th, table td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        table th { background-color: #f2f2f2; font-size: 11px; }
        .rec-item { border-left: 3px solid #C9A45B; padding-left: 10px; margin-bottom: 10px; }
        .rec-title { font-weight: bold; font-size: 12px; }
        .rec-body { color: #555; font-size: 11px; }
        .footer { text-align: center; font-size: 10px; color: #888; margin-top: 30px; border-top: 1px solid #eee; padding-top: 10px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; background: #073B2E; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Cetak Dokumen
        </button>
    </div>

    <div class="header">
        <h1>{{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}</h1>
        <p>{{ $setting->brand_tagline ?? 'UMROH & HAJI SPECIALIST' }}</p>
        <p>EXECUTIVE INTELLIGENCE & BUSINESS HEALTH REPORT</p>
        <p style="font-size: 10px; color: #999;">Tanggal Cetak: {{ $reportData['generated_at'] }}</p>
    </div>

    <div class="kpi-grid">
        <div class="kpi-card">
            <div class="title">Kesehatan Finansial</div>
            <div class="value">{{ $reportData['financial_health']['health_score'] }}/100</div>
            <div style="font-size: 10px;">{{ $reportData['financial_health']['health_status'] }}</div>
        </div>
        <div class="kpi-card">
            <div class="title">Realisasi Kas Masuk</div>
            <div class="value">{{ $reportData['financial_health']['collection_rate'] }}%</div>
            <div style="font-size: 10px;">Rp {{ number_format($reportData['financial_health']['total_paid'], 0, ',', '.') }}</div>
        </div>
        <div class="kpi-card">
            <div class="title">Beban Operasional</div>
            <div class="value">Rp {{ number_format($reportData['financial_health']['total_expenses'], 0, ',', '.') }}</div>
            <div style="font-size: 10px;">Direct & General</div>
        </div>
        <div class="kpi-card">
            <div class="title">Net Cash Flow</div>
            <div class="value">Rp {{ number_format($reportData['financial_health']['net_cash_flow'], 0, ',', '.') }}</div>
            <div style="font-size: 10px;">Margin: {{ $reportData['financial_health']['net_margin'] }}%</div>
        </div>
    </div>

    <h3>1. Rekomendasi Strategis Berbasis Data Aktual</h3>
    @foreach($reportData['recommendations'] as $rec)
    <div class="rec-item">
        <div class="rec-title">[{{ $rec['priority'] }}] {{ $rec['title'] }} ({{ $rec['category'] }})</div>
        <div class="rec-body">{{ $rec['narrative'] }}</div>
    </div>
    @endforeach

    <h3>2. Kesiapan Keberangkatan (Readiness Scorecard)</h3>
    <table>
        <thead>
            <tr>
                <th>Kloter</th>
                <th>Tanggal Keberangkatan</th>
                <th>Sisa Waktu</th>
                <th>Keterisian Kuota</th>
                <th>Skor Kesiapan</th>
                <th>Status Kesiapan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData['upcoming_readiness'] as $kloter)
            <tr>
                <td><strong>{{ $kloter['code'] }}</strong></td>
                <td>{{ \Carbon\Carbon::parse($kloter['departure_date'])->format('d M Y') }}</td>
                <td>{{ $kloter['days_to_departure'] }} hari</td>
                <td>{{ $kloter['total_registered'] }}/{{ $kloter['quota'] }} seat</td>
                <td>{{ $kloter['readiness_score'] }}%</td>
                <td>{{ $kloter['status_label'] }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Dokumen ini dihasilkan secara otomatis oleh Al Ghifari Intelligence System &bull; Seluruh angka deterministik divalidasi oleh FinanceService.
    </div>
</body>
</html>
