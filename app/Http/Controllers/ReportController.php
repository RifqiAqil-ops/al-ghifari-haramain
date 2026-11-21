<?php

namespace App\Http\Controllers;

use App\Exports\DeparturesExport;
use App\Exports\DeparturesProfitLossExport;
use App\Exports\PaymentsExport;
use App\Exports\ProfitLossExport;
use App\Exports\ReceivablesExport;
use App\Models\CompanySetting;
use App\Models\Departure;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use App\Services\ProfitLossService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('reports.index');
    }

    public function payments(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $method = $request->input('method');

        $query = Payment::with(['invoice.registration.jamaah', 'invoice.registration.package'])
            ->where('status', 'valid')
            ->whereBetween('payment_date', [$startDate, $endDate]);

        if ($method) {
            $query->where('payment_method', $method);
        }

        $payments = $query->latest('payment_date')->get();
        $totalAmount = $payments->sum('amount');
        $setting = CompanySetting::first();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.payments-pdf', compact('payments', 'totalAmount', 'startDate', 'endDate', 'setting'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_Pembayaran_' . $startDate . '_sd_' . $endDate . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return Excel::download(new PaymentsExport($payments, $totalAmount, $startDate, $endDate, $setting), 'Laporan_Pembayaran_' . $startDate . '_sd_' . $endDate . '.xlsx');
        }

        return view('reports.payments', compact('payments', 'totalAmount', 'startDate', 'endDate', 'method'));
    }

    public function receivables(Request $request)
    {
        $query = Invoice::with(['registration.jamaah', 'registration.package', 'registration.departure'])
            ->whereIn('status', ['belum_bayar', 'sebagian']);

        $invoices = $query->orderBy('due_date', 'asc')->get();
        $totalReceivable = $invoices->sum('remaining_amount');
        $setting = CompanySetting::first();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.receivables-pdf', compact('invoices', 'totalReceivable', 'setting'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_Tunggakan_Jamaah_' . date('Ymd') . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return Excel::download(new ReceivablesExport($invoices, $totalReceivable, $setting), 'Laporan_Tunggakan_Jamaah_' . date('Ymd') . '.xlsx');
        }

        return view('reports.receivables', compact('invoices', 'totalReceivable'));
    }

    public function profitLoss(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));

        $payments = Payment::where('status', 'valid')
            ->whereBetween('payment_date', [$startDate, $endDate])
            ->get();

        $expenses = Expense::with('category')
            ->where('is_cancelled', false)
            ->whereBetween('expense_date', [$startDate, $endDate])
            ->get();

        $totalIncome = $payments->sum('amount');
        $totalExpense = $expenses->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        $expensesByCategory = $expenses->groupBy('category.name');
        $setting = CompanySetting::first();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.profit-loss-pdf', compact(
                'totalIncome',
                'totalExpense',
                'netProfit',
                'expensesByCategory',
                'startDate',
                'endDate',
                'setting'
            ))->setPaper('a4', 'portrait');

            return $pdf->download('Laporan_Laba_Rugi_' . $startDate . '_sd_' . $endDate . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return Excel::download(new ProfitLossExport($totalIncome, $totalExpense, $netProfit, $expensesByCategory, $startDate, $endDate, $setting), 'Laporan_Laba_Rugi_' . $startDate . '_sd_' . $endDate . '.xlsx');
        }

        return view('reports.profit-loss', compact(
            'totalIncome',
            'totalExpense',
            'netProfit',
            'expensesByCategory',
            'startDate',
            'endDate'
        ));
    }

    public function departures(Request $request)
    {
        $year = $request->input('year', Carbon::now()->year);

        $departures = Departure::with(['package'])
            ->withCount(['registrations' => fn($q) => $q->where('status', '!=', 'batal')])
            ->whereYear('departure_date', $year)
            ->orderBy('departure_date', 'asc')
            ->get();

        $setting = CompanySetting::first();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.departures-pdf', compact('departures', 'year', 'setting'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_Keberangkatan_' . $year . '.pdf');
        }

        if ($request->input('export') === 'excel') {
            return Excel::download(new DeparturesExport($departures, $year, $setting), 'Laporan_Keberangkatan_' . $year . '.xlsx');
        }

        return view('reports.departures', compact('departures', 'year'));
    }

    /**
     * Laporan Laba Rugi per Kloter (Sebelum & Setelah Alokasi Biaya Umum).
     */
    public function departuresProfitLoss(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfYear()->format('Y-m-d'));
        $endDate   = $request->input('end_date', Carbon::now()->endOfYear()->format('Y-m-d'));
        $departureId = $request->input('departure_id');
        $packageId   = $request->input('package_id');
        $status      = $request->input('status');
        $method      = $request->input('allocation_method', 'proporsional_jamaah');

        $filters = [
            'start_date'        => $startDate,
            'end_date'          => $endDate,
            'departure_id'      => $departureId,
            'package_id'        => $packageId,
            'status'            => $status,
            'allocation_method' => $method,
        ];

        $report = ProfitLossService::calculateDeparturesProfitLoss($filters);
        $setting = CompanySetting::first();

        // Export PDF
        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.departures-profit-loss-pdf', compact('report', 'setting'))
                ->setPaper('a4', 'landscape');
            return $pdf->download('Laporan_Laba_Rugi_Kloter_' . ($startDate ?: 'All') . '_sd_' . ($endDate ?: 'All') . '.pdf');
        }

        // Export Excel
        if ($request->input('export') === 'excel') {
            return Excel::download(new DeparturesProfitLossExport($report, $setting), 'Laporan_Laba_Rugi_Kloter_' . date('Ymd_His') . '.xlsx');
        }

        $allDepartures = Departure::where('status', '!=', 'dibatalkan')->orderBy('departure_date', 'desc')->get();
        $packages      = Package::where('status', 'aktif')->orderBy('name')->get();

        return view('reports.departures-profit-loss', compact(
            'report',
            'allDepartures',
            'packages',
            'startDate',
            'endDate',
            'departureId',
            'packageId',
            'status',
            'method'
        ));
    }

    /**
     * Detail Laporan Laba Rugi untuk satu Kloter spesifik.
     */
    public function departureProfitLossDetail(Request $request, Departure $departure)
    {
        $departure->load(['package']);
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $detail = ProfitLossService::calculateSingleDepartureProfitLoss($departure, [
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        $setting = CompanySetting::first();

        if ($request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.departure-profit-loss-detail-pdf', compact('departure', 'detail', 'setting'))
                ->setPaper('a4', 'portrait');
            return $pdf->download('Detail_Laba_Rugi_' . $departure->code . '.pdf');
        }

        return view('reports.departure-profit-loss-detail', compact('departure', 'detail', 'setting', 'startDate', 'endDate'));
    }
}
