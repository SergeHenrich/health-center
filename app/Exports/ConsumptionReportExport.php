<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ConsumptionReportExport implements FromView, ShouldAutoSize
{
    protected $chartData;

    public function __construct(array $chartData)
    {
        $this->chartData = $chartData;
    }

    public function view(): View
    {
        return view('exports.pharmacy.consumption-report', [
            'chartData' => $this->chartData,
        ]);
    }
}
