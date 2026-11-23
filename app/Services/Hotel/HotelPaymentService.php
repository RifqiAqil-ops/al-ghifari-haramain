<?php

namespace App\Services\Hotel;

use App\Models\HotelInvoice;
use App\Models\HotelPayment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HotelPaymentService
{
    protected ExchangeRateService $fxService;

    public function __construct(ExchangeRateService $fxService)
    {
        $this->fxService = $fxService;
    }

    /**
     * Record mixed-currency payment (SAR or IDR), calculate equivalence, and record FX gain/loss.
     */
    public function recordPayment(HotelInvoice $invoice, array $data): HotelPayment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $paymentCurrency = strtoupper($data['payment_currency'] ?? 'SAR');
            $paymentAmount = (float) $data['payment_amount'];

            // Determine conversion rate used for payment
            if (!empty($data['payment_exchange_rate'])) {
                $paymentRate = (float) $data['payment_exchange_rate'];
            } else {
                $latest = $this->fxService->getLatestRate('SAR', 'IDR');
                $paymentRate = (float) $latest['rate'];
            }

            $invoiceSnapshotRate = (float) $invoice->exchange_rate_snapshot;

            // Calculate Equivalence in both currencies & FX difference
            if ($paymentCurrency === 'SAR') {
                $equivalentSar = $paymentAmount;
                $equivalentIdr = round($paymentAmount * $paymentRate, 2);
                
                // FX difference between payment day rate vs invoice snapshot rate
                // If payment converted to IDR yields more than snapshot, that's a Gain
                $expectedIdrAtInvoiceRate = round($paymentAmount * $invoiceSnapshotRate, 2);
                $fxGainLossIdr = $equivalentIdr - $expectedIdrAtInvoiceRate;
            } else { // IDR payment
                $equivalentIdr = $paymentAmount;
                $equivalentSar = round($paymentAmount / max(1, $paymentRate), 2);
                
                // If customer pays in IDR, what was expected at invoice rate vs what was paid
                $expectedIdrAtInvoiceRate = round($equivalentSar * $invoiceSnapshotRate, 2);
                $fxGainLossIdr = $paymentAmount - $expectedIdrAtInvoiceRate;
            }

            $paymentNumber = 'PAY-HTL-' . date('Ym') . '-' . str_pad((HotelPayment::count() + 1), 4, '0', STR_PAD_LEFT);

            $payment = HotelPayment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->customer_id,
                'payment_date' => $data['payment_date'] ?? now()->toDateString(),
                'payment_currency' => $paymentCurrency,
                'payment_amount' => $paymentAmount,
                'payment_exchange_rate' => $paymentRate,
                'equivalent_amount_sar' => $equivalentSar,
                'equivalent_amount_idr' => $equivalentIdr,
                'fx_gain_loss_idr' => $fxGainLossIdr,
                'payment_type' => $data['payment_type'] ?? 'dp',
                'payment_method' => $data['payment_method'] ?? 'transfer',
                'bank_name' => $data['bank_name'] ?? null,
                'sender_name' => $data['sender_name'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'proof_file' => $data['proof_file'] ?? null,
                'status' => 'valid',
                'notes' => $data['notes'] ?? null,
                'recorded_by' => auth()->id(),
            ]);

            // Update Invoice Paid and Remaining balances
            $newPaidSar = (float) $invoice->paid_amount_sar + $equivalentSar;
            $newRemainingSar = max(0, (float) $invoice->total_amount_sar - $newPaidSar);
            
            $newPaidIdr = (float) $invoice->paid_amount_idr + $equivalentIdr;
            $newRemainingIdr = max(0, (float) $invoice->total_amount_idr - $newPaidIdr);

            $status = 'sebagian';
            if ($newRemainingSar <= 0.05) { // Decimal tolerance
                $status = 'lunas';
                $newRemainingSar = 0;
                $newRemainingIdr = 0;
            }

            $invoice->update([
                'paid_amount_sar' => $newPaidSar,
                'remaining_amount_sar' => $newRemainingSar,
                'paid_amount_idr' => $newPaidIdr,
                'remaining_amount_idr' => $newRemainingIdr,
                'status' => $status,
            ]);

            // Sync reservation payment status
            if ($invoice->reservation) {
                $invoice->reservation->update([
                    'payment_status' => $status === 'lunas' ? 'lunas' : 'sebagian',
                    'status' => $status === 'lunas' ? 'confirmed' : 'partially_paid',
                ]);
            }

            return $payment;
        });
    }
}
