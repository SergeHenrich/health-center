<?php

namespace App\Exports;

use App\Models\StockBatch;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class StockReportExport implements FromView, ShouldAutoSize
{
    protected $batches;
    protected $totals;

    public function __construct($batches, array $totals)
    {
        $this->batches = $batches;
        $this->totals = $totals;
    }

    public function view(): View
    {
        return view('exports.pharmacy.stock-report', [
            'batches' => $this->batches,
            'totals' => $this->totals,
        ]);
    }
}
