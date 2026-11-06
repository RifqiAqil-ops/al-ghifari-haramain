<table>
    <!-- Header Perusahaan -->
    <tr>
        <th colspan="12" style="font-size: 16pt; font-weight: bold; color: #073B2E; text-align: left;">
            {{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}
        </th>
    </tr>
    <tr>
        <th colspan="12" style="font-size: 10pt; color: #C9A45B; font-weight: bold; text-align: left;">
            {{ $setting->company_tagline ?? 'UMROH &amp; HAJI SPECIALIST' }}
        </th>
    </tr>
    <tr>
        <th colspan="12" style="font-size: 9pt; color: #555555; text-align: left;">
            Laporan Direktori Master Data Jamaah &bull; Diekspor pada: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </th>
    </tr>
    <tr><td colspan="12"></td></tr>

    <!-- TABEL 1: RINGKASAN DATA JAMAAH -->
    <tr>
        <th colspan="4" style="background-color: #C9A45B; color: #000000; font-weight: bold; font-size: 11pt;">
            TABEL 1: RINGKASAN DATA JAMAAH
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Kategori</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">Jumlah Pax</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Persentase</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">Keterangan</th>
    </tr>
    @php
        $totalPax = $jamaahs->count();
        $maleCount = $jamaahs->where('gender', 'L')->count();
        $femaleCount = $jamaahs->where('gender', 'P')->count();
        $activeCount = $jamaahs->where('status', 'aktif')->count();
        $passportCount = $jamaahs->whereNotNull('passport_number')->count();
    @endphp
    <tr>
        <td style="border: 1px solid #CCCCCC;">Total Jamaah Terdaftar</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold;">{{ $totalPax }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
        <td style="border: 1px solid #CCCCCC;">Seluruh jamaah dalam database</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Jamaah Laki-laki (Ikhwan)</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $maleCount }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalPax > 0 ? round(($maleCount / $totalPax) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Jamaah pria</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Jamaah Perempuan (Akhwat)</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $femaleCount }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalPax > 0 ? round(($femaleCount / $totalPax) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Jamaah wanita</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Sudah Memiliki Paspor</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $passportCount }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalPax > 0 ? round(($passportCount / $totalPax) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Nomor paspor tercatat di sistem</td>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC;">Status Aktif</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $activeCount }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalPax > 0 ? round(($activeCount / $totalPax) * 100, 1) : 0 }}%</td>
        <td style="border: 1px solid #CCCCCC;">Akun aktif operasional</td>
    </tr>

    <!-- Pemisah Antar Tabel -->
    <tr><td colspan="12"></td></tr>
    <tr><td colspan="12"></td></tr>

    <!-- TABEL 2: RINCIAN LENGKAP MASTER DATA JAMAAH -->
    <tr>
        <th colspan="12" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            TABEL 2: RINCIAN LENGKAP MASTER DATA JAMAAH
        </th>
    </tr>
    <tr>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">NO</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NO. REGISTRASI</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NAMA LENGKAP</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NIK</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">NO. PASPOR</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">EXP PASPOR</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">GENDER</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">TGL LAHIR</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">WHATSAPP</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">KOTA DOMISILI</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; text-align: center;">STATUS</th>
        <th style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">CATATAN KHUSUS</th>
    </tr>
    @forelse($jamaahs as $idx => $j)
        <tr>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $idx + 1 }}</td>
            <td style="border: 1px solid #CCCCCC; font-weight: bold;">{{ $j->registration_number }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $j->name }}</td>
            <td style="border: 1px solid #CCCCCC; mso-number-format:'\@';">{{ $j->nik }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $j->passport_number ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $j->passport_expiry_date ? $j->passport_expiry_date->format('d/m/Y') : '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $j->gender }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $j->birth_date ? $j->birth_date->format('d/m/Y') : '-' }}</td>
            <td style="border: 1px solid #CCCCCC; mso-number-format:'\@';">{{ $j->phone }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $j->city ?? '-' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: center; font-weight: bold;">{{ strtoupper($j->status) }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $j->special_needs ?? '-' }}</td>
        </tr>
    @empty
        <tr>
            <td colspan="12" style="border: 1px solid #CCCCCC; text-align: center; padding: 10px;">
                Belum ada data jamaah terdaftar.
            </td>
        </tr>
    @endforelse
</table>
