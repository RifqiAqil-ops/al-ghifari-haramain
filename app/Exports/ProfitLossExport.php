<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProfitLossExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $totalIncome;
    protected $totalExpense;
    protected $netProfit;
    protected $expensesByCategory;
    protected $startDate;
    protected $endDate;
    protected $setting;

    public function __construct($totalIncome, $totalExpense, $netProfit, $expensesByCategory, $startDate, $endDate, $setting)
    {
        $this->totalIncome = $totalIncome;
        $this->totalExpense = $totalExpense;
        $this->netProfit = $netProfit;
        $this->expensesByCategory = $expensesByCategory;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->setting = $setting;
    }

    public function view(): View
    {
        return view('exports.profit-loss-excel', [
            'totalIncome' => $this->totalIncome,
            'totalExpense' => $this->totalExpense,
            'netProfit' => $this->netProfit,
            'expensesByCategory' => $this->expensesByCategory,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'setting' => $this->setting,
        ]);
    }

    public function title(): string
    {
        return 'Laba Rugi';
    }
}

