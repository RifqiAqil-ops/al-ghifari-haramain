<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Registration;
use App\Models\Expense;
use App\Models\Refund;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinanceService
{
    /**
     * Generate a new invoice for a registration.
     */
    public static function createInvoiceForRegistration(Registration $registration): Invoice
    {
        $yearMonth = Carbon::now()->format('Ym');
        $count = Invoice::count() + 1;
        do {
            $invoiceNumber = sprintf('INV-%s-%04d', $yearMonth, $count);
            $exists = Invoice::where('invoice_number', $invoiceNumber)->exists();
            if ($exists) {
                $count++;
            }
        } while ($exists);

        return Invoice::create([
            'invoice_number' => $invoiceNumber,
            'registration_id' => $registration->id,
            'total_amount' => $registration->final_price,
            'paid_amount' => 0,
            'remaining_amount' => $registration->final_price,
            'status' => 'belum_bayar',
            'due_date' => Carbon::now()->addDays(14),
        ]);
    }

    /**
     * Record a new payment with database transaction.
     */
    public static function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $yearMonth = Carbon::now()->format('Ym');
            $pCount = Payment::count() + 1;
            do {
                $trxNumber = sprintf('TRX-%s-%04d', $yearMonth, $pCount);
                $receiptNumber = sprintf('KWT-%s-%04d', $yearMonth, $pCount);
                $exists = Payment::where('transaction_number', $trxNumber)->orWhere('receipt_number', $receiptNumber)->exists();
                if ($exists) {
                    $pCount++;
                }
            } while ($exists);

            $payment = Payment::create([
                'invoice_id' => $invoice->id,
                'transaction_number' => $trxNumber,
                'receipt_number' => $receiptNumber,
                'payment_date' => $data['payment_date'] ?? Carbon::now()->format('Y-m-d'),
                'amount' => $data['amount'],
                'payment_type' => $data['payment_type'] ?? 'cicilan',
                'payment_method' => $data['payment_method'] ?? 'transfer',
                'bank_name' => $data['bank_name'] ?? null,
                'sender_name' => $data['sender_name'] ?? null,
                'proof_file' => $data['proof_file'] ?? null,
                'status' => 'valid',
                'notes' => $data['notes'] ?? null,
                'received_by' => Auth::id(),
            ]);

            // Recalculate invoice
            $invoice->recalculateFinancials();

            // Log activity
            ActivityLogger::log(
                'PAYMENT',
                'Keuangan',
                "Mencatat pembayaran Rp " . number_format($payment->amount, 0, ',', '.') . " untuk Invoice {$invoice->invoice_number} (Kwitansi: {$receiptNumber})",
                null,
                $payment->toArray()
            );

            return $payment;
        });
    }

    /**
     * Cancel/Reverse an existing payment with audit log.
     */
    public static function cancelPayment(Payment $payment, string $reason): Payment
    {
        return DB::transaction(function () use ($payment, $reason) {
            $oldData = $payment->toArray();

            $payment->update([
                'status' => 'dibatalkan',
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
                'cancelled_by' => Auth::id(),
            ]);

            // Recalculate invoice
            $invoice = $payment->invoice;
            $invoice->recalculateFinancials();

            ActivityLogger::log(
                'REVERSAL',
                'Keuangan',
                "Membatalkan pembayaran Kwitansi {$payment->receipt_number} sejumlah Rp " . number_format($payment->amount, 0, ',', '.') . ". Alasan: {$reason}",
                $oldData,
                $payment->toArray()
            );

            return $payment;
        });
    }

    /**
     * Record an operational expense.
     */
    public static function recordExpense(array $data): Expense
    {
        $yearMonth = Carbon::now()->format('Ym');
        $count = Expense::count() + 1;
        do {
            $expenseNumber = sprintf('EXP-%s-%04d', $yearMonth, $count);
            $exists = Expense::where('expense_number', $expenseNumber)->exists();
            if ($exists) {
                $count++;
            }
        } while ($exists);

        $expense = Expense::create([
            'expense_number' => $expenseNumber,
            'category_id' => $data['category_id'] ?? null,
            'departure_id' => $data['departure_id'] ?? null,
            'title' => $data['title'],
            'expense_date' => $data['expense_date'] ?? Carbon::now()->format('Y-m-d'),
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'] ?? 'transfer',
            'proof_file' => $data['proof_file'] ?? null,
            'notes' => $data['notes'] ?? null,
            'recorded_by' => Auth::id(),
        ]);

        ActivityLogger::log(
            'EXPENSE',
            'Keuangan',
            "Mencatat pengeluaran operasional '{$expense->title}' sejumlah Rp " . number_format($expense->amount, 0, ',', '.'),
            null,
            $expense->toArray()
        );

        return $expense;
    }
}
