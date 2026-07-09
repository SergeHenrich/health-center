<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Services\Pharmacy\KpiService;
use Illuminate\View\View;

class KpiController extends Controller
{
    public function __construct(private readonly KpiService $kpiService) {}

    public function index(): View
    {
        $data = $this->kpiService->getDashboardData();
        return view('pharmacy.kpi.index', compact('data'));
    }
}
