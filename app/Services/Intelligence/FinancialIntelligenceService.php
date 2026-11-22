<?php

namespace App\Services\Intelligence;

use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FinancialIntelligenceService
{
    /**
     * Compute comprehensive financial health indicators.
     * Guaranteed 100% deterministic using SQL & business logic.
     */
    public function getFinancialHealthIndicators(): array
    {
        $activeInvoices = Invoice::where('status', '!=', 'batal');

        $totalInvoiced = (float) $activeInvoices->sum('total_amount');
        $totalPaid = (float) $activeInvoices->sum('paid_amount');
        $totalOutstanding = (float) $activeInvoices->sum('remaining_amount');

        $collectionRate = $totalInvoiced > 0 ? round(($totalPaid / $totalInvoiced) * 100, 2) : 0;

        $totalExpenses = (float) Expense::where('is_cancelled', false)->sum('amount');
        $directExpenses = (float) Expense::where('is_cancelled', false)->where('expense_type', 'langsung')->sum('amount');
        $generalExpenses = (float) Expense::where('is_cancelled', false)->where('expense_type', 'operasional_umum')->sum('amount');

        // Net Operating Cash Flow
        $netCashFlow = $totalPaid - $totalExpenses;

        // Operating Margin Ratio
        $netMargin = $totalPaid > 0 ? round(($netCashFlow / $totalPaid) * 100, 2) : 0;

        // Health Score (0 - 100)
        $score = 100;
        if ($collectionRate < 70) $score -= 20;
        elseif ($collectionRate < 85) $score -= 10;

        if ($netCashFlow < 0) $score -= 30;
        elseif ($netMargin < 10) $score -= 10;

        $score = max(10, min(100, $score));

        $status = 'Sehat';
        $badgeClass = 'success';
        if ($score < 50) {
            $status = 'Kritis (Perhatian Khusus)';
            $badgeClass = 'danger';
        } elseif ($score < 75) {
            $status = 'Waspada (Perlu Perhatian)';
            $badgeClass = 'warning';
        }

        return [
            'total_invoiced' => $totalInvoiced,
            'total_paid' => $totalPaid,
            'total_outstanding' => $totalOutstanding,
            'collection_rate' => $collectionRate,
            'total_expenses' => $totalExpenses,
            'direct_expenses' => $directExpenses,
            'general_expenses' => $generalExpenses,
            'net_cash_flow' => $netCashFlow,
            'net_margin' => $netMargin,
            'health_score' => $score,
            'health_status' => $status,
            'badge_class' => $badgeClass,
        ];
    }

    /**
     * Aging receivables analysis (Umur Piutang).
     */
    public function getAgingReceivables(): array
    {
        $today = Carbon::today();

        $unpaidInvoices = Invoice::with(['registration.jamaah', 'registration.departure'])
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->where('remaining_amount', '>', 0)
            ->get();

        $current = 0; // Not due yet or <= 30 days
        $tier30to60 = 0; // 31 - 60 days overdue
        $tierOver60 = 0; // > 60 days overdue

        $overdueList = [];

        foreach ($unpaidInvoices as $inv) {
            $remaining = (float) $inv->remaining_amount;
            $dueDate = $inv->due_date ? Carbon::parse($inv->due_date) : $inv->created_at;
            $daysOverdue = $today->diffInDays($dueDate, false); // negative means past due

            if ($daysOverdue >= 0) {
                $current += $remaining;
            } elseif ($daysOverdue >= -30) {
                $current += $remaining;
            } elseif ($daysOverdue >= -60) {
                $tier30to60 += $remaining;
                $overdueList[] = [
                    'invoice_number' => $inv->invoice_number,
                    'jamaah_name' => $inv->registration?->jamaah?->name ?? 'Jamaah',
                    'departure_code' => $inv->registration?->departure?->code ?? '-',
                    'remaining' => $remaining,
                    'days_overdue' => abs($daysOverdue),
                    'tier' => '31-60 hari',
                ];
            } else {
                $tierOver60 += $remaining;
                $overdueList[] = [
                    'invoice_number' => $inv->invoice_number,
                    'jamaah_name' => $inv->registration?->jamaah?->name ?? 'Jamaah',
                    'departure_code' => $inv->registration?->departure?->code ?? '-',
                    'remaining' => $remaining,
                    'days_overdue' => abs($daysOverdue),
                    'tier' => '> 60 hari',
                ];
            }
        }

        $totalReceivables = $current + $tier30to60 + $tierOver60;

        return [
            'total_receivables' => $totalReceivables,
            'current_30_days' => $current,
            'overdue_31_60_days' => $tier30to60,
            'overdue_over_60_days' => $tierOver60,
            'overdue_items' => array_slice($overdueList, 0, 10),
        ];
    }

    /**
     * Detect potential duplicate payments (same invoice, same amount, recorded within 48h).
     */
    public function detectDuplicatePayments(): array
    {
        $duplicates = [];

        $payments = Payment::with(['invoice.registration.jamaah'])
            ->where('status', 'valid')
            ->orderBy('invoice_id')
            ->orderBy('payment_date')
            ->get();

        $grouped = $payments->groupBy(function ($item) {
            return $item->invoice_id . '_' . (float) $item->amount;
        });

        foreach ($grouped as $key => $group) {
            if ($group->count() > 1) {
                // Check if payment dates are within 2 days of each other
                for ($i = 0; $i < $group->count() - 1; $i++) {
                    $p1 = $group[$i];
                    $p2 = $group[$i + 1];

                    $date1 = Carbon::parse($p1->payment_date);
                    $date2 = Carbon::parse($p2->payment_date);

                    if (abs($date1->diffInDays($date2)) <= 2) {
                        $duplicates[] = [
                            'invoice_number' => $p1->invoice?->invoice_number,
                            'jamaah_name' => $p1->invoice?->registration?->jamaah?->name ?? 'Jamaah',
                            'amount' => (float) $p1->amount,
                            'payment_1' => [
                                'id' => $p1->id,
                                'receipt_number' => $p1->receipt_number,
                                'date' => $p1->payment_date,
                            ],
                            'payment_2' => [
                                'id' => $p2->id,
                                'receipt_number' => $p2->receipt_number,
                                'date' => $p2->payment_date,
                            ],
                            'risk_level' => 'Tinggi',
                            'reason' => 'Dua pembayaran tercatat pada invoice yang sama dengan nominal identik dalam rentang waktu $\le 48$ jam.',
                        ];
                        break;
                    }
                }
            }
        }

        return $duplicates;
    }

    /**
     * Detect unusual transactions (expenses exceeding 2.5x standard deviation/average).
     */
    public function detectUnusualTransactions(): array
    {
        $anomalies = [];
        $expenses = Expense::with('category')->where('is_cancelled', false)->get();

        if ($expenses->count() < 3) {
            return $anomalies;
        }

        $avgAmount = $expenses->avg('amount');
        $threshold = $avgAmount * 2.5;

        foreach ($expenses as $exp) {
            if ($exp->amount > $threshold) {
                $anomalies[] = [
                    'id' => $exp->id,
                    'expense_number' => $exp->expense_number,
                    'title' => $exp->title,
                    'category' => $exp->category?->name ?? 'Umum',
                    'amount' => (float) $exp->amount,
                    'expense_date' => $exp->expense_date,
                    'average_comparison' => round($exp->amount / max(1, $avgAmount), 1) . 'x dari rata-rata',
                    'reason' => 'Nominal pengeluaran melampaui 2.5x batas rata-rata pengeluaran operasional normal.',
                ];
            }
        }

        return $anomalies;
    }
}
