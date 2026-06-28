<?php

namespace Database\Seeders;

use App\Models\Room;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            MedicineSeeder::class,
            LabExamSeeder::class,
            PharmacyDemoSeeder::class,
        ]);

        $this->seedRooms();
        $this->seedSettings();

        // 50 fake patients with medical records
        \App\Models\Patient::factory(50)
            ->create()
            ->each(function ($patient) {
                \App\Models\MedicalRecord::factory()->create(['patient_id' => $patient->id]);
            });

        // Walk-in patient for POS sales without registration
        \App\Models\Patient::firstOrCreate(
            ['patient_code' => 'WALK-IN'],
            [
                'first_name' => 'Client',
                'last_name'  => 'de passage',
                'gender'     => 'male',
                'is_active'  => true,
            ]
        );
    }

    private function seedRooms(): void
    {
        $rooms = [
            ['room_number' => '101', 'name' => 'Chambre Standard A', 'type' => 'standard', 'floor' => 'RDC', 'capacity' => 2],
            ['room_number' => '102', 'name' => 'Chambre Standard B', 'type' => 'standard', 'floor' => 'RDC', 'capacity' => 2],
            ['room_number' => '201', 'name' => 'Maternité 1', 'type' => 'maternity', 'floor' => '1er étage', 'capacity' => 1],
            ['room_number' => '202', 'name' => 'Maternité 2', 'type' => 'maternity', 'floor' => '1er étage', 'capacity' => 1],
            ['room_number' => 'USI-1', 'name' => 'Unité de soins intensifs', 'type' => 'intensive_care', 'floor' => '2e étage', 'capacity' => 4],
            ['room_number' => 'URG-1', 'name' => 'Urgences', 'type' => 'emergency', 'floor' => 'RDC', 'capacity' => 3],
        ];

        foreach ($rooms as $data) {
            $room = Room::firstOrCreate(['room_number' => $data['room_number']], $data);

            if ($room->beds()->count() === 0) {
                for ($i = 1; $i <= $data['capacity']; $i++) {
                    $room->beds()->create([
                        'bed_number' => $data['room_number'] . '-' . str_pad($i, 2, '0', STR_PAD_LEFT),
                        'type'       => $data['type'] === 'maternity' ? 'maternity' : 'standard',
                        'status'     => 'available',
                    ]);
                }
            }
        }
    }

    private function seedSettings(): void
    {
        $settings = [
            ['key' => 'app.name', 'value' => 'HealthCenter', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.currency', 'value' => 'XAF', 'type' => 'string', 'group' => 'general'],
            ['key' => 'app.timezone', 'value' => 'Africa/Douala', 'type' => 'string', 'group' => 'general'],
            ['key' => 'billing.tax_rate', 'value' => '0', 'type' => 'integer', 'group' => 'billing'],
            ['key' => 'billing.invoice_prefix', 'value' => 'INV-', 'type' => 'string', 'group' => 'billing'],
            ['key' => 'lab.request_prefix', 'value' => 'LAB-', 'type' => 'string', 'group' => 'lab'],
            ['key' => 'pharmacy.low_stock_threshold', 'value' => '10', 'type' => 'integer', 'group' => 'pharmacy'],
            ['key' => 'appointment.default_duration', 'value' => '30', 'type' => 'integer', 'group' => 'appointments'],
        ];

        foreach ($settings as $s) {
            Setting::firstOrCreate(['key' => $s['key']], $s);
        }
    }
}
