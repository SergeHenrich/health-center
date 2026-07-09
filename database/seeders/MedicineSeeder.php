<?php

namespace Database\Seeders;

use App\Models\Medicine;
use App\Models\Stock;
use Illuminate\Database\Seeder;

class MedicineSeeder extends Seeder
{
    public function run(): void
    {
        $medicines = [
            ['code' => 'MED-001', 'name' => 'Paracétamol', 'generic_name' => 'Acetaminophen', 'category' => 'Analgésique', 'form' => 'tablet', 'strength' => '500mg', 'unit_price' => 50, 'requires_prescription' => false, 'qty' => 500],
            ['code' => 'MED-002', 'name' => 'Amoxicilline', 'generic_name' => 'Amoxicillin', 'category' => 'Antibiotique', 'form' => 'capsule', 'strength' => '500mg', 'unit_price' => 150, 'requires_prescription' => true, 'qty' => 300],
            ['code' => 'MED-003', 'name' => 'Ibuprofène', 'generic_name' => 'Ibuprofen', 'category' => 'Anti-inflammatoire', 'form' => 'tablet', 'strength' => '400mg', 'unit_price' => 75, 'requires_prescription' => false, 'qty' => 400],
            ['code' => 'MED-004', 'name' => 'Artéméther/Luméfantrine', 'generic_name' => 'Coartem', 'category' => 'Antipaludique', 'form' => 'tablet', 'strength' => '20/120mg', 'unit_price' => 2500, 'requires_prescription' => true, 'qty' => 150],
            ['code' => 'MED-005', 'name' => 'Métronidazole', 'generic_name' => 'Metronidazole', 'category' => 'Antibiotique', 'form' => 'tablet', 'strength' => '500mg', 'unit_price' => 100, 'requires_prescription' => true, 'qty' => 250],
            ['code' => 'MED-006', 'name' => 'Oméprazole', 'generic_name' => 'Omeprazole', 'category' => 'Antiacide', 'form' => 'capsule', 'strength' => '20mg', 'unit_price' => 200, 'requires_prescription' => true, 'qty' => 200],
            ['code' => 'MED-007', 'name' => 'Salbutamol', 'generic_name' => 'Albuterol', 'category' => 'Bronchodilatateur', 'form' => 'inhaler' === 'inhaler' ? 'other' : 'other', 'strength' => '100mcg', 'unit_price' => 3500, 'requires_prescription' => true, 'qty' => 80],
            ['code' => 'MED-008', 'name' => 'Sérum physiologique', 'generic_name' => 'Saline 0.9%', 'category' => 'Solution IV', 'form' => 'injection', 'strength' => '500ml', 'unit_price' => 800, 'requires_prescription' => true, 'qty' => 120],
            ['code' => 'MED-009', 'name' => 'Diclofénac', 'generic_name' => 'Diclofenac', 'category' => 'Anti-inflammatoire', 'form' => 'cream', 'strength' => '1%', 'unit_price' => 1200, 'requires_prescription' => false, 'qty' => 90],
            ['code' => 'MED-010', 'name' => 'Ciprofloxacine', 'generic_name' => 'Ciprofloxacin', 'category' => 'Antibiotique', 'form' => 'tablet', 'strength' => '500mg', 'unit_price' => 180, 'requires_prescription' => true, 'qty' => 220],
            ['code' => 'MED-011', 'name' => 'Vitamine C', 'generic_name' => 'Ascorbic Acid', 'category' => 'Vitamine', 'form' => 'tablet', 'strength' => '1000mg', 'unit_price' => 60, 'requires_prescription' => false, 'qty' => 600],
            ['code' => 'MED-012', 'name' => 'Loratadine', 'generic_name' => 'Loratadine', 'category' => 'Antihistaminique', 'form' => 'tablet', 'strength' => '10mg', 'unit_price' => 90, 'requires_prescription' => false, 'qty' => 350],
            ['code' => 'MED-013', 'name' => 'Insuline NPH', 'generic_name' => 'Insulin', 'category' => 'Antidiabétique', 'form' => 'injection', 'strength' => '100UI/ml', 'unit_price' => 4500, 'requires_prescription' => true, 'qty' => 40],
            ['code' => 'MED-014', 'name' => 'Sirop antitussif', 'generic_name' => 'Dextromethorphan', 'category' => 'Antitussif', 'form' => 'syrup', 'strength' => '100ml', 'unit_price' => 900, 'requires_prescription' => false, 'qty' => 180],
            ['code' => 'MED-015', 'name' => 'Furosémide', 'generic_name' => 'Furosemide', 'category' => 'Diurétique', 'form' => 'tablet', 'strength' => '40mg', 'unit_price' => 110, 'requires_prescription' => true, 'qty' => 200],
            ['code' => 'MED-016', 'name' => 'Métformine', 'generic_name' => 'Metformin', 'category' => 'Antidiabétique', 'form' => 'tablet', 'strength' => '850mg', 'unit_price' => 120, 'requires_prescription' => true, 'qty' => 280],
            ['code' => 'MED-017', 'name' => 'Amlodipine', 'generic_name' => 'Amlodipine', 'category' => 'Antihypertenseur', 'form' => 'tablet', 'strength' => '5mg', 'unit_price' => 130, 'requires_prescription' => true, 'qty' => 240],
            ['code' => 'MED-018', 'name' => 'Solution réhydratation orale', 'generic_name' => 'ORS', 'category' => 'Réhydratation', 'form' => 'other', 'strength' => 'sachet', 'unit_price' => 250, 'requires_prescription' => false, 'qty' => 8],
            ['code' => 'MED-019', 'name' => 'Gel hydroalcoolique', 'generic_name' => 'Hand sanitizer', 'category' => 'Hygiène', 'form' => 'other', 'strength' => '500ml', 'unit_price' => 1500, 'requires_prescription' => false, 'qty' => 5],
            ['code' => 'MED-020', 'name' => 'Compresses stériles', 'generic_name' => 'Sterile gauze', 'category' => 'Pansement', 'form' => 'other', 'strength' => '10x10cm', 'unit_price' => 300, 'requires_prescription' => false, 'qty' => 9],
        ];

        foreach ($medicines as $data) {
            $qty = $data['qty'];
            unset($data['qty']);

            $medicine = Medicine::firstOrCreate(['code' => $data['code']], $data);

            Stock::firstOrCreate(
                ['medicine_id' => $medicine->id],
                [
                    'quantity_available' => $qty,
                    'minimum_quantity'   => 10,
                    'maximum_quantity'   => 1000,
                    'last_updated_at'    => now(),
                ]
            );
        }
    }
}
