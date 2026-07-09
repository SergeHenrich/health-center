<?php

namespace App\Services\Pharmacy;

use App\Models\Dispensation;
use App\Models\Medicine;
use App\Models\MedicationEvent;
use App\Models\NarcoticRegister;
use App\Models\PharmaceuticalValidation;
use App\Models\Prescription;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\WarehouseStock;
use Illuminate\Support\Facades\DB;

class KpiService
{
    public function getDashboardData(): array
    {
        return [
            'dispensation' => $this->dispensationStats(),
            'stock' => $this->stockStats(),
            'quality' => $this->qualityStats(),
            'narcotic' => $this->narcoticStats(),
            'formulary' => $this->formularyStats(),
            'events' => $this->eventStats(),
            'charts' => $this->chartData(),
        ];
    }

    private function dispensationStats(): array
    {
        $totalDispensations = Dispensation::count();
        $monthlyDispensations = Dispensation::whereMonth('dispensed_at', now()->month)
            ->whereYear('dispensed_at', now()->year)
            ->count();

        $pendingPrescriptions = Prescription::whereIn('status', ['pending', 'validated_with_interventions'])->count();

        return [
            'total' => $totalDispensations,
            'monthly' => $monthlyDispensations,
            'pending' => $pendingPrescriptions,
            'daily_avg' => $this->dailyAverage(),
        ];
    }

    private function dailyAverage(): float
    {
        $days = Dispensation::selectRaw('DATE(dispensed_at) as day')
            ->distinct()
            ->count();

        if ($days === 0) {
            return 0;
        }

        return round(Dispensation::count() / $days, 1);
    }

    private function stockStats(): array
    {
        $totalMedicines = Medicine::active()->count();
        $lowStock = Stock::lowStock()->count();
        $outOfStock = Stock::where('quantity_available', 0)->count();
        $warehouseItems = WarehouseStock::sum('quantity_available');
        $stockValue = Stock::join('medicines', 'stocks.medicine_id', '=', 'medicines.id')
            ->select(DB::raw('SUM(stocks.quantity_available * medicines.unit_price) as total'))
            ->value('total') ?? 0;

        return [
            'total_medicines' => $totalMedicines,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'warehouse_items' => $warehouseItems,
            'stock_value' => round($stockValue, 2),
            'stock_health' => $totalMedicines > 0
                ? round((($totalMedicines - $lowStock) / $totalMedicines) * 100, 1)
                : 0,
        ];
    }

    private function qualityStats(): array
    {
        $totalValidations = PharmaceuticalValidation::count();
        $approvedValidations = PharmaceuticalValidation::where('status', 'approved')->count();
        $interventionCount = PharmaceuticalValidation::withCount('interventions')
            ->get()
            ->sum('interventions_count');

        $validationRate = $totalValidations > 0
            ? round(($approvedValidations / $totalValidations) * 100, 1)
            : 0;

        return [
            'total_validations' => $totalValidations,
            'approved' => $approvedValidations,
            'interventions' => $interventionCount,
            'validation_rate' => $validationRate,
        ];
    }

    private function narcoticStats(): array
    {
        $totalEntries = NarcoticRegister::count();
        $narcoticMedicines = Medicine::narcotic()->active()->count();
        $totalBalance = NarcoticRegister::select(DB::raw('SUM(quantity_in) - SUM(quantity_out) as balance'))
            ->value('balance') ?? 0;

        return [
            'total_entries' => $totalEntries,
            'narcotic_medicines' => $narcoticMedicines,
            'total_balance' => $totalBalance,
        ];
    }

    private function formularyStats(): array
    {
        $totalMedicines = Medicine::count();
        $onFormulary = Medicine::onFormulary()->count();
        $offFormulary = Medicine::where('formulary_status', 'non_inscrit')->count();

        $formularyRate = $totalMedicines > 0
            ? round(($onFormulary / $totalMedicines) * 100, 1)
            : 0;

        return [
            'on_formulary' => $onFormulary,
            'off_formulary' => $offFormulary,
            'coverage_rate' => $formularyRate,
        ];
    }

    private function eventStats(): array
    {
        $total = MedicationEvent::count();
        $open = MedicationEvent::where('status', 'open')->count();
        $critical = MedicationEvent::where('severity', 'critical')->where('status', '!=', 'resolved')->count();

        $byType = MedicationEvent::select('type', DB::raw('count(*) as count'))
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();

        return [
            'total' => $total,
            'open' => $open,
            'critical' => $critical,
            'by_type' => $byType,
        ];
    }

    public function chartData(): array
    {
        $dateFormat = DB::connection()->getDriverName() === 'pgsql'
            ? "TO_CHAR(%s, 'YYYY-MM')"
            : "strftime('%%Y-%%m', %s)";

        $monthlyDispensations = Dispensation::selectRaw(sprintf($dateFormat, 'dispensed_at') . " as month, count(*) as total")
            ->where('dispensed_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $monthlyEvents = MedicationEvent::selectRaw(sprintf($dateFormat, 'occurred_at') . " as month, count(*) as total")
            ->where('occurred_at', '>=', now()->subMonths(6))
            ->groupBy('month')
            ->orderBy('month')
            ->pluck('total', 'month')
            ->toArray();

        return [
            'dispensations' => $monthlyDispensations,
            'events' => $monthlyEvents,
        ];
    }
}
