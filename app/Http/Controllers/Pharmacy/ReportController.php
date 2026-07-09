<?php

namespace App\Http\Controllers\Pharmacy;

use App\Exports\ConsumptionReportExport;
use App\Exports\StockReportExport;
use App\Http\Controllers\Controller;
use App\Services\Pharmacy\BatchService;
use App\Services\Pharmacy\KpiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private readonly KpiService $kpiService,
        private readonly BatchService $batchService,
    ) {}

    public function stockReport(Request $request): View
    {
        $filters = $request->only(['medicine_id', 'warehouse_id', 'status', 'expiring']);
        $batches = $this->batchService->getBatches($filters);

        $totals = [
            'batches' => $batches->count(),
            'total_available' => $batches->sum('quantity_available'),
            'total_initial' => $batches->sum('initial_quantity'),
            'expired_count' => $batches->filter(fn($b) => $b->isExpired())->count(),
        ];

        return view('pharmacy.reports.stock', compact('batches', 'totals'));
    }

    public function consumptionReport(Request $request): View
    {
        $period = $request->input('period', 'month');
        $raw = $this->kpiService->chartData();

        $labels = array_keys($raw['dispensations'] ?? []);
        $dispensed = array_values($raw['dispensations'] ?? []);

        $chartData = compact('labels', 'dispensed');

        return view('pharmacy.reports.consumption', compact('chartData', 'period'));
    }

    public function stockReportPdf(Request $request)
    {
        $filters = $request->only(['medicine_id', 'warehouse_id', 'status', 'expiring']);
        $batches = $this->batchService->getBatches($filters);

        $totals = [
            'batches' => $batches->count(),
            'total_available' => $batches->sum('quantity_available'),
            'total_initial' => $batches->sum('initial_quantity'),
            'expired_count' => $batches->filter(fn($b) => $b->isExpired())->count(),
        ];

        $pdf = Pdf::loadView('pdf.pharmacy.stock-report', compact('batches', 'totals'));
        return $pdf->download('rapport-stocks-' . now()->format('Y-m-d') . '.pdf');
    }

    public function stockReportExcel(Request $request)
    {
        $filters = $request->only(['medicine_id', 'warehouse_id', 'status', 'expiring']);
        $batches = $this->batchService->getBatches($filters);

        $totals = [
            'batches' => $batches->count(),
            'total_available' => $batches->sum('quantity_available'),
            'total_initial' => $batches->sum('initial_quantity'),
            'expired_count' => $batches->filter(fn($b) => $b->isExpired())->count(),
        ];

        return Excel::download(
            new StockReportExport($batches, $totals),
            'rapport-stocks-' . now()->format('Y-m-d') . '.xlsx'
        );
    }

    public function consumptionReportPdf(Request $request)
    {
        $raw = $this->kpiService->chartData();

        $labels = array_keys($raw['dispensations'] ?? []);
        $dispensed = array_values($raw['dispensations'] ?? []);
        $chartData = compact('labels', 'dispensed');

        $pdf = Pdf::loadView('pdf.pharmacy.consumption-report', compact('chartData'));
        return $pdf->download('rapport-consommation-' . now()->format('Y-m-d') . '.pdf');
    }

    public function consumptionReportExcel(Request $request)
    {
        $raw = $this->kpiService->chartData();

        $labels = array_keys($raw['dispensations'] ?? []);
        $dispensed = array_values($raw['dispensations'] ?? []);
        $chartData = compact('labels', 'dispensed');

        return Excel::download(
            new ConsumptionReportExport($chartData),
            'rapport-consommation-' . now()->format('Y-m-d') . '.xlsx'
        );
    }
}
