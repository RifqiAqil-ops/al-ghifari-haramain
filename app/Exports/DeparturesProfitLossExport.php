<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DeparturesProfitLossExport implements FromView, ShouldAutoSize, WithTitle
{
    protected array $report;
    protected $setting;

    public function __construct(array $report, $setting)
    {
        $this->report = $report;
        $this->setting = $setting;
    }

    public function view(): View
    {
        return view('exports.departures-profit-loss-excel', [
            'report'  => $this->report,
            'setting' => $this->setting,
        ]);
    }

    public function title(): string
    {
        return 'Laba Rugi per Kloter';
    }
}
