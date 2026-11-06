<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ReceivablesExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $invoices;
    protected $totalReceivable;
    protected $setting;

    public function __construct($invoices, $totalReceivable, $setting)
    {
        $this->invoices = $invoices;
        $this->totalReceivable = $totalReceivable;
        $this->setting = $setting;
    }

    public function view(): View
    {
        return view('exports.receivables-excel', [
            'invoices' => $this->invoices,
            'totalReceivable' => $this->totalReceivable,
            'setting' => $this->setting,
        ]);
    }

    public function title(): string
    {
        return 'Piutang Jamaah';
    }
}

