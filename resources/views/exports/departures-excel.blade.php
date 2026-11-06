<table>
    <!-- Header Perusahaan -->
    <tr>
        <th colspan="10" style="font-size: 16pt; font-weight: bold; color: #073B2E; text-align: left;">
            {{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}
        </th>
    </tr>
    <tr>
        <th colspan="10" style="font-size: 10pt; color: #C9A45B; font-weight: bold; text-align: left;">
            {{ $setting->company_tagline ?? 'UMROH &amp; HAJI SPECIALIST' }}
        </th>
    </tr>
    <tr>
        <th colspan="10" style="font-size: 9pt; color: #555555; text-align: left;">
            Laporan Rekapitulasi Jadwal Keberangkatan &bull; Tahun: {{ $year }} &bull; Diekspor pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </th>
    </tr>
    <tr><td colspan="10"></td></tr>

    <!-- TABEL 1: RINGKASAN OKUPANSI -->
    <tr>
        <th colspan="4" style="background-color: #C9A45B; color: #000000; font-weight: bold; font-size: 11pt;">
            TABEL 1: RINGKASAN REKAPITULASI KLOTER TAHUN {{ $year }}
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Parameter Keberangkatan</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Total Kuota / Pax</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Okupansi (%)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Keterangan</th>
    </tr>
    @php
        $totalQuota = $departures->sum('quota');
        $totalRegistered = $departures->sum('registrations_count');
        $totalRemaining = max(0, $totalQuota - $totalRegistered);
    @endphp
    <tr>
        <td style="border: 1px solid #CCCCCC;">Total Kloter Terjadwal</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">{{ $departures->count() }} Jadwal</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">-</td>
        <td style="border: 1px solid #CCCCCC;">Jadwal penerbangan tahun {{ $year }}</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Total Alokasi Kuota Seat</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">{{ $totalQuota }} Pax</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
        <td style="border: 1px solid #CCCCCC;">Total kursi yang dibuka untuk dipasarkan</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Total Jamaah Terdaftar</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #198754;">{{ $totalRegistered }} Pax</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalQuota > 0 ? round(($totalRegistered / $totalQuota) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Realisasi kursi terisi</td>
    </tr>
    <tr style="background-color: #E2E8F0; font-weight: bold;">
        <td style="border: 1px solid #CCCCCC;">Sisa Kuota Tersedia</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; color: #073B2E;">{{ $totalRemaining }} Pax</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalQuota > 0 ? round(($totalRemaining / $totalQuota) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Seat yang masih dapat menerima jamaah</td>
    </tr>

    <!-- Pemisah Antar Tabel -->
    <tr><td colspan="10"></td></tr>
    <tr><td colspan="10"></td></tr>

    <!-- TABEL 2: RINCIAN LENGKAP -->
    <tr>
        <th colspan="10" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            TABEL 2: RINCIAN JADWAL KLOTER &amp; REALISASI KUOTA
        </th>
    </tr>
    <tr>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">NO</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">KODE KLOTER</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">PAKET PERJALANAN</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">MASKAPAI</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">TGL BERANGKAT</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">TGL KEPULANGAN</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">KUOTA</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">TERISI</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: right;">SISA SEAT</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">STATUS</th>
    </tr>
    @forelse($departures as $idx => $dep)
        @php
            $rem = max(0, $dep->quota - $dep->registrations_count);
        @endphp
        <tr>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #CCCCCC; font-weight: bold;">{{ $dep->code }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $dep->package->name ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $dep->airline ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $dep->departure_date ? $dep->departure_date->format('d/m/Y') : '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $dep->return_date ? $dep->return_date->format('d/m/Y') : '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $dep->quota }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #198754;">{{ $dep->registrations_count }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; color: #073B2E;">{{ $rem }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center; text-transform: uppercase;">{{ $dep->status_label }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="10" style="border: 1px solid #CCCCCC; text-align: center; padding: 10px;">
                Belum ada jadwal keberangkatan pada tahun {{ $year }}.
            </td>
        </tr>
    @endforelse
    <tr style="background-color: #F5F0E5; font-weight: bold;">
        <td colspan="6" style="border: 1px solid #073B2E; text-align: right;">TOTAL:</td>
        <td style="border: 1px solid #073B2E; text-align: right;">{{ $totalQuota }}</td>
        <td style="border: 1px solid #073B2E; text-align: right; color: #198754;">{{ $totalRegistered }}</td>
        <td style="border: 1px solid #073B2E; text-align: right; color: #073B2E;">{{ $totalRemaining }}</td>
        <td style="border: 1px solid #073B2E;"></td>
    </tr>
</table>
