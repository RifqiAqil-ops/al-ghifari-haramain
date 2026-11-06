<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class DeparturesExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $departures;
    protected $year;
    protected $setting;

    public function __construct($departures, $year, $setting)
    {
        $this->departures = $departures;
        $this->year = $year;
        $this->setting = $setting;
    }

    public function view(): View
    {
        return view('exports.departures-excel', [
            'departures' => $this->departures,
            'year' => $this->year,
            'setting' => $this->setting,
        ]);
    }

    public function title(): string
    {
        return 'Jadwal Keberangkatan';
    }
}

