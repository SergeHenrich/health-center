<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billing\StorePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Services\BillingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function __construct(private readonly BillingService $billingService) {}

    public function index(): View
    {
        $payments = Payment::with(['invoice.patient', 'receivedBy'])
            ->latest('paid_at')
            ->paginate(25);

        $todayTotal = Payment::today()->sum('amount');

        return view('billing.payments.index', compact('payments', 'todayTotal'));
    }

    public function store(StorePaymentRequest $request): RedirectResponse
    {
        $invoice = Invoice::findOrFail($request->invoice_id);
        $payment = $this->billingService->recordPayment($invoice, $request->validated());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Paiement de " . number_format($payment->amount, 0, ',', ' ') . " XAF enregistré.");
    }
}
