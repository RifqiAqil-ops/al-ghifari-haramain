<table>
    <!-- Header Perusahaan -->
    <tr>
        <th colspan="4" style="font-size: 16pt; font-weight: bold; color: #073B2E; text-align: left;">
            {{ $setting->company_name ?? 'AL GHIFARI HARAMAIN' }}
        </th>
    </tr>
    <tr>
        <th colspan="4" style="font-size: 10pt; color: #C9A45B; font-weight: bold; text-align: left;">
            {{ $setting->company_tagline ?? 'UMROH &amp; HAJI SPECIALIST' }}
        </th>
    </tr>
    <tr>
        <th colspan="4" style="font-size: 9pt; color: #555555; text-align: left;">
            Laporan Keuangan Laba Rugi Operasional &bull; Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }} &bull; Dicetak: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB
        </th>
    </tr>
    <tr><td colspan="4"></td></tr>

    <!-- TABEL 1: PENDAPATAN OPERASIONAL -->
    <tr>
        <th colspan="4" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            TABEL 1: PENDAPATAN OPERASIONAL (KAS MASUK)
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 40px; text-align: center;">NO</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 250px;">KOMPONEN PENDAPATAN</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 180px; text-align: right;">NOMINAL (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; width: 120px; text-align: right;">KONTRIBUSI (%)</th>
    </tr>
    <tr>
        <td style="border: 1px solid #CCCCCC; text-align: center;">1</td>
        <td style="border: 1px solid #CCCCCC;">Penerimaan Pembayaran Paket Haji &amp; Umrah</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #198754;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
    </tr>
    <tr style="background-color: #E8F5E9; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #CCCCCC; text-align: right;">TOTAL PENDAPATAN OPERASIONAL:</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; color: #198754;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
    </tr>

    <!-- Pemisah Antar Tabel -->
    <tr><td colspan="4"></td></tr>
    <tr><td colspan="4"></td></tr>

    <!-- TABEL 2: BEBAN PENGELUARAN OPERASIONAL -->
    <tr>
        <th colspan="4" style="background-color: #C9A45B; color: #000000; font-weight: bold; font-size: 11pt;">
            TABEL 2: BEBAN PENGELUARAN OPERASIONAL
        </th>
    </tr>
    <tr>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: center;">NO</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">KATEGORI BIAYA OPERASIONAL</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">NOMINAL (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">PORSI BEBAN (%)</th>
    </tr>
    @php $expIdx = 1; @endphp
    @forelse($expensesByCategory as $catName => $items)
        @php
            $catTotal = $items->sum('amount');
        @endphp
        <tr>
            <td style="border: 1px solid #CCCCCC; text-align: center;">{{ $expIdx++ }}</td>
            <td style="border: 1px solid #CCCCCC;">{{ $catName ?? 'Operasional Umum' }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #DC3545;">Rp {{ number_format($catTotal, 0, ',', '.') }}</td>
            <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalExpense > 0 ? round(($catTotal / $totalExpense) * 100, 1) : 0 }}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="4" style="border: 1px solid #CCCCCC; text-align: center; padding: 8px;">
                Belum ada data pengeluaran operasional pada periode ini.
            </td>
        </tr>
    @endforelse
    <tr style="background-color: #FFEBEE; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #CCCCCC; text-align: right;">TOTAL BEBAN OPERASIONAL:</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; color: #DC3545;">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
    </tr>

    <!-- Pemisah Antar Tabel -->
    <tr><td colspan="4"></td></tr>
    <tr><td colspan="4"></td></tr>

    <!-- TABEL 3: REKAPITULASI LABA BERSIH -->
    <tr>
        <th colspan="4" style="background-color: #073B2E; color: #FFFFFF; font-weight: bold; font-size: 11pt;">
            TABEL 3: REKAPITULASI LABA / RUGI BERSIH OPERASIONAL
        </th>
    </tr>
    <tr>
        <th colspan="2" style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC;">INDIKATOR KEUANGAN</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">NOMINAL (RP)</th>
        <th style="background-color: #F5F0E5; font-weight: bold; border: 1px solid #CCCCCC; text-align: right;">MARGIN</th>
    </tr>
    <tr>
        <td colspan="2" style="border: 1px solid #CCCCCC;">Total Pendapatan Masuk</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #198754;">Rp {{ number_format($totalIncome, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">100%</td>
    </tr>
    <tr>
        <td colspan="2" style="border: 1px solid #CCCCCC;">Total Beban Operasional</td>
        <td style="border: 1px solid #CCCCCC; text-align: right; font-weight: bold; color: #DC3545;">Rp {{ number_format($totalExpense, 0, ',', '.') }}</td>
        <td style="border: 1px solid #CCCCCC; text-align: right;">{{ $totalIncome > 0 ? round(($totalExpense / $totalIncome) * 100, 1) : 0 }}%</td>
    </tr>
    <tr style="background-color: #073B2E; color: #FFFFFF; font-weight: bold;">
        <td colspan="2" style="border: 1px solid #000000; font-size: 11pt; color: #FFFFFF;">LABA / (RUGI) BERSIH OPERASIONAL:</td>
        <td style="border: 1px solid #000000; text-align: right; font-size: 11pt; color: #E5CF96;">Rp {{ number_format($netProfit, 0, ',', '.') }}</td>
        <td style="border: 1px solid #000000; text-align: right; color: #FFFFFF;">{{ $totalIncome > 0 ? round(($netProfit / $totalIncome) * 100, 1) : 0 }}%</td>
    </tr>
</table>
