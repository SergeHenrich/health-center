<?php

namespace App\Http\Controllers\Pharmacy;

use App\Http\Controllers\Controller;
use App\Models\Medicine;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\PharmacyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PurchaseOrderController extends Controller
{
    public function __construct(private readonly PharmacyService $pharmacyService) {}

    public function index(): View
    {
        $orders = PurchaseOrder::with(['pharmacist', 'approvedBy', 'supplier'])
            ->latest('ordered_at')
            ->paginate(20);

        return view('pharmacy.orders.index', compact('orders'));
    }

    public function create(): View
    {
        $medicines = Medicine::active()->orderBy('name')->get(['id', 'name', 'code', 'unit_price']);
        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name', 'phone', 'email', 'contact_name']);
        return view('pharmacy.orders.create', compact('medicines', 'suppliers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'      => 'required|exists:suppliers,id',
            'expected_delivery'  => 'nullable|date|after:today',
            'notes'              => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.medicine_id'     => 'required|exists:medicines,id',
            'items.*.quantity_ordered'=> 'required|integer|min:1',
            'items.*.unit_cost'       => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $supplier = Supplier::findOrFail($validated['supplier_id']);
            $year  = now()->year;
            $base  = "PO-{$year}-";
            $last  = PurchaseOrder::where('order_number', 'like', "{$base}%")
                ->orderByDesc('order_number')->value('order_number');
            $next  = $last ? ((int) substr($last, -5)) + 1 : 1;

            $total = collect($validated['items'])->sum(
                fn($i) => $i['quantity_ordered'] * $i['unit_cost']
            );

            $order = PurchaseOrder::create([
                'order_number'     => $base . str_pad($next, 5, '0', STR_PAD_LEFT),
                'pharmacist_id'    => auth()->id(),
                'supplier_id'      => $supplier->id,
                'supplier_name'    => $supplier->name,
                'supplier_contact' => $supplier->phone ?? $supplier->email,
                'expected_delivery'=> $validated['expected_delivery'] ?? null,
                'notes'            => $validated['notes'] ?? null,
                'status'           => 'draft',
                'total_amount'     => $total,
                'ordered_at'       => now(),
            ]);

            foreach ($validated['items'] as $item) {
                $order->items()->create($item);
            }
        });

        return redirect()->route('purchase-orders.index')
            ->with('success', 'Bon de commande créé.');
    }

    public function show(PurchaseOrder $purchaseOrder): View
    {
        $purchaseOrder->load(['pharmacist', 'approvedBy', 'items.medicine']);
        return view('pharmacy.orders.show', compact('purchaseOrder'));
    }

    public function approve(PurchaseOrder $purchaseOrder): RedirectResponse
    {
        $purchaseOrder->approve(auth()->user());
        return back()->with('success', 'Commande approuvée.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
    {
        DB::transaction(function () use ($purchaseOrder) {
            foreach ($purchaseOrder->items as $item) {
                $this->pharmacyService->receiveStock(
                    $item->medicine_id,
                    $item->quantity_ordered,
                    (float) $item->unit_cost,
                    $item->lot_number,
                    $item->expiry_date,
                    $item->id
                );
                $item->update(['quantity_received' => $item->quantity_ordered]);
            }
            $purchaseOrder->receive();
        });

        return back()->with('success', 'Réception de commande enregistrée et stock mis à jour.');
    }
}
