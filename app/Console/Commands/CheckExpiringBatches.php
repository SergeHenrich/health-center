<?php

namespace App\Console\Commands;

use App\Models\Notification;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Console\Command;

class CheckExpiringBatches extends Command
{
    protected $signature = 'pharmacy:check-expiring-batches
        {--days=30 : Nombre de jours avant péremption pour alerter}';

    protected $description = 'Vérifie les lots proches de péremption et notifie les pharmaciens';

    public function handle(): int
    {
        $days = (int) $this->option('days');

        $expiringSoon = StockBatch::expiringSoon($days)->with('medicine')->get();
        $alreadyExpired = StockBatch::expired()->with('medicine')->get();

        if ($expiringSoon->isEmpty() && $alreadyExpired->isEmpty()) {
            $this->info("Aucun lot proche de péremption ou périmé trouvé.");
            return self::SUCCESS;
        }

        $lines = [];

        if ($expiringSoon->isNotEmpty()) {
            $lines[] = "=== Lots expirant dans moins de {$days} jours ===";
            foreach ($expiringSoon as $batch) {
                $lines[] = "  - {$batch->medicine->name} (lot {$batch->lot_number}) expire le {$batch->expiry_date->format('d/m/Y')} (dans {$batch->expiry_date->diffInDays(now())} jours)";
            }
        }

        if ($alreadyExpired->isNotEmpty()) {
            $lines[] = "=== Lots périmés encore en stock ===";
            foreach ($alreadyExpired as $batch) {
                $lines[] = "  - {$batch->medicine->name} (lot {$batch->lot_number}) périmé depuis le {$batch->expiry_date->format('d/m/Y')} — {$batch->quantity_available} unités encore en stock";
            }
        }

        $this->info(implode("\n", $lines));

        $pharmacistUserIds = User::role('pharmacist')->pluck('id');

        if ($pharmacistUserIds->isEmpty()) {
            $this->warn("Aucun pharmacien trouvé pour recevoir les notifications.");
            return self::SUCCESS;
        }

        if ($expiringSoon->isNotEmpty()) {
            $notification = Notification::create([
                'sender_id' => null,
                'title' => "Lots proches de péremption ({$expiringSoon->count()} lots)",
                'body' => "{$expiringSoon->count()} lots expirent dans moins de {$days} jours. Consultez le rapport des stocks.",
                'type' => 'expiring_batches',
                'channel' => 'in_app',
                'is_broadcast' => true,
            ]);
            $notification->users()->attach($pharmacistUserIds);
            $this->info("Notification envoyée à {$pharmacistUserIds->count()} pharmacien(s).");
        }

        if ($alreadyExpired->isNotEmpty()) {
            $notification = Notification::create([
                'sender_id' => null,
                'title' => "Lots périmés en stock ({$alreadyExpired->count()} lots)",
                'body' => "{$alreadyExpired->count()} lots sont périmés avec du stock encore disponible. Procédez à leur mise au rebut.",
                'type' => 'expired_batches',
                'channel' => 'in_app',
                'is_broadcast' => true,
            ]);
            $notification->users()->attach($pharmacistUserIds);
            $this->info("Notification périmée envoyée à {$pharmacistUserIds->count()} pharmacien(s).");
        }

        return self::SUCCESS;
    }
}
