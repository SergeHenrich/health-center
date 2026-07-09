<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Patient;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(private readonly BillingService $billingService) {}

    public function index(Request $request): View
    {
        $invoices = Invoice::with(['patient', 'createdBy'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->q, fn($q) => $q->whereHas('patient', fn($p) =>
                $p->where('first_name', 'ilike', "%{$request->q}%")
                  ->orWhere('last_name', 'ilike', "%{$request->q}%")
            ))
            ->latest('invoice_date')
            ->paginate(20);

        return view('billing.invoices.index', compact('invoices'));
    }

    public function create(): View
    {
        $patients = Patient::active()->orderBy('last_name')->get(['id', 'first_name', 'last_name', 'patient_code']);
        return view('billing.invoices.create', compact('patients'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'patient_id'       => 'required|exists:patients,id',
            'due_date'         => 'nullable|date|after:today',
            'notes'            => 'nullable|string|max:1000',
            'invoiceable_type' => 'nullable|string',
            'invoiceable_id'   => 'nullable|integer',
            'items'            => 'required|array|min:1',
            'items.*.description'    => 'required|string|max:255',
            'items.*.item_type'      => 'required|in:consultation,lab_exam,medicine,hospitalization,procedure,other',
            'items.*.quantity'       => 'required|numeric|min:0.01',
            'items.*.unit_price'     => 'required|numeric|min:0',
            'items.*.discount_percent' => 'nullable|numeric|min:0|max:100',
        ], [
            'items.required'                  => 'Ajoutez au moins une ligne à la facture.',
            'items.*.description.required'    => 'La description est obligatoire.',
            'items.*.unit_price.required'     => 'Le prix unitaire est obligatoire.',
        ]);

        $invoice = $this->billingService->generateInvoice($validated);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Facture {$invoice->invoice_number} créée.");
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['patient', 'items', 'payments.receivedBy', 'createdBy']);
        return view('billing.invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load(['patient', 'items', 'payments']);
        return view('billing.invoices.print', compact('invoice'));
    }

    public function cancel(Invoice $invoice): RedirectResponse
    {
        $invoice->cancel();
        return back()->with('success', 'Facture annulée.');
    }
}
