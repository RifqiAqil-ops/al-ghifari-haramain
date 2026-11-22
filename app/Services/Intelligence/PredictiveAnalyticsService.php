<?php

namespace App\Services\Intelligence;

use App\Models\Departure;
use App\Models\Equipment;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Registration;
use Carbon\Carbon;

class PredictiveAnalyticsService
{
    /**
     * Forecast departure occupancy rate using statistical lead-time heuristic curve.
     */
    public function predictDepartureOccupancy(Departure $departure): array
    {
        $currentFilled = $departure->registrations()->where('status', '!=', 'batal')->count();
        $quota = $departure->quota;
        $currentRate = $quota > 0 ? round(($currentFilled / $quota) * 100, 1) : 0;

        $departureDate = Carbon::parse($departure->departure_date);
        $daysUntilDeparture = Carbon::today()->diffInDays($departureDate, false);

        // Data sufficiency check
        $hasSufficientData = $currentFilled > 0;

        if ($daysUntilDeparture <= 0) {
            $predictedFinal = $currentFilled;
            $confidence = 98.00;
            $confidenceLabel = 'Tinggi';
            $status = 'Jadwal Telah Tiba / Selesai';
        } elseif (!$hasSufficientData) {
            // Insufficient initial data
            $predictedFinal = min($quota, round($quota * 0.5));
            $confidence = 45.00;
            $confidenceLabel = 'Rendah (Belum Ada Pendaftar)';
            $status = 'Belum Ada Data Pendaftaran';
        } elseif ($daysUntilDeparture > 60) {
            $additionalProjected = round(($quota - $currentFilled) * 0.65);
            $predictedFinal = min($quota, $currentFilled + $additionalProjected);
            $confidence = 70.00;
            $confidenceLabel = 'Sedang';
            $status = 'Tahap Awal Pemasaran (> 60 Hari)';
        } elseif ($daysUntilDeparture > 15) {
            $additionalProjected = round(($quota - $currentFilled) * 0.40);
            $predictedFinal = min($quota, $currentFilled + $additionalProjected);
            $confidence = 85.00;
            $confidenceLabel = 'Tinggi';
            $status = 'Masa Akselerasi Pendaftaran (16 - 60 Hari)';
        } else {
            $predictedFinal = min($quota, $currentFilled + round(($quota - $currentFilled) * 0.10));
            $confidence = 92.00;
            $confidenceLabel = 'Sangat Tinggi';
            $status = 'Masa Penutupan Kuota (Final Cut-off <= 15 Hari)';
        }

        $predictedRate = $quota > 0 ? round(($predictedFinal / $quota) * 100, 1) : 0;

        return [
            'departure_id' => $departure->id,
            'departure_code' => $departure->code,
            'departure_date' => $departure->departure_date,
            'quota' => $quota,
            'current_filled' => $currentFilled,
            'sample_size' => $currentFilled,
            'current_occupancy_percent' => $currentRate,
            'predicted_final_occupancy' => $predictedFinal,
            'predicted_occupancy_percent' => $predictedRate,
            'days_remaining' => max(0, $daysUntilDeparture),
            'confidence_score' => $confidence,
            'confidence_label' => $confidenceLabel,
            'status_phase' => $status,
            'model_type' => 'Statistical Lead-Time Curve (Heuristic)',
            'formula' => 'Proyeksi = Terisi Saat Ini + (Sisa Kuota * Faktor Akselerasi Periode)',
            'assumptions' => 'Mengikuti kurva historis pendaftaran haji & umrah di mana percepatan pendaftaran terjadi pada H-60 hingga H-15.',
            'limitations_note' => 'Bukan model Machine Learning permanen. Prediksi tidak memperhitungkan promo harga mendadak, kuota visa eksternal, atau pembatalan masal.',
        ];
    }

    /**
     * Forecast equipment inventory demands for upcoming departures.
     */
    public function predictEquipmentNeeds(): array
    {
        $upcomingDepartures = Departure::where('departure_date', '>=', now())
            ->where('departure_date', '<=', now()->addDays(60))
            ->where('status', '!=', 'dibatalkan')
            ->get();

        $totalUpcomingJamaah = 0;
        foreach ($upcomingDepartures as $dep) {
            $totalUpcomingJamaah += $dep->registrations()->where('status', '!=', 'batal')->count();
        }

        $equipments = Equipment::where('is_active', true)->get();
        $predictions = [];

        foreach ($equipments as $item) {
            $projectedDemand = $totalUpcomingJamaah;
            $currentStock = $item->stock;
            $balance = $currentStock - $projectedDemand;
            $isDeficit = $balance < 0;

            $predictions[] = [
                'equipment_id' => $item->id,
                'name' => $item->name,
                'code' => $item->code,
                'current_stock' => $currentStock,
                'min_stock' => $item->min_stock,
                'projected_demand' => $projectedDemand,
                'projected_balance' => $balance,
                'deficit' => $isDeficit ? abs($balance) : 0,
                'status' => $isDeficit ? 'Defisit Terdeteksi' : ($balance <= $item->min_stock ? 'Mendekati Batas Aman' : 'Stok Cukup'),
                'badge' => $isDeficit ? 'danger' : ($balance <= $item->min_stock ? 'warning' : 'success'),
            ];
        }

        $confidence = $upcomingDepartures->count() > 0 ? 88.00 : 50.00;

        return [
            'forecast_window_days' => 60,
            'total_upcoming_jamaah' => $totalUpcomingJamaah,
            'departures_sample_count' => $upcomingDepartures->count(),
            'items' => $predictions,
            'confidence_score' => $confidence,
            'confidence_label' => $confidence >= 80 ? 'Tinggi' : 'Rendah (Belum Ada Kloter Dekat)',
            'model_type' => 'Deterministic Material Requirement Planning (MRP)',
            'formula' => 'Kebutuhan = Jumlah Jamaah Aktif Kloter 60 Hari x 1 pcs; Sisa = Stok - Kebutuhan',
            'assumptions' => 'Setiap jamaah aktif yang berangkat menerima tepat 1 paket standar perlengkapan.',
            'limitations_note' => 'Permintaan barang tambahan di luar paket standar atau kerusakan barang saat pengiriman tidak diperhitungkan dalam estimasi.',
        ];
    }

