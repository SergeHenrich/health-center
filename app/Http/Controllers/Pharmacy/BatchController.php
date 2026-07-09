<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\StockBatch;
use App\Services\Pharmacy\BatchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BatchController extends Controller
{
    public function __construct(private readonly BatchService $batchService) {}

    public function index(Request $request): View
    {
        $batches = $this->batchService->getBatches($request->only(['medicine_id', 'warehouse_id', 'status', 'expiring', 'expired']));
        return view('pharmacy.batches.index', compact('batches'));
    }

    public function show(StockBatch $stockBatch): View
    {
        $stockBatch->load(['medicine', 'warehouse', 'stockMovements' => fn($q) => $q->latest('moved_at')->limit(20)]);
        return view('pharmacy.batches.show', compact('stockBatch'));
    }

    public function writeOff(Request $request, StockBatch $stockBatch): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $stockBatch->quantity_available,
            'reason' => 'required|in:expired,damaged,theft,other',
        ]);

        $this->batchService->writeOff($stockBatch, $validated['quantity'], $validated['reason']);

        return redirect()->route('batches.show', $stockBatch)
            ->with('success', 'Mise au rebut enregistrée.');
    }

    public function returnToSupplier(Request $request, StockBatch $stockBatch): RedirectResponse
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:1|max:' . $stockBatch->quantity_available,
        ]);

        $this->batchService->returnToSupplier($stockBatch, $validated['quantity']);

        return redirect()->route('batches.show', $stockBatch)
            ->with('success', 'Retour fournisseur enregistré.');
    }
}
