<?php

namespace App\Services;

use App\Models\Departure;
use App\Models\Expense;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ProfitLossService
{
    /**
     * Menghitung Laporan Laba Rugi per Kloter lengkap dengan dua versi:
     * 1. Sebelum alokasi biaya operasional umum (Direct Margin).
     * 2. Setelah alokasi biaya operasional umum (Net Profit Kloter).
     *
     * Kebijakan Akuntansi:
     * - Pendapatan: Basis nilai penjualan kontrak paket jamaah terdaftar aktif (final_price, status != 'batal')
     *   yang terikat pada kloter keberangkatan (departure_date).
     * - Biaya Langsung: Job-Order Costing. Seluruh beban operasional yang terikat secara spesifik pada kloter (is_cancelled = false).
     * - Biaya Operasional Umum: Period Costing (berdasarkan expense_date). Beban operasional umum/kantor.
     * - Integritas Denominator Alokasi: Denominator alokasi beban umum MENGGUNAKAN SELURUH KLOTER AKTIF
     *   dalam periode alokasi (Allocation Pool), sehingga filter tampilan (misal filter per kloter/paket)
     *   TIDAK mendistorsi rasio proporsional maupun beban teralokasi masing-masing kloter.
     */
    public static function calculateDeparturesProfitLoss(array $filters = []): array
    {
        $departureId = $filters['departure_id'] ?? null;
        $packageId   = $filters['package_id'] ?? null;
        $status      = $filters['status'] ?? null;
        $startDate   = $filters['start_date'] ?? null;
        $endDate     = $filters['end_date'] ?? null;
        $allocationMethod = $filters['allocation_method'] ?? 'proporsional_jamaah';

        // 1. Query seluruh Kloter yang masuk dalam Lingkup Alokasi Periode (Allocation Pool)
        $poolQuery = Departure::with(['package'])
            ->where('status', '!=', 'dibatalkan');

        if ($startDate && $endDate) {
            $poolQuery->whereBetween('departure_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $poolQuery->where('departure_date', '>=', $startDate);
        } elseif ($endDate) {
            $poolQuery->where('departure_date', '<=', $endDate);
        }

        $poolDepartures = $poolQuery->orderBy('departure_date', 'asc')->get();

        // 2. Query Biaya Operasional Umum dalam periode laporan (Period Costing)
        $genExpenseQuery = Expense::with('category')
            ->where('is_cancelled', false)
            ->where(function ($q) {
                $q->where('expense_type', 'operasional_umum')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('expense_type')->whereNull('departure_id');
                  });
            });

        if ($startDate && $endDate) {
            $genExpenseQuery->whereBetween('expense_date', [$startDate, $endDate]);
        } elseif ($startDate) {
            $genExpenseQuery->where('expense_date', '>=', $startDate);
        } elseif ($endDate) {
            $genExpenseQuery->where('expense_date', '<=', $endDate);
        }

        $generalExpenses = $genExpenseQuery->orderBy('expense_date', 'asc')->get();
        $totalGeneralExpense = (float) $generalExpenses->sum('amount');

        // 3. Hitung metrik dasar untuk seluruh kloter dalam Allocation Pool
        $poolKloterData = [];
        $totalPeriodActiveJamaah = 0;

        foreach ($poolDepartures as $departure) {
            // Jamaah aktif (tidak batal dan tidak terhapus)
            $activeRegistrations = Registration::where('departure_id', $departure->id)
                ->where('status', '!=', 'batal')
                ->get();

            $activeJamaahCount = $activeRegistrations->count();
            $totalRegisteredCount = Registration::where('departure_id', $departure->id)->count();

            // Total Pendapatan Kloter (Basis Nilai Paket Kontrak Penjualan Jamaah)
            $kloterRevenue = (float) $activeRegistrations->sum('final_price');

            // Biaya Langsung Kloter (Job-Order Costing: seluruh pengeluaran yang terikat pada kloter ini)
            $directExpenses = Expense::with('category')
                ->where('is_cancelled', false)
                ->where('departure_id', $departure->id)
                ->where(function ($q) {
                    $q->where('expense_type', 'langsung')
                      ->orWhereNull('expense_type');
                })
                ->orderBy('expense_date', 'asc')
                ->get();

            $kloterDirectExpense = (float) $directExpenses->sum('amount');

            // Rincian Biaya Langsung per Kategori
            $directExpensesByCategory = $directExpenses->groupBy(fn($exp) => $exp->category->name ?? 'Lain-lain')
                ->map(fn($group) => [
                    'category_name' => $group->first()->category->name ?? 'Lain-lain',
                    'count'         => $group->count(),
                    'total_amount'  => (float) $group->sum('amount'),
                ]);

            // Laba Rugi Sebelum Alokasi
            $profitBefore = $kloterRevenue - $kloterDirectExpense;
            $marginBefore = $kloterRevenue > 0 ? ($profitBefore / $kloterRevenue) * 100 : 0;

            $totalPeriodActiveJamaah += $activeJamaahCount;

            $poolKloterData[$departure->id] = [
                'departure'                 => $departure,
                'active_jamaah_count'       => $activeJamaahCount,
                'total_registered_count'    => $totalRegisteredCount,
                'revenue'                   => $kloterRevenue,
                'direct_expense'            => $kloterDirectExpense,
                'direct_expenses_list'      => $directExpenses,
                'direct_expenses_by_cat'    => $directExpensesByCategory,
                'profit_before_allocation'  => $profitBefore,
                'margin_before_allocation'  => round($marginBefore, 2),
                'allocated_general_expense' => 0.0,
                'allocation_ratio'          => 0.0,
                'total_expense_after'       => $kloterDirectExpense,
                'profit_after_allocation'   => $profitBefore,
                'margin_after_allocation'   => round($marginBefore, 2),
                'cost_per_jamaah'           => $activeJamaahCount > 0 ? round($kloterDirectExpense / $activeJamaahCount, 2) : 0,
                'profit_per_jamaah'         => $activeJamaahCount > 0 ? round($profitBefore / $activeJamaahCount, 2) : 0,
                'status_profit'             => $profitBefore > 0 ? 'untung' : ($profitBefore < 0 ? 'rugi' : 'impas'),
            ];
        }

        // 4. Distribusi Alokasi Biaya Operasional Umum pada tingkat Pool Seluruh Periode
        $totalPoolAllocatedGeneralExpense = 0.0;

        if ($totalGeneralExpense > 0 && $totalPeriodActiveJamaah > 0 && count($poolKloterData) > 0) {
            $runningAllocatedSum = 0.0;
            $keys = array_keys($poolKloterData);
            $lastKey = end($keys);

            foreach ($poolKloterData as $depId => &$item) {
                if ($item['active_jamaah_count'] > 0) {
                    $ratio = $item['active_jamaah_count'] / $totalPeriodActiveJamaah;
                    $item['allocation_ratio'] = round($ratio * 100, 2);

                    if ($depId === $lastKey) {
                        // Kloter terakhir menyerap selisih pembulatan agar total alokasi pas 100% dari biaya umum
                        $allocatedAmount = round($totalGeneralExpense - $runningAllocatedSum, 2);
                    } else {
                        $allocatedAmount = round($ratio * $totalGeneralExpense, 2);
                        $runningAllocatedSum += $allocatedAmount;
                    }
                } else {
                    $item['allocation_ratio'] = 0.0;
                    $allocatedAmount = 0.0;
                }

                $item['allocated_general_expense'] = $allocatedAmount;
                $totalPoolAllocatedGeneralExpense += $allocatedAmount;

                // Perhitungan Setelah Alokasi (Net Profit Kloter)
                $totalExpenseAfter = $item['direct_expense'] + $allocatedAmount;
                $profitAfter = $item['revenue'] - $totalExpenseAfter;
                $marginAfter = $item['revenue'] > 0 ? ($profitAfter / $item['revenue']) * 100 : 0;

                $item['total_expense_after']     = $totalExpenseAfter;
                $item['profit_after_allocation'] = $profitAfter;
                $item['margin_after_allocation'] = round($marginAfter, 2);
                $item['cost_per_jamaah']         = $item['active_jamaah_count'] > 0 ? round($totalExpenseAfter / $item['active_jamaah_count'], 2) : 0;
                $item['profit_per_jamaah']       = $item['active_jamaah_count'] > 0 ? round($profitAfter / $item['active_jamaah_count'], 2) : 0;
                $item['status_profit']           = $profitAfter > 0 ? 'untung' : ($profitAfter < 0 ? 'rugi' : 'impas');
            }
            unset($item);
        }

        // 5. Terapkan Display Filter (jika user memfilter tampilan berdasarkan kloter, paket, atau status)
        // Nilai alokasi dan rasio kloter tetap stabil karena sudah dihitung dari denominator periode penuh
        $filteredKloterData = [];
        $isFilteredView = false;

        foreach ($poolKloterData as $depId => $item) {
            $dep = $item['departure'];

            if ($departureId && (int) $dep->id !== (int) $departureId) {
                $isFilteredView = true;
                continue;
            }

            if ($packageId && (int) $dep->package_id !== (int) $packageId) {
                $isFilteredView = true;
                continue;
            }

            if ($status && $dep->status !== $status) {
                $isFilteredView = true;
                continue;
            }

            $filteredKloterData[$depId] = $item;
        }

        // Hitung total agregat untuk kloter yang ditampilkan di view
        $displayActiveJamaah = 0;
        $displayRevenue = 0.0;
        $displayDirectExpense = 0.0;
        $displayAllocatedGeneralExpense = 0.0;

        foreach ($filteredKloterData as $item) {
            $displayActiveJamaah += $item['active_jamaah_count'];
            $displayRevenue += $item['revenue'];
            $displayDirectExpense += $item['direct_expense'];
            $displayAllocatedGeneralExpense += $item['allocated_general_expense'];
        }

        $totalProfitBefore = $displayRevenue - $displayDirectExpense;
        $totalProfitAfter  = $displayRevenue - ($displayDirectExpense + $displayAllocatedGeneralExpense);
        $unallocatedGeneralExpense = max(0, $totalGeneralExpense - $totalPoolAllocatedGeneralExpense);

        return [
            'kloters'                      => collect($filteredKloterData),
            'general_expenses'             => $generalExpenses,
            'total_revenue'                => $displayRevenue,
            'total_direct_expense'         => $displayDirectExpense,
            'total_general_expense'        => $totalGeneralExpense,
            'allocated_general_expense'    => $displayAllocatedGeneralExpense,
            'unallocated_general_expense'  => $unallocatedGeneralExpense,
            'total_profit_before'          => $totalProfitBefore,
            'total_profit_after'           => $totalProfitAfter,
            'total_active_jamaah'          => $displayActiveJamaah,
            'total_period_active_jamaah'   => $totalPeriodActiveJamaah,
            'total_kloter_count'           => count($filteredKloterData),
            'total_period_kloter_count'    => count($poolKloterData),
            'is_filtered_view'             => $isFilteredView,
            'allocation_method'            => $allocationMethod,
            'allocation_method_label'      => 'Proporsional Jumlah Jamaah Aktif',
            'filters'                      => $filters,
            'start_date'                   => $startDate,
            'end_date'                     => $endDate,
        ];
    }

    /**
     * Menghitung Laporan Laba Rugi khusus untuk 1 Kloter tertentu (Detail).
     */
    public static function calculateSingleDepartureProfitLoss(Departure $departure, array $filters = []): array
    {
        $allReport = self::calculateDeparturesProfitLoss(array_merge($filters, [
            'start_date' => $filters['start_date'] ?? null,
            'end_date'   => $filters['end_date'] ?? null,
        ]));

        $kloterItem = $allReport['kloters']->get($departure->id);

        if (!$kloterItem) {
            // Jika kloter tidak masuk filter periode, hitung khusus kloter ini sendiri
            $activeRegistrations = Registration::with('jamaah')
                ->where('departure_id', $departure->id)
                ->where('status', '!=', 'batal')
                ->get();

            $activeCount = $activeRegistrations->count();
            $revenue = (float) $activeRegistrations->sum('final_price');

            $directExpenses = Expense::with('category')
                ->where('is_cancelled', false)
                ->where('departure_id', $departure->id)
                ->orderBy('expense_date', 'asc')
                ->get();

            $directTotal = (float) $directExpenses->sum('amount');
            $profitBefore = $revenue - $directTotal;

            $kloterItem = [
                'departure'                 => $departure,
                'active_jamaah_count'       => $activeCount,
                'total_registered_count'    => Registration::where('departure_id', $departure->id)->count(),
                'revenue'                   => $revenue,
                'direct_expense'            => $directTotal,
                'direct_expenses_list'      => $directExpenses,
                'direct_expenses_by_cat'    => $directExpenses->groupBy(fn($e) => $e->category->name ?? 'Lain-lain')->map(fn($g) => [
                    'category_name' => $g->first()->category->name ?? 'Lain-lain',
                    'count'         => $g->count(),
                    'total_amount'  => (float) $g->sum('amount'),
                ]),
                'profit_before_allocation'  => $profitBefore,
                'margin_before_allocation'  => $revenue > 0 ? round(($profitBefore / $revenue) * 100, 2) : 0,
                'allocated_general_expense' => 0.0,
                'allocation_ratio'          => 0.0,
                'total_expense_after'       => $directTotal,
                'profit_after_allocation'   => $profitBefore,
                'margin_after_allocation'   => $revenue > 0 ? round(($profitBefore / $revenue) * 100, 2) : 0,
                'cost_per_jamaah'           => $activeCount > 0 ? round($directTotal / $activeCount, 2) : 0,
                'profit_per_jamaah'         => $activeCount > 0 ? round($profitBefore / $activeCount, 2) : 0,
                'status_profit'             => $profitBefore > 0 ? 'untung' : ($profitBefore < 0 ? 'rugi' : 'impas'),
            ];
        }

        // Ambil daftar jamaah aktif beserta rincian nilai invoice dan pembayaran
        $jamaahList = Registration::with(['jamaah', 'invoice.validPayments'])
            ->where('departure_id', $departure->id)
            ->where('status', '!=', 'batal')
            ->get();

        return [
            'item'             => $kloterItem,
            'jamaah_list'      => $jamaahList,
            'general_expenses' => $allReport['general_expenses'],
            'total_report'     => $allReport,
        ];
    }
}
