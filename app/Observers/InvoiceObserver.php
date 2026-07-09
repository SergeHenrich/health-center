<?php

namespace App\Observers;

use App\Models\Invoice;

class InvoiceObserver
{
    public function saving(Invoice $invoice): void
    {
        // Auto-set status to overdue if due date has passed
        if (
            $invoice->due_date
            && $invoice->due_date->isPast()
            && !in_array($invoice->status, ['paid', 'cancelled', 'refunded'])
        ) {
            $invoice->status = 'overdue';
        }
    }
}
