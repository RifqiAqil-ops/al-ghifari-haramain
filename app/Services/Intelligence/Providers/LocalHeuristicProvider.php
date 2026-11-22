<?php

namespace App\Services\Intelligence\Providers;

use App\Models\Departure;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\Intelligence\Contracts\AiProviderInterface;
use Carbon\Carbon;

class LocalHeuristicProvider implements AiProviderInterface
{
    public function isAvailable(): bool
    {
        return true;
    }

    public function getProviderName(): string
    {
        return 'local_heuristic';
    }

    public function chat(string $prompt, array $context = [], array $history = []): array
    {
        $promptLower = strtolower($prompt);
        $grounding = [
            'engine' => 'Al Ghifari Heuristic Intelligence Engine v1.0',
            'timestamp' => now()->toIso8601String(),
            'database_synced' => true,
        ];

        // 1. Intent: Jamaah & Pendaftaran
        if (str_contains($promptLower, 'jamaah') || str_contains($promptLower, 'daftar') || str_contains($promptLower, 'peserta')) {
            $totalJamaah = Jamaah::count();
            $activeJamaah = Jamaah::where('status', 'aktif')->count();
            $totalRegistrations = Registration::count();
            $activeRegistrations = Registration::where('status', '!=', 'batal')->count();
            $cancelledRegistrations = Registration::where('status', 'batal')->count();

            $grounding['sources'] = ['jamaahs', 'registrations'];
            $grounding['period'] = 'Keseluruhan Data Aktif';

            $content = "### Ringkasan Data Jamaah & Pendaftaran (Sumber Database Terverifikasi)\n\n" .
                "- **Total Master Jamaah**: " . number_format($totalJamaah, 0, ',', '.') . " orang\n" .
                "- **Jamaah Status Aktif**: " . number_format($activeJamaah, 0, ',', '.') . " orang\n" .
                "- **Total Riwayat Registrasi**: " . number_format($totalRegistrations, 0, ',', '.') . " transaksi\n" .
                "- **Registrasi Berjalan (Non-Batal)**: " . number_format($activeRegistrations, 0, ',', '.') . " jamaah\n" .
                "- **Registrasi Dibatalkan**: " . number_format($cancelledRegistrations, 0, ',', '.') . " jamaah\n\n" .
                "**Rekomendasi Tindakan**:\n" .
                "- Pastikan seluruh jamaah aktif pada keberangkatan terdekat telah melengkapi dokumen paspor dan KTP di menu *Smart Document Intelligence*.\n" .
                "- Untuk jamaah berstatus batal, periksa apakah terdapat pengajuan *refund* yang masih menggantung di modul keuangan.";

            return [
                'content' => $content,
                'tokens_used' => 120,
                'grounding' => $grounding,
                'provider' => $this->getProviderName(),
            ];
        }

        // 2. Intent: Keuangan, Piutang, Pembayaran, Tagihan, Kas
        if (str_contains($promptLower, 'uang') || str_contains($promptLower, 'bayar') || str_contains($promptLower, 'piutang') || str_contains($promptLower, 'tagih') || str_contains($promptLower, 'kas') || str_contains($promptLower, 'laba') || str_contains($promptLower, 'rugi')) {
            $totalInvoiced = Invoice::where('status', '!=', 'batal')->sum('total_amount');
            $totalPaid = Invoice::where('status', '!=', 'batal')->sum('paid_amount');
            $totalOutstanding = Invoice::where('status', '!=', 'batal')->sum('remaining_amount');
            $unpaidCount = Invoice::whereIn('status', ['belum_bayar', 'sebagian'])->count();
            $totalExpense = Expense::where('is_cancelled', false)->sum('amount');

            $grounding['sources'] = ['invoices', 'payments', 'expenses'];
            $grounding['metrics'] = [
                'total_invoiced' => $totalInvoiced,
                'total_paid' => $totalPaid,
                'outstanding' => $totalOutstanding,
                'total_expense' => $totalExpense,
            ];

            $content = "### Analisis Keuangan & Piutang Operasional (Sumber FinanceService)\n\n" .
                "- **Total Nilai Invoice Berjalan**: Rp " . number_format($totalInvoiced, 0, ',', '.') . "\n" .
                "- **Penerimaan Kas Masuk (Paid)**: Rp " . number_format($totalPaid, 0, ',', '.') . "\n" .
                "- **Sisa Piutang Jamaah (Outstanding)**: Rp " . number_format($totalOutstanding, 0, ',', '.') . "\n" .
                "- **Jumlah Tagihan Belum Lunas**: " . number_format($unpaidCount, 0, ',', '.') . " invoice\n" .
                "- **Total Pengeluaran Operasional**: Rp " . number_format($totalExpense, 0, ',', '.') . "\n\n" .
                "**Wawasan Arus Kas**:\n" .
                "- Rasio penagihan kas saat ini berada di angka **" . ($totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 1) : 0) . "%**.\n" .
                "- Sistem menyarankan otomatisasi pengingat via WhatsApp untuk tagihan yang jatuh tempo dalam $\\le 7$ hari ke depan guna mengamankan likuiditas operasional keberangkatan.";

            return [
                'content' => $content,
                'tokens_used' => 140,
                'grounding' => $grounding,
                'provider' => $this->getProviderName(),
            ];
        }

