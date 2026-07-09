<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class BillingService
{
    /**
     * Generate a new invoice with its line items.
     */
    public function generateInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $number = $this->generateInvoiceNumber();

            $invoice = Invoice::create([
                'patient_id'       => $data['patient_id'],
                'created_by_id'    => auth()->id(),
                'invoice_number'   => $number,
                'invoice_date'     => today(),
                'due_date'         => $data['due_date'] ?? today()->addDays(30),
                'status'           => 'issued',
                'notes'            => $data['notes'] ?? null,
                'invoiceable_type' => $data['invoiceable_type'] ?? null,
                'invoiceable_id'   => $data['invoiceable_id'] ?? null,
            ]);

            $subtotal = 0;

            foreach ($data['items'] as $item) {
                $qty        = $item['quantity'] ?? 1;
                $unitPrice  = $item['unit_price'];
                $discount   = $item['discount_percent'] ?? 0;
                $lineTotal  = $qty * $unitPrice * (1 - $discount / 100);

                InvoiceItem::create([
                    'invoice_id'       => $invoice->id,
                    'description'      => $item['description'],
                    'item_type'        => $item['item_type'],
                    'reference_id'     => $item['reference_id'] ?? null,
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'discount_percent' => $discount,
                    'total_price'      => $lineTotal,
                ]);

                $subtotal += $lineTotal;
            }

            $taxRate    = (float) config('health.billing.tax_rate', 0);
            $taxAmount  = $subtotal * $taxRate / 100;
            $total      = $subtotal + $taxAmount;

            $invoice->update([
                'subtotal'     => $subtotal,
                'tax_amount'   => $taxAmount,
                'total_amount' => $total,
            ]);

            return $invoice->load('items', 'patient');
        });
    }

    /**
     * Record a payment against an invoice.
     */
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $payment = Payment::create([
                'invoice_id'     => $invoice->id,
                'received_by_id' => auth()->id(),
                'payment_number' => $this->generatePaymentNumber(),
                'amount'         => $data['amount'],
                'method'         => $data['method'],
                'reference_code' => $data['reference_code'] ?? null,
                'paid_at'        => now(),
                'notes'          => $data['notes'] ?? null,
            ]);

            $totalPaid = $invoice->payments()->sum('amount');
            $invoice->update(['amount_paid' => $totalPaid]);

            if ($totalPaid >= $invoice->total_amount) {
                $invoice->markAsPaid();
            } elseif ($totalPaid > 0) {
                $invoice->update(['status' => 'partially_paid']);
            }

            return $payment;
        });
    }

    /**
     * Generate a unique invoice number like INV-2024-00042.
     */
    private function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $base = "INV-{$year}-";
        $last = Invoice::where('invoice_number', 'like', "{$base}%")
            ->orderByDesc('invoice_number')->value('invoice_number');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $base . str_pad($next, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Generate a unique payment number.
     */
    private function generatePaymentNumber(): string
    {
        $year = now()->year;
        $base = "PAY-{$year}-";
        $last = Payment::where('payment_number', 'like', "{$base}%")
            ->orderByDesc('payment_number')->value('payment_number');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $base . str_pad($next, 5, '0', STR_PAD_LEFT);
    }
}
