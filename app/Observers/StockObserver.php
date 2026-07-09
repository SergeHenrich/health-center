<?php

namespace App\Observers;

use App\Models\Notification;
use App\Models\Stock;
use App\Models\User;

class StockObserver
{
    public function updated(Stock $stock): void
    {
        if ($stock->isLow()) {
            $pharmacists = User::role('pharmacist')->active()->get();

            $notification = Notification::create([
                'sender_id'    => null,
                'title'        => '⚠️ Stock bas : ' . $stock->medicine->name,
                'body'         => "Le stock de {$stock->medicine->name} est bas ({$stock->quantity_available} unités restantes, minimum : {$stock->minimum_quantity}).",
                'type'         => 'warning',
                'channel'      => 'in_app',
                'is_broadcast' => false,
            ]);

            $notification->users()->attach($pharmacists->pluck('id'));
        }
    }
}