        // 3. Intent: Keberangkatan, Kloter, Flight, Hotel
        if (str_contains($promptLower, 'berangkat') || str_contains($promptLower, 'kloter') || str_contains($promptLower, 'jadwal') || str_contains($promptLower, 'departure') || str_contains($promptLower, 'hotel') || str_contains($promptLower, 'pesawat')) {
            $upcomingDepartures = Departure::where('departure_date', '>=', now())
                ->where('status', '!=', 'dibatalkan')
                ->orderBy('departure_date', 'asc')
                ->take(5)
                ->get();

            $grounding['sources'] = ['departures', 'packages'];
            $grounding['count'] = $upcomingDepartures->count();

            $content = "### Status Keberangkatan & Kloter Mendatang\n\n";
            if ($upcomingDepartures->isEmpty()) {
                $content .= "Saat ini belum ada jadwal keberangkatan aktif yang terdaftar untuk periode mendatang.\n";
            } else {
                $content .= "Daftar 5 kloter keberangkatan terdekat:\n\n";
                foreach ($upcomingDepartures as $dep) {
                    $filled = $dep->registrations()->where('status', '!=', 'batal')->count();
                    $daysRemaining = Carbon::parse($dep->departure_date)->diffInDays(now(), false);
                    $daysLabel = $daysRemaining < 0 ? abs($daysRemaining) . " hari lagi" : "hari ini";
                    $content .= "- **Kloter {$dep->code}** (" . Carbon::parse($dep->departure_date)->format('d M Y') . ", {$daysLabel}): Terisi {$filled}/{$dep->quota} seat (" . ($dep->quota > 0 ? round(($filled / $dep->quota) * 100) : 0) . "%). Maskapai: " . ($dep->airline ?? 'Belum ditentukan') . ".\n";
                }
            }

            $content .= "\n**Rekomendasi Kloter**:\n" .
                "- Silakan tinjau halaman *Smart Departure Intelligence* untuk memeriksa *Departure Readiness Score* masing-masing kloter (kelengkapan visa, tiket penerbangan, hotel, dan distribusi koper).";

            return [
                'content' => $content,
                'tokens_used' => 150,
                'grounding' => $grounding,
                'provider' => $this->getProviderName(),
            ];
        }

        // 4. Intent: Perlengkapan, Logistik, Stok, Koper, Kain Ihram
        if (str_contains($promptLower, 'stok') || str_contains($promptLower, 'logistik') || str_contains($promptLower, 'perlengkapan') || str_contains($promptLower, 'barang') || str_contains($promptLower, 'koper')) {
            $lowStockItems = Equipment::whereColumn('stock', '<=', 'min_stock')->where('is_active', true)->get();
            $totalItems = Equipment::count();

            $grounding['sources'] = ['equipments', 'equipment_mutations'];

            $content = "### Status Logistik & Perlengkapan Jamaah\n\n" .
                "- **Total Jenis Barang Terdaftar**: " . number_format($totalItems, 0, ',', '.') . " item\n";

            if ($lowStockItems->isNotEmpty()) {
                $content .= "- **Peringatan Stok Menipis (" . $lowStockItems->count() . " item)**:\n";
                foreach ($lowStockItems as $item) {
                    $content .= "  - **{$item->name}**: Sisa {$item->stock} {$item->unit} (Batas minimum: {$item->min_stock} {$item->unit})\n";
                }
                $content .= "\n**Tindakan Prioritas**: Lakukan restock segera sebelum jadwal distribusi kloter berikutnya dimulai.";
            } else {
                $content .= "- Seluruh perlengkapan saat ini berada di atas ambang batas minimum stok aman.\n";
            }

            return [
                'content' => $content,
                'tokens_used' => 110,
                'grounding' => $grounding,
                'provider' => $this->getProviderName(),
            ];
        }

        // 5. Default General Intelligence Summary
        $activeJamaah = Jamaah::where('status', 'aktif')->count();
        $upcomingDeparturesCount = Departure::where('departure_date', '>=', now())->where('status', '!=', 'dibatalkan')->count();
        $totalOutstanding = Invoice::where('status', '!=', 'batal')->sum('remaining_amount');

        $content = "Halo! Saya adalah **Al Ghifari Intelligence Assistant**.\n\n" .
            "Saya dapat membantu Anda memantau dan menganalisis operasional travel secara real-time berdasarkan basis data aktual perusahaan:\n\n" .
            "- **Operasional Jamaah**: Saat ini ada **" . number_format($activeJamaah, 0, ',', '.') . " jamaah aktif** terdaftar.\n" .
            "- **Kloter Aktif**: Ada **" . number_format($upcomingDeparturesCount, 0, ',', '.') . " jadwal keberangkatan** yang sedang dipersiapkan.\n" .
            "- **Piutang Berjalan**: Sisa piutang tertagih saat ini tercatat sebesar **Rp " . number_format($totalOutstanding, 0, ',', '.') . "**.\n\n" .
            "Silakan tanyakan hal spesifik, seperti:\n" .
            "1. *\"Berapa sisa tagihan piutang jamaah saat ini?\"*\n" .
            "2. *\"Bagaimana status kloter keberangkatan terdekat?\"*\n" .
            "3. *\"Apakah ada stok perlengkapan yang menipis?\"*\n" .
            "4. *\"Tampilkan ringkasan kesiapan operasional bulan ini.\"*";

        return [
            'content' => $content,
            'tokens_used' => 160,
            'grounding' => $grounding,
            'provider' => $this->getProviderName(),
        ];
    }
}