    /**
     * Forecast 3-month Cash Flow based on verified invoices and historical cost averages.
     */
    public function predictCashFlow(): array
    {
        $today = Carbon::today();
        $monthlyForecast = [];

        for ($i = 0; $i < 3; $i++) {
            $targetMonth = $today->copy()->addMonths($i);
            $monthStart = $targetMonth->copy()->startOfMonth();
            $monthEnd = $targetMonth->copy()->endOfMonth();

            $scheduledReceivables = (float) Invoice::where('status', '!=', 'batal')
                ->whereBetween('due_date', [$monthStart, $monthEnd])
                ->sum('remaining_amount');

            $departuresInMonth = Departure::whereBetween('departure_date', [$monthStart, $monthEnd])
                ->where('status', '!=', 'dibatalkan')
                ->count();

            $historicalExpenses = Expense::where('expense_type', 'langsung')->where('is_cancelled', false);
            $expenseSampleSize = $historicalExpenses->count();
            $avgHistoricalExpensePerKloter = (float) ($historicalExpenses->avg('amount') ?? 15000000);

            $projectedOutflow = $departuresInMonth * max(15000000, $avgHistoricalExpensePerKloter);
            $projectedInflow = $scheduledReceivables > 0 ? $scheduledReceivables : (30000000 * max(1, $departuresInMonth));
            $projectedNet = $projectedInflow - $projectedOutflow;

            $monthlyForecast[] = [
                'month_name' => $targetMonth->translatedFormat('F Y'),
                'projected_inflow' => $projectedInflow,
                'projected_outflow' => $projectedOutflow,
                'projected_net' => $projectedNet,
                'departures_count' => $departuresInMonth,
            ];
        }

        return [
            'forecast_months' => $monthlyForecast,
            'confidence_score' => 75.00,
            'confidence_label' => 'Sedang',
            'model_type' => 'Cash Flow Schedule Aggregation',
            'formula' => 'Net Proyeksi = Piutang Invoice Jatuh Tempo Bulan Terkait - (Kloter x Rata-rata Biaya Langsung Kloter)',
            'assumptions' => 'Jamaah melunasi tagihan tepat pada tanggal jatuh tempo invoice aktif.',
            'limitations_note' => 'Arus kas masuk dihitung berdasarkan due date invoice. Keterlambatan pembayaran jamaah atau biaya darurat di Tanah Suci akan mempengaruhi realisasi kas aktual.',
        ];
    }

    /**
     * Forecast Jamaah Registration Growth (Next 30 days).
     */
    public function predictJamaahGrowth(): array
    {
        $totalActive = Jamaah::where('status', 'aktif')->count();
        $registrationsLast30Days = Registration::where('created_at', '>=', now()->subDays(30))->count();

        // Data sufficiency check: if sample < 3, mark confidence as low
        $isSufficient = $registrationsLast30Days >= 3;
        $projectedNew = max(3, round($registrationsLast30Days * 1.15));
        $confidence = $isSufficient ? 80.00 : 45.00;
        $confidenceLabel = $isSufficient ? 'Tinggi' : 'Rendah (Sampel < 3 transaksi)';

        return [
            'current_active_jamaah' => $totalActive,
            'registrations_last_30_days' => $registrationsLast30Days,
            'sample_size' => $registrationsLast30Days,
            'projected_new_jamaah_next_30_days' => $projectedNew,
            'projected_total_jamaah' => $totalActive + $projectedNew,
            'confidence_score' => $confidence,
            'confidence_label' => $confidenceLabel,
            'model_type' => 'Moving Average Trend Projection',
            'formula' => 'Proyeksi Baru = Pendaftaran 30 Hari Terakhir x 1.15 (Faktor Pertumbuhan)',
            'assumptions' => 'Laju pendaftaran mengikuti tren 30 hari terakhir dengan estimasi pertumbuhan linear 15%.',
            'limitations_note' => $isSufficient
                ? 'Model mengasumsikan tren stabil; siklus musim haji/umrah dan periode liburan sekolah dapat menyebabkan deviasi signifikan.'
                : 'Data pendaftaran 30 hari terakhir kurang dari 3 transaksi. Proyeksi ini merupakan estimasi awal dan memerlukan penambahan data untuk akurasi.',
        ];
    }
}
