<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\StockBatch;
use App\Models\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ExpiringBatchSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = Medicine::all();
        if ($medicines->isEmpty()) {
            $this->command->warn('Aucun médicament trouvé. Exécutez d\'abord MedicineSeeder.');
            return;
        }

        $warehouse = Warehouse::first();
        if (!$warehouse) {
            $warehouse = Warehouse::create([
                'name' => 'Dépôt principal',
                'code' => 'DP-001',
                'location' => 'Pharmacie centrale',
                'is_active' => true,
            ]);
        }

        $now = Carbon::now();

        $batchesData = [
            // Lots actifs (valides longtemps)
            ['medicine_id' => $medicines[0]->id, 'lot_number' => 'LOT-A-001', 'expiry_date' => $now->copy()->addMonths(18), 'quantity_available' => 200, 'initial_quantity' => 200, 'status' => 'active', 'unit_cost' => 2.50],
            ['medicine_id' => $medicines[1]->id, 'lot_number' => 'LOT-A-002', 'expiry_date' => $now->copy()->addMonths(24), 'quantity_available' => 150, 'initial_quantity' => 150, 'status' => 'active', 'unit_cost' => 1.80],

            // Lots expirant dans 10 jours (→ expiring soon à 30 jours)
            ['medicine_id' => $medicines[2]->id, 'lot_number' => 'LOT-B-001', 'expiry_date' => $now->copy()->addDays(10), 'quantity_available' => 80, 'initial_quantity' => 100, 'status' => 'active', 'unit_cost' => 3.00],
            ['medicine_id' => $medicines[3]->id, 'lot_number' => 'LOT-B-002', 'expiry_date' => $now->copy()->addDays(15), 'quantity_available' => 45, 'initial_quantity' => 60, 'status' => 'active', 'unit_cost' => 5.50],

            // Lot expirant dans 25 jours (toujours dans la fenêtre 30 jours)
            ['medicine_id' => $medicines[0]->id, 'lot_number' => 'LOT-B-003', 'expiry_date' => $now->copy()->addDays(25), 'quantity_available' => 120, 'initial_quantity' => 120, 'status' => 'active', 'unit_cost' => 2.50],

            // Lots déjà périmés avec stock encore disponible
            ['medicine_id' => $medicines[4]->id, 'lot_number' => 'LOT-C-001', 'expiry_date' => $now->copy()->subDays(5), 'quantity_available' => 30, 'initial_quantity' => 100, 'status' => 'active', 'unit_cost' => 1.20],
            ['medicine_id' => $medicines[5]->id, 'lot_number' => 'LOT-C-002', 'expiry_date' => $now->copy()->subDays(30), 'quantity_available' => 15, 'initial_quantity' => 80, 'status' => 'active', 'unit_cost' => 2.00],

            // Lot consommé (ne doit pas apparaître)
            ['medicine_id' => $medicines[6]->id, 'lot_number' => 'LOT-D-001', 'expiry_date' => $now->copy()->addMonths(12), 'quantity_available' => 0, 'initial_quantity' => 50, 'status' => 'depleted', 'unit_cost' => 4.00],
        ];

        foreach ($batchesData as $data) {
            StockBatch::firstOrCreate(
                ['lot_number' => $data['lot_number']],
                array_merge($data, ['warehouse_id' => $warehouse->id, 'received_at' => $now])
            );
        }

        $this->command->info('8 lots de test créés : 2 actifs, 3 proches péremption, 2 périmés, 1 épuisé.');
    }
}
