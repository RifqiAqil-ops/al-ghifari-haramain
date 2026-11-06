<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithTitle;

class JamaahExport implements FromView, ShouldAutoSize, WithTitle
{
    protected $jamaahs;
    protected $setting;

    public function __construct($jamaahs, $setting)
    {
        $this->jamaahs = $jamaahs;
        $this->setting = $setting;
    }

    public function view(): View
    {
        return view('exports.jamaah-excel', [
            'jamaahs' => $this->jamaahs,
            'setting' => $this->setting,
        ]);
    }

    public function title(): string
    {
        return 'Data Jamaah';
    }
}

