<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\CompanySetting;
use App\Models\Departure;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Refund;
use App\Models\Registration;
use App\Models\TransactionCategory;
use App\Services\ActivityLogger;
use App\Services\FinanceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinanceController extends Controller
{
    public function summary(Request $request): View
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $pQuery = Payment::where('status', 'valid');
        $eQuery = Expense::where('is_cancelled', false);

        if ($startDate && $endDate) {
            $pQuery->whereBetween('payment_date', [$startDate, $endDate]);
            $eQuery->whereBetween('expense_date', [$startDate, $endDate]);
        }

        $totalIncome = (float) $pQuery->sum('amount');
        $totalExpense = (float) $eQuery->sum('amount');
        $netProfit = $totalIncome - $totalExpense;

        $totalReceivable = (float) Invoice::whereIn('status', ['belum_bayar', 'sebagian'])->sum('remaining_amount');

        $recentPayments = Payment::with(['invoice.registration.jamaah'])
            ->latest('payment_date')
            ->take(6)
            ->get();

        $recentExpenses = Expense::with(['category', 'departure'])
            ->latest('expense_date')
            ->take(6)
            ->get();

        return view('finance.summary', compact(
            'totalIncome',
            'totalExpense',
            'netProfit',
            'totalReceivable',
            'recentPayments',
            'recentExpenses',
            'startDate',
            'endDate'
        ));
    }

    public function invoices(Request $request): View
    {
        $search = $request->input('search');
        $status = $request->input('status');

        $query = Invoice::with(['registration.jamaah', 'registration.package', 'registration.departure']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('registration.jamaah', fn($jq) => $jq->where('name', 'like', "%{$search}%")->orWhere('registration_number', 'like', "%{$search}%"));
            });
        }

        if ($status) {
            $query->where('status', $status);
        }

        $invoices = $query->latest()->paginate(10)->withQueryString();

        return view('finance.invoices', compact('invoices', 'search', 'status'));
    }

    public function showInvoice(Invoice $invoice): View
    {
        $invoice->load([
            'registration.jamaah',
            'registration.package',
            'registration.departure',
            'payments.receiver',
            'refunds',
        ]);

        $bankAccounts = BankAccount::where('is_active', true)->get();
        $setting = CompanySetting::first();

        return view('finance.invoice-detail', compact('invoice', 'bankAccounts', 'setting'));
    }

    public function payments(Request $request): View
    {
        $search = $request->input('search');
        $type = $request->input('type');
        $method = $request->input('method');

        $query = Payment::with(['invoice.registration.jamaah', 'invoice.registration.package', 'receiver']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('receipt_number', 'like', "%{$search}%")
                  ->orWhere('transaction_number', 'like', "%{$search}%")
                  ->orWhere('sender_name', 'like', "%{$search}%")
                  ->orWhereHas('invoice.registration.jamaah', fn($jq) => $jq->where('name', 'like', "%{$search}%"));
            });
        }

        if ($type) {
            $query->where('payment_type', $type);
        }

        if ($method) {
            $query->where('payment_method', $method);
        }

        $payments = $query->latest('payment_date')->paginate(12)->withQueryString();

        return view('finance.payments', compact('payments', 'search', 'type', 'method'));
    }

    public function storePayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1000', 'max:' . $invoice->remaining_amount],
            'payment_date' => ['required', 'date'],
            'payment_type' => ['required', 'in:dp,cicilan,pelunasan'],
            'payment_method' => ['required', 'in:transfer,tunai'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'sender_name' => ['nullable', 'string', 'max:255'],
            'proof_file' => ['nullable', 'image', 'mimes:jpeg,png,jpg,pdf', 'max:3072'],
            'notes' => ['nullable', 'string'],
        ], [
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.max' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan (Rp ' . number_format($invoice->remaining_amount, 0, ',', '.') . ').',
            'payment_date.required' => 'Tanggal pembayaran wajib diisi.',
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('payment_proofs', 'public');
        }

        $payment = FinanceService::recordPayment($invoice, $validated);

        return redirect()->route('finance.invoices.show', $invoice)
            ->with('toast_success', "Pembayaran sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " berhasil dicatat dengan Kwitansi {$payment->receipt_number}.");
    }

    public function cancelPayment(Request $request, Payment $payment): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ], [
            'cancellation_reason.required' => 'Alasan pembatalan pembayaran wajib disertakan.',
        ]);

        FinanceService::cancelPayment($payment, $request->cancellation_reason);

        return back()->with('toast_success', "Pembayaran {$payment->receipt_number} berhasil dibatalkan.");
    }

    public function printReceipt(Payment $payment)
    {
        $payment->load([
            'invoice.registration.jamaah',
            'invoice.registration.package',
            'receiver',
        ]);

        $setting = CompanySetting::first();

        $pdf = Pdf::loadView('finance.receipt-pdf', compact('payment', 'setting'))
            ->setPaper('a5', 'landscape');

        return $pdf->stream('Kwitansi_' . $payment->receipt_number . '.pdf');
    }

    public function expenses(Request $request): View
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');

        $query = Expense::with(['category', 'departure', 'recorder']);

        if ($search) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('expense_number', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $expenses = $query->latest('expense_date')->paginate(10)->withQueryString();
        $categories = TransactionCategory::where('type', 'pengeluaran')->get();
        $departures = Departure::where('status', '!=', 'dibatalkan')->get();

        return view('finance.expenses', compact('expenses', 'categories', 'departures', 'search', 'categoryId'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'exists:transaction_categories,id'],
            'departure_id' => ['nullable', 'exists:departures,id'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:transfer,tunai'],
            'proof_file' => ['nullable', 'file', 'mimes:jpeg,png,jpg,pdf', 'max:3072'],
            'notes' => ['nullable', 'string'],
        ], [
            'title.required' => 'Nama/keterangan pengeluaran wajib diisi.',
            'category_id.required' => 'Kategori transaksi wajib dipilih.',
            'amount.required' => 'Nominal pengeluaran wajib diisi.',
        ]);

        if ($request->hasFile('proof_file')) {
            $validated['proof_file'] = $request->file('proof_file')->store('expense_proofs', 'public');
        }

        $expense = FinanceService::recordExpense($validated);

        return back()->with('toast_success', "Pengeluaran {$expense->expense_number} sebesar Rp " . number_format($expense->amount, 0, ',', '.') . " berhasil dicatat.");
    }

    public function refunds(Request $request): View
    {
        $refunds = Refund::with(['registration.jamaah', 'registration.package', 'invoice', 'approver'])
            ->latest()
            ->paginate(10);

        $registrations = Registration::with(['jamaah', 'package', 'invoice'])
            ->whereHas('invoice', fn($q) => $q->where('paid_amount', '>', 0))
            ->get();

        return view('finance.refunds', compact('refunds', 'registrations'));
    }

    public function storeRefund(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'registration_id' => ['required', 'exists:registrations,id'],
            'amount' => ['required', 'numeric', 'min:1000'],
            'refund_date' => ['required', 'date'],
            'reason' => ['required', 'string'],
            'payment_method' => ['required', 'in:transfer,tunai'],
            'bank_name' => ['nullable', 'string'],
            'account_number' => ['nullable', 'string'],
            'account_name' => ['nullable', 'string'],
        ]);

        $reg = Registration::with('invoice')->findOrFail($validated['registration_id']);

        $yearMonth = Carbon::now()->format('Ym');
        $count = Refund::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->count() + 1;
        $validated['refund_number'] = sprintf('RFD-%s-%04d', $yearMonth, $count);
        $validated['invoice_id'] = $reg->invoice?->id;
        $validated['status'] = 'diajukan';

        $refund = Refund::create($validated);

        ActivityLogger::log('CREATE_REFUND', 'Keuangan', "Mengajukan refund {$refund->refund_number} sejumlah Rp " . number_format($refund->amount, 0, ',', '.'));

        return back()->with('toast_success', "Pengajuan refund {$refund->refund_number} berhasil disimpan.");
    }

    public function approveRefund(Request $request, Refund $refund): RedirectResponse
    {
        $status = $request->input('status', 'disetujui'); // disetujui, ditolak, selesai

        $refund->update([
            'status' => $status,
            'approved_by' => Auth::id(),
            'processed_at' => now(),
            'notes' => $request->input('notes'),
        ]);

        ActivityLogger::log('APPROVE_REFUND', 'Keuangan', "Proses refund {$refund->refund_number}: status {$status}");

        return back()->with('toast_success', "Status refund {$refund->refund_number} berhasil diperbarui.");
    }
}
