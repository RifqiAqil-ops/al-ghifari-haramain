<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Departure;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Jamaah;
use App\Models\Package;
use App\Models\Payment;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->input('period', 'all'); // all, today, this_month, this_year, custom
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Date filter builder helper
        $applyDateFilter = function ($query, $column = 'created_at') use ($period, $startDate, $endDate) {
            if ($period === 'today') {
                $query->whereDate($column, Carbon::today());
            } elseif ($period === 'this_month') {
                $query->whereMonth($column, Carbon::now()->month)
                      ->whereYear($column, Carbon::now()->year);
            } elseif ($period === 'this_year') {
                $query->whereYear($column, Carbon::now()->year);
            } elseif ($period === 'custom' && $startDate && $endDate) {
                $query->whereBetween($column, [$startDate . ' 00:00:00', $endDate . ' 23:59:59']);
            }
            return $query;
        };

        // 1. Metric Counters
        $totalJamaah = Jamaah::count();
        $activeJamaah = Jamaah::where('status', 'aktif')->count();

        // Jamaah per travel type
        $hajiJamaah = Registration::whereHas('package', fn($q) => $q->where('type', 'haji'))
            ->where('status', '!=', 'batal')
            ->count();
        $umrahJamaah = Registration::whereHas('package', fn($q) => $q->where('type', 'umrah'))
            ->where('status', '!=', 'batal')
            ->count();

        $totalPackages = Package::where('status', '!=', 'arsip')->count();

        $upcomingDepartures = Departure::with(['package'])
            ->where('departure_date', '>=', Carbon::today())
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('departure_date', 'asc')
            ->take(5)
            ->get();

        // Financial Metrics with period filter
        $totalPaymentsQuery = Payment::where('status', 'valid');
        $totalPayments = (float) $applyDateFilter(clone $totalPaymentsQuery, 'payment_date')->sum('amount');

        $totalExpensesQuery = Expense::where('is_cancelled', false);
        $totalExpenses = (float) $applyDateFilter(clone $totalExpensesQuery, 'expense_date')->sum('amount');

        $unpaidInvoicesCount = Invoice::whereIn('status', ['belum_bayar', 'sebagian'])->count();
        $totalOutstanding = (float) Invoice::whereIn('status', ['belum_bayar', 'sebagian'])->sum('remaining_amount');

        // 2. Chart Data: Monthly Revenue (Last 6 Months)
        $revenueMonths = [];
        $revenueValues = [];
        $expenseValues = [];
        $registrationValues = [];

        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthLabel = $monthDate->translatedFormat('M Y');
            $year = $monthDate->year;
            $month = $monthDate->month;

            $rev = (float) Payment::where('status', 'valid')
                ->whereYear('payment_date', $year)
                ->whereMonth('payment_date', $month)
                ->sum('amount');

            $exp = (float) Expense::where('is_cancelled', false)
                ->whereYear('expense_date', $year)
                ->whereMonth('expense_date', $month)
                ->sum('amount');

            $reg = Registration::where('status', '!=', 'batal')
                ->whereYear('registration_date', $year)
                ->whereMonth('registration_date', $month)
                ->count();

            $revenueMonths[] = $monthLabel;
            $revenueValues[] = $rev;
            $expenseValues[] = $exp;
            $registrationValues[] = $reg;
        }

        // 3. Alerts & Reminders
        // Incomplete documents: Jamaahs with registration but missing verified KTP or Paspor
        $incompleteDocsJamaahs = Jamaah::whereHas('registrations', fn($q) => $q->where('status', '!=', 'batal'))
            ->whereDoesntHave('documents', fn($q) => $q->where('document_type', 'paspor')->where('status', 'diverifikasi'))
            ->take(5)
            ->get();

        // Due payments: Invoices due in next 10 days
        $dueInvoices = Invoice::with(['registration.jamaah', 'registration.package'])
            ->whereIn('status', ['belum_bayar', 'sebagian'])
            ->whereNotNull('due_date')
            ->where('due_date', '<=', Carbon::today()->addDays(10))
            ->orderBy('due_date', 'asc')
            ->take(5)
            ->get();

        // Upcoming departures in < 30 days
        $nearDepartures = Departure::with(['package'])
            ->whereBetween('departure_date', [Carbon::today(), Carbon::today()->addDays(30)])
            ->where('status', '!=', 'dibatalkan')
            ->orderBy('departure_date', 'asc')
            ->take(5)
            ->get();

        // 4. Recent Activities
        $recentActivities = ActivityLog::latest()
            ->take(8)
            ->get();

        return view('dashboard.index', compact(
            'period',
            'startDate',
            'endDate',
            'totalJamaah',
            'activeJamaah',
            'hajiJamaah',
            'umrahJamaah',
            'totalPackages',
            'upcomingDepartures',
            'totalPayments',
            'totalExpenses',
            'unpaidInvoicesCount',
            'totalOutstanding',
            'revenueMonths',
            'revenueValues',
            'expenseValues',
            'registrationValues',
            'incompleteDocsJamaahs',
            'dueInvoices',
            'nearDepartures',
            'recentActivities'
        ));
    }
}
