<?php

namespace App\Services\Pharmacy;

use App\Models\Patient;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\BillingService;
use Illuminate\Support\Facades\DB;

class PosService
{
    public function __construct(private readonly BillingService $billingService) {}

    public function checkout(array $cart, array $paymentData, ?int $patientId = null): array
    {
        if (empty($cart)) {
            throw new \RuntimeException('Le panier est vide.');
        }

        $patientId = $this->resolvePatientId($patientId);

        return DB::transaction(function () use ($cart, $paymentData, $patientId) {
            $invoice = $this->billingService->generateInvoice([
                'patient_id'       => $patientId,
                'items'            => $this->buildInvoiceItems($cart),
                'due_date'         => today(),
                'notes'            => $paymentData['notes'] ?? null,
                'invoiceable_type' => 'pharmacy_pos',
                'invoiceable_id'   => null,
            ]);

            $payment = $this->billingService->recordPayment($invoice, [
                'amount'         => $paymentData['amount'],
                'method'         => $paymentData['method'],
                'reference_code' => $paymentData['reference_code'] ?? null,
            ]);

            $this->deductStock($cart, $invoice->id);

            return ['invoice' => $invoice, 'payment' => $payment];
        });
    }

    private function resolvePatientId(?int $patientId): int
    {
        if ($patientId) {
            return $patientId;
        }

        $walkIn = Patient::where('patient_code', 'WALK-IN')->first();

        if (!$walkIn) {
            throw new \RuntimeException('Patient "Client de passage" introuvable. Lancez les seeders.');
        }

        return $walkIn->id;
    }

    private function buildInvoiceItems(array $cart): array
    {
        $items = [];

        foreach ($cart as $item) {
            $items[] = [
                'description'   => $item['name'] . ($item['strength'] ? " {$item['strength']}" : ''),
                'item_type'     => $item['requires_prescription'] ? 'medicine' : 'other',
                'quantity'      => $item['quantity'],
                'unit_price'    => $item['unit_price'],
                'reference_id'  => $item['medicine_id'],
            ];
        }

        return $items;
    }

    private function deductStock(array $cart, int $invoiceId): void
    {
        foreach ($cart as $item) {
            $stock = Stock::where('medicine_id', $item['medicine_id'])->lockForUpdate()->first();

            if (!$stock) {
                continue;
            }

            $stock->removeStock($item['quantity']);

            StockMovement::create([
                'stock_id'       => $stock->id,
                'medicine_id'    => $item['medicine_id'],
                'user_id'        => auth()->id(),
                'type'           => 'out',
                'quantity'       => $item['quantity'],
                'unit_cost'      => $item['unit_price'],
                'reference_type' => 'pos_sale',
                'reference_id'   => $invoiceId,
                'reason'         => 'Vente POS',
                'moved_at'       => now(),
            ]);
        }
    }
}
