<?php

namespace Database\Seeders;

use App\Models\{
    Consultation, Dispensation, DispensationItem, MedicalRecord,
    Medicine, Patient, PharmaceuticalValidation, Prescription,
    PrescriptionItem, PurchaseOrder, PurchaseOrderItem,
    Stock, StockBatch, Supplier, User,
};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyFlowSeeder extends Seeder
{
    public function run(): void
    {
        // ── Lookup existing data ────────────────────────────────
        $doctor   = User::role('general_practitioner')->first() ?? User::factory()->create(['username' => 'flow_doctor'])->assignRole('general_practitioner');
        $pharmacist = User::role('pharmacist')->first() ?? User::factory()->create(['username' => 'flow_pharmacist'])->assignRole('pharmacist');
        $patients = Patient::take(4)->get();
        if ($patients->count() < 4) {
            $patients = Patient::factory(4 - $patients->count())->create()->merge($patients);
        }

        $supplier = Supplier::first();
        if (!$supplier) {
            $supplier = Supplier::create([
                'code' => 'SUP-001', 'name' => 'PharmaDistri SA',
                'contact_name' => 'Marc Tchinda', 'email' => 'contact@pharmadistri.cm',
                'phone' => '677000001', 'is_active' => true,
            ]);
        }

        // ── Helper to find/create StockBatch ────────────────────
        $ensureBatch = function (Medicine $medicine, int $qty, string $lot, int $expDays = 365) {
            return StockBatch::firstOrCreate(
                ['lot_number' => $lot],
                [
                    'medicine_id' => $medicine->id,
                    'warehouse_id' => null,
                    'expiry_date' => Carbon::now()->addDays($expDays),
                    'quantity_available' => $qty,
                    'initial_quantity' => $qty,
                    'status' => 'active',
                    'unit_cost' => $medicine->unit_price,
                    'received_at' => now(),
                ]
            );
        };

        // ── Helper to find/create Consultation ──────────────────
        $ensureConsultation = function (Patient $patient) use ($doctor) {
            $record = $patient->medicalRecord ?? MedicalRecord::factory()->create(['patient_id' => $patient->id]);
            return Consultation::firstOrCreate(
                ['medical_record_id' => $record->id, 'doctor_id' => $doctor->id],
                [
                    'consultation_date' => now()->subDays(rand(1, 10)),
                    'chief_complaint' => 'Consultation de suivi',
                    'status' => 'completed',
                ]
            );
        };

        DB::beginTransaction();
        try {

            // ══════════════════════════════════════════════════════
            // PRESCRIPTION 1 : En attente, non validée
            // ══════════════════════════════════════════════════════
            $p1_med1 = Medicine::where('code', 'MED-007')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-007', 'name' => 'Salbutamol', 'unit_price' => 3500]));
            $p1_med2 = Medicine::where('code', 'MED-009')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-009', 'name' => 'Diclofénac', 'unit_price' => 1200]));

            $c1 = $ensureConsultation($patients[0]);
            $rx1 = Prescription::create([
                'consultation_id' => $c1->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patients[0]->id,
                'prescription_number' => 'RX-2026-0001',
                'issued_at' => now()->subDays(2),
                'valid_until' => now()->addDays(28),
                'status' => 'pending',
                'notes' => 'Patient asthmatique, surveillance requise.',
            ]);
            PrescriptionItem::create(['prescription_id' => $rx1->id, 'medicine_id' => $p1_med1->id, 'medicine_name' => $p1_med1->name, 'dosage' => '100mcg', 'frequency' => '2x/jour', 'duration' => '7 jours', 'quantity_prescribed' => 1, 'quantity_dispensed' => 0, 'route' => 'inhalation', 'instructions' => '1 bouffée matin et soir']);
            PrescriptionItem::create(['prescription_id' => $rx1->id, 'medicine_id' => $p1_med2->id, 'medicine_name' => $p1_med2->name, 'dosage' => '1%', 'frequency' => '3x/jour', 'duration' => '5 jours', 'quantity_prescribed' => 1, 'quantity_dispensed' => 0, 'route' => 'topique', 'instructions' => 'Appliquer localement 3 fois par jour']);

            // ══════════════════════════════════════════════════════
            // PRESCRIPTION 2 : Validée, prête à dispenser
            // ══════════════════════════════════════════════════════
            $p2_med1 = Medicine::where('code', 'MED-002')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-002', 'name' => 'Amoxicilline', 'unit_price' => 150]));
            $p2_med2 = Medicine::where('code', 'MED-011')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-011', 'name' => 'Vitamine C', 'unit_price' => 60]));

            $ensureBatch($p2_med1, 200, 'AMX-2026-A01');
            $ensureBatch($p2_med2, 500, 'VITC-2026-B01');

            $c2 = $ensureConsultation($patients[1]);
            $rx2 = Prescription::create([
                'consultation_id' => $c2->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patients[1]->id,
                'prescription_number' => 'RX-2026-0002',
                'issued_at' => now()->subDay(),
                'valid_until' => now()->addDays(30),
                'status' => 'validated',
                'notes' => 'Infection respiratoire. Traitement de 7 jours.',
            ]);
            PrescriptionItem::create(['prescription_id' => $rx2->id, 'medicine_id' => $p2_med1->id, 'medicine_name' => $p2_med1->name, 'dosage' => '500mg', 'frequency' => '3x/jour', 'duration' => '7 jours', 'quantity_prescribed' => 21, 'quantity_dispensed' => 0, 'route' => 'oral', 'instructions' => '1 gélule 3 fois par jour']);
            PrescriptionItem::create(['prescription_id' => $rx2->id, 'medicine_id' => $p2_med2->id, 'medicine_name' => $p2_med2->name, 'dosage' => '1000mg', 'frequency' => '1x/jour', 'duration' => '30 jours', 'quantity_prescribed' => 30, 'quantity_dispensed' => 0, 'route' => 'oral', 'instructions' => '1 comprimé le matin']);

            PharmaceuticalValidation::create([
                'prescription_id' => $rx2->id,
                'pharmacist_id' => $pharmacist->id,
                'validated_at' => now()->subHours(6),
                'status' => 'approved',
                'notes' => 'Prescription conforme au livret. AVC OK.',
            ]);

            // ══════════════════════════════════════════════════════
            // PRESCRIPTION 3 : Partiellement dispensée
            // ══════════════════════════════════════════════════════
            $p3_med1 = Medicine::where('code', 'MED-004')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-004', 'name' => 'Artéméther/Luméfantrine', 'unit_price' => 2500]));
            $p3_med2 = Medicine::where('code', 'MED-005')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-005', 'name' => 'Métronidazole', 'unit_price' => 100]));

            $batch3a = $ensureBatch($p3_med1, 100, 'ART-2026-C01');
            $batch3b = $ensureBatch($p3_med2, 200, 'METRO-2026-D01');

            $c3 = $ensureConsultation($patients[2]);
            $rx3 = Prescription::create([
                'consultation_id' => $c3->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patients[2]->id,
                'prescription_number' => 'RX-2026-0003',
                'issued_at' => now()->subDays(3),
                'valid_until' => now()->addDays(27),
                'status' => 'partially_dispensed',
                'notes' => 'Paludisme simple + infection bactérienne associée.',
            ]);
            $rx3_item1 = PrescriptionItem::create(['prescription_id' => $rx3->id, 'medicine_id' => $p3_med1->id, 'medicine_name' => $p3_med1->name, 'dosage' => '20/120mg', 'frequency' => '2x/jour', 'duration' => '3 jours', 'quantity_prescribed' => 10, 'quantity_dispensed' => 3, 'route' => 'oral', 'instructions' => '2 comprimés matin et soir']);
            $rx3_item2 = PrescriptionItem::create(['prescription_id' => $rx3->id, 'medicine_id' => $p3_med2->id, 'medicine_name' => $p3_med2->name, 'dosage' => '500mg', 'frequency' => '3x/jour', 'duration' => '5 jours', 'quantity_prescribed' => 10, 'quantity_dispensed' => 0, 'route' => 'oral', 'instructions' => '1 comprimé 3 fois par jour']);

            // Validation
            PharmaceuticalValidation::create([
                'prescription_id' => $rx3->id,
                'pharmacist_id' => $pharmacist->id,
                'validated_at' => now()->subDays(2),
                'status' => 'approved',
                'notes' => 'Validé. Surveillance des effets secondaires paludisme.',
            ]);

            // Dispensation 1 (partielle) : 3 Artéméther
            $disp1 = Dispensation::create([
                'prescription_id' => $rx3->id,
                'pharmacist_id' => $pharmacist->id,
                'dispensed_at' => now()->subDay(),
                'notes' => '1ère dispensation — 3 comprimés Artéméther/Luméfantrine.',
            ]);
            DispensationItem::create([
                'dispensation_id' => $disp1->id,
                'prescription_item_id' => $rx3_item1->id,
                'medicine_id' => $p3_med1->id,
                'quantity_dispensed' => 3,
                'lot_number' => $batch3a->lot_number,
                'unit_price' => $p3_med1->unit_price,
                'stock_batch_id' => $batch3a->id,
            ]);
            $batch3a->consume(3);
            Stock::where('medicine_id', $p3_med1->id)->first()?->removeStock(3);

            // ══════════════════════════════════════════════════════
            // PRESCRIPTION 4 : Totalement dispensée
            // ══════════════════════════════════════════════════════
            $p4_med1 = Medicine::where('code', 'MED-001')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-001', 'name' => 'Paracétamol', 'unit_price' => 50]));
            $p4_med2 = Medicine::where('code', 'MED-003')->firstOr(fn() => Medicine::factory()->create(['code' => 'MED-003', 'name' => 'Ibuprofène', 'unit_price' => 75]));

            $batch4a = $ensureBatch($p4_med1, 500, 'PARA-2026-E01');
            $batch4b = $ensureBatch($p4_med2, 400, 'IBU-2026-F01');

            $c4 = $ensureConsultation($patients[3]);
            $rx4 = Prescription::create([
                'consultation_id' => $c4->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patients[3]->id,
                'prescription_number' => 'RX-2026-0004',
                'issued_at' => now()->subDays(5),
                'valid_until' => now()->addDays(25),
                'status' => 'dispensed',
                'notes' => 'Céphalées + douleurs musculaires.',
            ]);
            $rx4_item1 = PrescriptionItem::create(['prescription_id' => $rx4->id, 'medicine_id' => $p4_med1->id, 'medicine_name' => $p4_med1->name, 'dosage' => '500mg', 'frequency' => '3x/jour', 'duration' => '5 jours', 'quantity_prescribed' => 20, 'quantity_dispensed' => 20, 'route' => 'oral', 'instructions' => '1 comprimé 3 fois par jour si nécessaire']);
            $rx4_item2 = PrescriptionItem::create(['prescription_id' => $rx4->id, 'medicine_id' => $p4_med2->id, 'medicine_name' => $p4_med2->name, 'dosage' => '400mg', 'frequency' => '2x/jour', 'duration' => '5 jours', 'quantity_prescribed' => 10, 'quantity_dispensed' => 10, 'route' => 'oral', 'instructions' => '1 comprimé matin et soir']);

            PharmaceuticalValidation::create([
                'prescription_id' => $rx4->id,
                'pharmacist_id' => $pharmacist->id,
                'validated_at' => now()->subDays(4),
                'status' => 'approved',
                'notes' => 'Validé sans réserve.',
            ]);

            // Dispensation 2 (complète pour RX4)
            $disp2 = Dispensation::create([
                'prescription_id' => $rx4->id,
                'pharmacist_id' => $pharmacist->id,
                'dispensed_at' => now()->subDays(4),
                'notes' => 'Dispensation complète — Paracétamol + Ibuprofène.',
            ]);
            DispensationItem::create([
                'dispensation_id' => $disp2->id,
                'prescription_item_id' => $rx4_item1->id,
                'medicine_id' => $p4_med1->id,
                'quantity_dispensed' => 20,
                'lot_number' => $batch4a->lot_number,
                'unit_price' => $p4_med1->unit_price,
                'stock_batch_id' => $batch4a->id,
            ]);
            DispensationItem::create([
                'dispensation_id' => $disp2->id,
                'prescription_item_id' => $rx4_item2->id,
                'medicine_id' => $p4_med2->id,
                'quantity_dispensed' => 10,
                'lot_number' => $batch4b->lot_number,
                'unit_price' => $p4_med2->unit_price,
                'stock_batch_id' => $batch4b->id,
            ]);
            $batch4a->consume(20);
            $batch4b->consume(10);
            Stock::where('medicine_id', $p4_med1->id)->first()?->removeStock(20);
            Stock::where('medicine_id', $p4_med2->id)->first()?->removeStock(10);

            // ══════════════════════════════════════════════════════
            // DISPENSATION 3 : Complète les 7 restants pour RX3
            // ══════════════════════════════════════════════════════
            $disp3 = Dispensation::create([
                'prescription_id' => $rx3->id,
                'pharmacist_id' => $pharmacist->id,
                'dispensed_at' => now(),
                'notes' => '2ème dispensation — reliquats Artéméther + Métronidazole.',
            ]);
            DispensationItem::create([
                'dispensation_id' => $disp3->id,
                'prescription_item_id' => $rx3_item1->id,
                'medicine_id' => $p3_med1->id,
                'quantity_dispensed' => 7,
                'lot_number' => $batch3a->lot_number,
                'unit_price' => $p3_med1->unit_price,
                'stock_batch_id' => $batch3a->id,
            ]);
            DispensationItem::create([
                'dispensation_id' => $disp3->id,
                'prescription_item_id' => $rx3_item2->id,
                'medicine_id' => $p3_med2->id,
                'quantity_dispensed' => 10,
                'lot_number' => $batch3b->lot_number,
                'unit_price' => $p3_med2->unit_price,
                'stock_batch_id' => $batch3b->id,
            ]);
            $batch3a->consume(7);
            $batch3b->consume(10);
            Stock::where('medicine_id', $p3_med1->id)->first()?->removeStock(7);
            Stock::where('medicine_id', $p3_med2->id)->first()?->removeStock(10);

            // ══════════════════════════════════════════════════════
            // COMMANDE 1 : En attente (draft)
            // ══════════════════════════════════════════════════════
            $po1 = PurchaseOrder::create([
                'order_number' => 'CMD-2026-0001',
                'pharmacist_id' => $pharmacist->id,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'supplier_contact' => $supplier->phone,
                'delivery_address' => 'Pharmacie centrale, Yaoundé',
                'payment_terms' => '30 jours',
                'status' => 'draft',
                'ordered_at' => now()->subDays(2),
                'expected_delivery' => now()->addDays(10),
                'total_amount' => 0,
                'notes' => 'Réapprovisionnement urgents — ruptures prévues.',
            ]);
            $po1_items = [
                ['medicine_id' => $p1_med1->id, 'quantity_ordered' => 50, 'unit_cost' => 2800, 'lot_number' => '', 'expiry_date' => null],
                ['medicine_id' => $p1_med2->id, 'quantity_ordered' => 30, 'unit_cost' => 900, 'lot_number' => '', 'expiry_date' => null],
                ['medicine_id' => $p4_med2->id, 'quantity_ordered' => 100, 'unit_cost' => 55, 'lot_number' => '', 'expiry_date' => null],
            ];
            foreach ($po1_items as $item) {
                PurchaseOrderItem::create(array_merge($item, ['purchase_order_id' => $po1->id, 'quantity_received' => 0]));
            }
            $po1->update(['total_amount' => $po1->items->sum(fn($i) => $i->quantity_ordered * $i->unit_cost)]);

            // ══════════════════════════════════════════════════════
            // COMMANDE 2 : En attente (draft)
            // ══════════════════════════════════════════════════════
            $po2 = PurchaseOrder::create([
                'order_number' => 'CMD-2026-0002',
                'pharmacist_id' => $pharmacist->id,
                'supplier_id' => $supplier->id,
                'supplier_name' => $supplier->name,
                'supplier_contact' => $supplier->phone,
                'delivery_address' => 'Pharmacie centrale, Yaoundé',
                'payment_terms' => '60 jours',
                'status' => 'draft',
                'ordered_at' => now()->subDay(),
                'expected_delivery' => now()->addDays(15),
                'total_amount' => 0,
                'notes' => 'Commande trimestrielle — médicaments essentiels.',
            ]);
            $po2_items = [
                ['medicine_id' => $p2_med1->id, 'quantity_ordered' => 500, 'unit_cost' => 110, 'lot_number' => '', 'expiry_date' => null],
                ['medicine_id' => $p2_med2->id, 'quantity_ordered' => 1000, 'unit_cost' => 40, 'lot_number' => '', 'expiry_date' => null],
                ['medicine_id' => $p4_med1->id, 'quantity_ordered' => 1000, 'unit_cost' => 35, 'lot_number' => '', 'expiry_date' => null],
            ];
            foreach ($po2_items as $item) {
                PurchaseOrderItem::create(array_merge($item, ['purchase_order_id' => $po2->id, 'quantity_received' => 0]));
            }
            $po2->update(['total_amount' => $po2->items->sum(fn($i) => $i->quantity_ordered * $i->unit_cost)]);

            DB::commit();

            $this->command->info("Flow pharmacy créé :");
            $this->command->info("  - 4 ordonnances (1 pending sans validation, 1 pending validée, 1 partielle, 1 dispensée)");
            $this->command->info("  - 3 dispensations (1 partielle RX3, 1 complète RX4, 1 complément RX3)");
            $this->command->info("  - 2 commandes en attente (draft)");

        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("Erreur durant PharmacyFlowSeeder : " . $e->getMessage());
            throw $e;
        }
    }
}
