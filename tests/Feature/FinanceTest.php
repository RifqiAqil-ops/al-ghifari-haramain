<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use App\Services\FinanceService;
use Tests\TestCase;

class FinanceTest extends TestCase
{
    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::where('email', 'superadmin@alghifari.com')->first();
    }

    public function test_authenticated_user_can_view_finance_summary(): void
    {
        $response = $this->actingAs($this->user)->get(route('finance.summary'));
        $response->assertStatus(200);
        $response->assertSee('Ringkasan');
    }

    public function test_authenticated_user_can_view_invoices(): void
    {
        $response = $this->actingAs($this->user)->get(route('finance.invoices'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Tagihan');
    }

    public function test_recording_payment_updates_invoice_balance(): void
    {
        $invoice = Invoice::where('remaining_amount', '>', 1000000)->first();
        if ($invoice) {
            $prevPaid = (float) $invoice->paid_amount;
            $payAmount = 500000;

            $paymentData = [
                'amount' => $payAmount,
                'payment_date' => now()->format('Y-m-d'),
                'payment_type' => 'cicilan',
                'payment_method' => 'transfer',
                'bank_name' => 'BSI',
                'sender_name' => 'Test Penyetor',
                'notes' => 'Uji Pembayaran Otomatis',
            ];

            $payment = FinanceService::recordPayment($invoice, $paymentData, $this->user->id);

            $this->assertNotNull($payment->receipt_number);
            $this->assertEquals('valid', $payment->status);

            $invoice->refresh();
            $this->assertEquals($prevPaid + $payAmount, (float) $invoice->paid_amount);

            // Cancel the test payment to restore state cleanly
            FinanceService::cancelPayment($payment, 'Pembatalan otomatis pengetesan QA unit', $this->user->id);
            $invoice->refresh();
            $this->assertEquals($prevPaid, (float) $invoice->paid_amount);
        }
    }
}
