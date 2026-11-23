<?php

namespace App\Services\Hotel;

use App\Models\Hotel;
use App\Models\HotelCost;
use App\Models\HotelCustomer;
use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use App\Models\HotelReservation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelFinancialService
{
    /**
     * Get independent Hotel Financial Performance metrics.
     * Guaranteed deterministic, separated between SAR operations and IDR reporting.
     */
    public function getHotelFinancialSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $invoiceQuery = HotelInvoice::where('status', '!=', 'batal');
        $paymentQuery = HotelPayment::where('status', 'valid');
        $costQuery = HotelCost::query();

        if ($startDate && $endDate) {
            $invoiceQuery->whereBetween('invoice_date', [$startDate, $endDate]);
            $paymentQuery->whereBetween('payment_date', [$startDate, $endDate]);
            $costQuery->whereBetween('cost_date', [$startDate, $endDate]);
        }

        // 1. Invoices & Sales
        $totalSalesSar = (float) $invoiceQuery->sum('total_amount_sar');
        $totalSalesIdr = (float) $invoiceQuery->sum('total_amount_idr');

        // 2. Cash Collected (Actual payments received)
        $cashCollectedSar = (float) $paymentQuery->sum('equivalent_amount_sar');
        $cashCollectedIdr = (float) $paymentQuery->sum('equivalent_amount_idr');

        // 3. Outstanding Receivables
        $receivablesSar = (float) $invoiceQuery->sum('remaining_amount_sar');
        $receivablesIdr = (float) $invoiceQuery->sum('remaining_amount_idr');

        // 4. Procurement & Operating Costs
        $totalCostsSar = (float) $costQuery->sum('amount_sar');
        $totalCostsIdr = (float) $costQuery->sum('amount_idr');

        // 5. Foreign Exchange Gain / Loss Realized
        $totalFxGainLossIdr = (float) $paymentQuery->sum('fx_gain_loss_idr');

        // 6. Gross Profit Calculation (Sales - Procurement Cost)
        $grossProfitSar = $totalSalesSar - $totalCostsSar;
        $grossProfitIdr = $totalSalesIdr - $totalCostsIdr;

        // 7. Net Profit including FX Difference
        $netProfitIdr = $grossProfitIdr + $totalFxGainLossIdr;

        // Counts
        $totalInvoicesCount = $invoiceQuery->count();
        $unpaidInvoicesCount = (clone $invoiceQuery)->whereIn('status', ['issued', 'sebagian'])->count();
        $totalReservationsCount = HotelReservation::where('status', '!=', 'cancelled')->count();

        return [
            'total_sales_sar' => $totalSalesSar,
            'total_sales_idr' => $totalSalesIdr,
            'cash_collected_sar' => $cashCollectedSar,
            'cash_collected_idr' => $cashCollectedIdr,
            'receivables_sar' => $receivablesSar,
            'receivables_idr' => $receivablesIdr,
            'total_costs_sar' => $totalCostsSar,
            'total_costs_idr' => $totalCostsIdr,
            'gross_profit_sar' => $grossProfitSar,
            'gross_profit_idr' => $grossProfitIdr,
            'fx_gain_loss_idr' => $totalFxGainLossIdr,
            'net_profit_idr' => $netProfitIdr,
            'invoices_count' => $totalInvoicesCount,
            'unpaid_invoices_count' => $unpaidInvoicesCount,
            'reservations_count' => $totalReservationsCount,
        ];
    }

    /**
     * Get consolidated financial metrics across Haji, Umrah, and Hotel business lines.
     */
    public function getConsolidatedCompanyMetrics(): array
    {
        // 1. Haji & Umrah (from Invoices & Expenses)
        $hajiUmrahInvoices = \App\Models\Invoice::where('status', '!=', 'batal');
        $hajiUmrahSalesIdr = (float) $hajiUmrahInvoices->sum('total_amount');
        $hajiUmrahPaidIdr = (float) $hajiUmrahInvoices->sum('paid_amount');
        $hajiUmrahReceivablesIdr = (float) $hajiUmrahInvoices->sum('remaining_amount');
        $hajiUmrahExpensesIdr = (float) \App\Models\Expense::where('is_cancelled', false)->sum('amount');
        $hajiUmrahProfitIdr = $hajiUmrahPaidIdr - $hajiUmrahExpensesIdr;

        // 2. Hotel Business Management
        $hotelMetrics = $this->getHotelFinancialSummary();

        // 3. Consolidated Figures (Reporting in IDR)
        $consolidatedSalesIdr = $hajiUmrahSalesIdr + $hotelMetrics['total_sales_idr'];
        $consolidatedCashInIdr = $hajiUmrahPaidIdr + $hotelMetrics['cash_collected_idr'];
        $consolidatedReceivablesIdr = $hajiUmrahReceivablesIdr + $hotelMetrics['receivables_idr'];
        $consolidatedOutflowIdr = $hajiUmrahExpensesIdr + $hotelMetrics['total_costs_idr'];
        $consolidatedProfitIdr = $hajiUmrahProfitIdr + $hotelMetrics['net_profit_idr'];

        return [
            'haji_umrah' => [
                'sales_idr' => $hajiUmrahSalesIdr,
                'cash_in_idr' => $hajiUmrahPaidIdr,
                'receivables_idr' => $hajiUmrahReceivablesIdr,
                'expenses_idr' => $hajiUmrahExpensesIdr,
                'profit_idr' => $hajiUmrahProfitIdr,
            ],
            'hotel' => [
                'sales_sar' => $hotelMetrics['total_sales_sar'],
                'sales_idr' => $hotelMetrics['total_sales_idr'],
                'cash_in_sar' => $hotelMetrics['cash_collected_sar'],
                'cash_in_idr' => $hotelMetrics['cash_collected_idr'],
                'receivables_sar' => $hotelMetrics['receivables_sar'],
                'receivables_idr' => $hotelMetrics['receivables_idr'],
                'costs_sar' => $hotelMetrics['total_costs_sar'],
                'costs_idr' => $hotelMetrics['total_costs_idr'],
                'fx_gain_loss_idr' => $hotelMetrics['fx_gain_loss_idr'],
                'profit_idr' => $hotelMetrics['net_profit_idr'],
            ],
            'consolidated' => [
                'total_revenue_idr' => $consolidatedSalesIdr,
                'total_cash_inflow_idr' => $consolidatedCashInIdr,
                'total_receivables_idr' => $consolidatedReceivablesIdr,
                'total_expenses_idr' => $consolidatedOutflowIdr,
                'net_operating_profit_idr' => $consolidatedProfitIdr,
            ],
        ];
    }
}
