<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class PaymentsExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $payments;
    protected $totalAmount;
    protected $startDate;
    protected $endDate;
    protected $setting;

    public function __construct($payments, $totalAmount, $startDate, $endDate, $setting)
    {
        $this->payments = $payments;
        $this->totalAmount = $totalAmount;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->setting = $setting;
    }

    public function view(): View
    {
        return view('exports.payments-excel', [
            'payments' => $this->payments,
            'totalAmount' => $this->totalAmount,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'setting' => $this->setting,
        ]);
    }

    public function title(): string
    {
        return 'Kas Pembayaran';
    }
}

