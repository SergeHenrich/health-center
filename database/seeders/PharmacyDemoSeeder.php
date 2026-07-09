<?php

namespace Database\Seeders;

use App\Models\{
    Consultation, Dispensation, DispensationItem, FormularyCommissionDecision,
    MedicationEvent, Medicine, NarcoticRegister, Patient,
    PharmaceuticalIntervention, PharmaceuticalValidation, PharmacyDocument,
    Prescription, PrescriptionItem, PurchaseOrder, PurchaseOrderItem,
    Stock, StockBatch, StockMovement, Supplier, SupplierContract,
    SupplierEvaluation, TherapeuticSubstitution, User, Warehouse, WarehouseStock,
};
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PharmacyDemoSeeder extends Seeder
{
    private array $users = [];
    private array $medicines = [];
    private array $patients = [];
    private array $suppliers = [];
    private array $warehouses = [];

    public function run(): void
    {
        $this->loadBaseData();
        $this->clearExistingRecords();

        DB::beginTransaction();
        try {
            $this->createSuppliers();
            $this->createWarehouses();
            $this->setupMedicines();
            $this->createPurchaseOrders();
            $this->createStockBatches();
            $this->createFormularyDecisions();
            $this->createTherapeuticSubstitutions();
            $this->createConsultationsAndPrescriptions();
            $this->createValidations();
            $this->createDispensations();
            $this->createMedicationEvents();
            $this->createNarcoticRegister();
            $this->createDocuments();
            $this->createSupplierContractsAndEvaluations();
            DB::commit();
            $this->report();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->command->error("PharmacyDemoSeeder failed: " . $e->getMessage());
            throw $e;
        }
    }

    private function loadBaseData(): void
    {
        $this->users = [
            'doctor'     => User::where('username', 'dr.martin')->firstOr(fn() => User::factory()->create(['username' => 'dr.martin'])->assignRole('general_practitioner')),
            'pharmacist' => User::where('username', 'pharmacien')->firstOr(fn() => User::factory()->create(['username' => 'pharmacien'])->assignRole('pharmacist')),
            'director'   => User::where('username', 'directeur')->firstOr(fn() => User::factory()->create(['username' => 'directeur'])->assignRole('director')),
            'nurse'      => User::where('username', 'infirmiere.kamga')->firstOr(fn() => User::factory()->create(['username' => 'infirmiere.kamga'])->assignRole('nurse')),
        ];

        $this->medicines['by_code'] = Medicine::all()->keyBy('code')->all();
        $this->medicines['by_id']   = Medicine::all()->keyBy('id')->all();

        $patients = Patient::take(20)->get();
        if ($patients->count() < 20) {
            $patients = Patient::factory(20 - $patients->count())->create()->merge($patients);
        }
        $this->patients = $patients->all();
    }

    private function clearExistingRecords(): void
    {
        $tables = [
            'narcotic_registers', 'medication_events', 'pharmacy_documents',
            'pharmaceutical_interventions', 'pharmaceutical_validations',
            'dispensation_items', 'dispensations', 'prescription_items',
            'prescriptions', 'stock_movements', 'stock_batches',
            'warehouse_stocks', 'purchase_order_items', 'purchase_orders',
            'supplier_contracts', 'supplier_evaluations',
            'therapeutic_substitutions', 'formulary_commission_decisions',
        ];
        foreach ($tables as $table) {
            DB::table($table)->delete();
        }
        DB::table('stocks')->delete();
        DB::table('suppliers')->delete();
        DB::table('warehouses')->delete();
    }

    private function med(string $code): Medicine
    {
        return $this->medicines['by_code'][$code];
    }

    private function patient(int $index): Patient
    {
        return $this->patients[$index % count($this->patients)];
    }

    private function ensureConsultation(Patient $patient): Consultation
    {
        $record = $patient->medicalRecord;
        if (!$record) {
            $record = \App\Models\MedicalRecord::factory()->create(['patient_id' => $patient->id]);
        }
        $complaints = ['Fievre et cephalees', 'Douleurs abdominales', 'Toux persistante', 'Consultation de suivi', 'Infection respiratoire', 'Lombalgie', "Crise d'asthme", 'Diabete deseausole'];
        return Consultation::create([
            'medical_record_id' => $record->id,
            'doctor_id'         => $this->users['doctor']->id,
            'consultation_date' => now()->subDays(rand(1, 15))->format('Y-m-d'),
            'chief_complaint'   => $complaints[array_rand($complaints)],
            'status'            => 'completed',
        ]);
    }

    private function ensureBatch(Medicine $medicine, int $qty, string $lot, ?int $warehouseIndex = null, int $expDays = 365): StockBatch
    {
        return StockBatch::create([
            'medicine_id'        => $medicine->id,
            'warehouse_id'       => $warehouseIndex !== null ? $this->warehouses[$warehouseIndex]->id : null,
            'lot_number'         => $lot,
            'expiry_date'        => Carbon::now()->addDays($expDays),
            'quantity_available' => $qty,
            'initial_quantity'   => $qty,
            'status'             => 'active',
            'unit_cost'          => $medicine->unit_price,
            'received_at'        => now()->subDays(rand(1, 30)),
        ]);
    }

    private function adjustStock(Medicine $medicine, int $qty): void
    {
        $stock = Stock::firstOrCreate(
            ['medicine_id' => $medicine->id],
            ['quantity_available' => 0, 'minimum_quantity' => 10, 'maximum_quantity' => 1000, 'last_updated_at' => now()]
        );
        $stock->addStock($qty);
    }

    private function consumeStock(Medicine $medicine, int $qty): void
    {
        $stock = Stock::where('medicine_id', $medicine->id)->first();
        if ($stock) {
            $stock->removeStock($qty);
        }
    }

    private function supplier(int $index): Supplier
    {
        return $this->suppliers[$index % count($this->suppliers)];
    }

    // ============================================================
    //  1 - SUPPLIERS (12)
    // ============================================================

    private function createSuppliers(): void
    {
        $data = [
            ['code' => 'SUP-001', 'name' => 'PharmaDistri SA',                   'contact_name' => 'Marc Tchinda',    'email' => 'contact@pharmadistri.cm',       'phone' => '677000001', 'city' => 'Douala',    'category' => 'Grossiste'],
            ['code' => 'SUP-002', 'name' => 'MedEquip Afrique',                   'contact_name' => 'Sandra Nkwi',     'email' => 'cmd@medequip-afrique.cm',       'phone' => '677000002', 'city' => 'Yaounde',   'category' => 'Distributeur'],
            ['code' => 'SUP-003', 'name' => 'Laboratoires M&B',                   'contact_name' => 'Paul Biyong',     'email' => 'fournisseur@labo-mb.cm',        'phone' => '677000003', 'city' => 'Douala',    'category' => 'Laboratoire'],
            ['code' => 'SUP-004', 'name' => 'Sante Plus Distribution',            'contact_name' => 'Chantal Eyenga',  'email' => 'commandes@santeplus.cm',        'phone' => '677000004', 'city' => 'Yaounde',   'category' => 'Grossiste'],
            ['code' => 'SUP-005', 'name' => 'PharmaGreen Cameroun',               'contact_name' => 'Joseph Mbah',     'email' => 'ventes@pharmagreen.cm',         'phone' => '677000005', 'city' => 'Bafoussam', 'category' => 'Fabricant'],
            ['code' => 'SUP-006', 'name' => 'DispoMed International',             'contact_name' => 'Esther Ngo Mbele','email' => 'info@disponed.cm',              'phone' => '677000006', 'city' => 'Douala',    'category' => 'Distributeur'],
            ['code' => 'SUP-007', 'name' => "Centrale d'Achat Pharmacie",         'contact_name' => 'Pierre Kamdem',   'email' => 'cap@cap-pharma.cm',             'phone' => '677000007', 'city' => 'Yaounde',   'category' => "Centrale d'achat"],
            ['code' => 'SUP-008', 'name' => 'BioPharm Solutions',                 'contact_name' => 'Mireille Tagny',  'email' => 'service@biopharm.cm',           'phone' => '677000008', 'city' => 'Douala',    'category' => 'Laboratoire'],
            ['code' => 'SUP-009', 'name' => 'Sante Monde Import',                 'contact_name' => 'Alain Foka',      'email' => 'import@santemonde.cm',          'phone' => '677000009', 'city' => 'Yaounde',   'category' => 'Importateur'],
            ['code' => 'SUP-010', 'name' => 'PharmaLogistique',                   'contact_name' => 'Gisele Mvondo',   'email' => 'logistique@pharmalog.cm',       'phone' => '677000010', 'city' => 'Douala',    'category' => 'Grossiste'],
            ['code' => 'SUP-011', 'name' => 'MedicAfrica',                        'contact_name' => 'Herve Tchaptchet','email' => 'ventes@medicafrica.cm',         'phone' => '677000011', 'city' => 'Bamenda',   'category' => 'Distributeur'],
            ['code' => 'SUP-012', 'name' => 'DistriPharm Centre',                 'contact_name' => 'Lydie Bille',     'email' => 'info@distripharm-centre.cm',    'phone' => '677000012', 'city' => 'Yaounde',   'category' => 'Grossiste'],
        ];
        foreach ($data as $row) {
            $this->suppliers[] = Supplier::create($row + ['is_active' => true]);
        }
    }

    // ============================================================
    //  2 - WAREHOUSES (3)
    // ============================================================

    private function createWarehouses(): void
    {
        $list = [
            ['code' => 'DEP-001', 'name' => 'Pharmacie Centrale',  'type' => 'central',   'location' => 'Rez-de-chaussee, aile A'],
            ['code' => 'DEP-002', 'name' => 'Urgences',            'type' => 'emergency', 'location' => 'Rez-de-chaussee, aile B'],
            ['code' => 'DEP-003', 'name' => 'Bloc Operatoire',     'type' => 'bloc',      'location' => '1er etage'],
        ];
        foreach ($list as $row) {
            $this->warehouses[] = Warehouse::create($row + ['is_active' => true]);
        }
    }

    // ============================================================
    //  3 - MEDICINES (add narcotics + psychotropes, refresh Stock)
    // ============================================================

    private function setupMedicines(): void
    {
        $additional = [
            ['code' => 'MED-021', 'name' => 'Morphine',   'generic_name' => 'Morphine Sulfate', 'category' => 'Analgesique stupefiant', 'form' => 'injection', 'strength' => '10mg/ml',  'unit_price' => 2000, 'is_narcotic' => true],
            ['code' => 'MED-022', 'name' => 'Fentanyl',   'generic_name' => 'Fentanyl Citrate', 'category' => 'Analgesique stupefiant', 'form' => 'injection', 'strength' => '50mcg/ml', 'unit_price' => 3500, 'is_narcotic' => true],
            ['code' => 'MED-023', 'name' => 'Tramadol',   'generic_name' => 'Tramadol HCl',     'category' => 'Analgesique opioide',    'form' => 'capsule',   'strength' => '50mg',    'unit_price' => 500,  'is_narcotic' => true],
            ['code' => 'MED-024', 'name' => 'Diazepam',   'generic_name' => 'Diazepam',         'category' => 'Anxiolytique',           'form' => 'tablet',    'strength' => '10mg',    'unit_price' => 150,  'is_psychotropic' => true],
            ['code' => 'MED-025', 'name' => 'Pethidine',  'generic_name' => 'Pethidine HCl',    'category' => 'Analgesique stupefiant', 'form' => 'injection', 'strength' => '50mg/ml', 'unit_price' => 1800, 'is_narcotic' => true],
        ];

        foreach ($additional as $data) {
            $medicine = Medicine::firstOrCreate(
                ['code' => $data['code']],
                [
                    'name' => $data['name'],
                    'generic_name' => $data['generic_name'],
                    'category' => $data['category'],
                    'form' => $data['form'],
                    'strength' => $data['strength'],
                    'unit_price' => $data['unit_price'],
                    'is_narcotic' => $data['is_narcotic'] ?? false,
                    'is_psychotropic' => $data['is_psychotropic'] ?? false,
                    'requires_prescription' => true,
                    'is_active' => true,
                ]
            );
            $this->medicines['by_code'][$medicine->code] = $medicine;
            $this->medicines['by_id'][$medicine->id] = $medicine;
        }

        // Reset non-narcotic medicines (cleanup from old data)
        Medicine::whereNotIn('code', ['MED-021', 'MED-022', 'MED-023', 'MED-024', 'MED-025'])
            ->update(['is_narcotic' => false, 'is_psychotropic' => false]);

        foreach ($this->medicines['by_code'] as $code => $med) {
            Stock::firstOrCreate(
                ['medicine_id' => $med->id],
                [
                    'quantity_available' => 0,
                    'minimum_quantity'   => 10,
                    'maximum_quantity'   => 1000,
                    'last_updated_at'    => now(),
                ]
            );
        }
    }

    // ============================================================
    //  4 - PURCHASE ORDERS (5)
    // ============================================================

    private function createPurchaseOrders(): void
    {
        $pos = [
            [
                'order_number' => 'CMD-2026-0001',
                'status' => 'draft',
                'pharmacist_id' => $this->users['pharmacist']->id,
                'supplier' => 0,
                'delivery_address' => 'Pharmacie Centrale, Hopital General',
                'payment_terms' => '30 jours fin de mois',
                'ordered_at' => now()->subDays(5),
                'expected_delivery' => now()->addDays(10),
                'notes' => 'Reapprovisionnement urgents',
                'items' => [
                    ['medicine' => 'MED-001', 'qty' => 500, 'cost' => 35],
                    ['medicine' => 'MED-007', 'qty' => 100, 'cost' => 2800],
                    ['medicine' => 'MED-003', 'qty' => 400, 'cost' => 55],
                ],
            ],
            [
                'order_number' => 'CMD-2026-0002',
                'status' => 'draft',
                'pharmacist_id' => $this->users['pharmacist']->id,
                'supplier' => 1,
                'delivery_address' => 'Pharmacie Centrale, Hopital General',
                'payment_terms' => '60 jours',
                'ordered_at' => now()->subDays(3),
                'expected_delivery' => now()->addDays(15),
                'notes' => 'Commande trimestrielle',
                'items' => [
                    ['medicine' => 'MED-002', 'qty' => 1000, 'cost' => 110],
                    ['medicine' => 'MED-011', 'qty' => 2000, 'cost' => 40],
                    ['medicine' => 'MED-006', 'qty' => 500,  'cost' => 140],
                    ['medicine' => 'MED-005', 'qty' => 800,  'cost' => 70],
                ],
            ],
            [
                'order_number' => 'CMD-2026-0003',
                'status' => 'sent',
                'pharmacist_id' => $this->users['pharmacist']->id,
                'approved_by' => 'director',
                'supplier' => 2,
                'delivery_address' => 'Pharmacie Centrale, Hopital General',
                'payment_terms' => '30 jours',
                'ordered_at' => now()->subDays(10),
                'expected_delivery' => now()->subDays(2),
                'notes' => 'Medicaments essentiels OMS',
                'items' => [
                    ['medicine' => 'MED-001', 'qty' => 300, 'cost' => 35],
                    ['medicine' => 'MED-011', 'qty' => 500, 'cost' => 42],
                    ['medicine' => 'MED-010', 'qty' => 400, 'cost' => 130],
                ],
                'received_items' => [0, 1],
            ],
            [
                'order_number' => 'CMD-2026-0004',
                'status' => 'received',
                'pharmacist_id' => $this->users['pharmacist']->id,
                'approved_by' => 'director',
                'supplier' => 3,
                'delivery_address' => 'Pharmacie Centrale, Hopital General',
                'payment_terms' => '30 jours',
                'ordered_at' => now()->subDays(20),
                'expected_delivery' => now()->subDays(8),
                'received_at' => now()->subDays(8),
                'notes' => 'Reapprovisionnement standard',
                'items' => [
                    ['medicine' => 'MED-002', 'qty' => 500, 'cost' => 110],
                    ['medicine' => 'MED-003', 'qty' => 400, 'cost' => 55],
                    ['medicine' => 'MED-005', 'qty' => 300, 'cost' => 70],
                    ['medicine' => 'MED-006', 'qty' => 200, 'cost' => 140],
                ],
                'received_items' => [0, 1, 2, 3],
            ],
            [
                'order_number' => 'CMD-2026-0005',
                'status' => 'draft',
                'pharmacist_id' => $this->users['pharmacist']->id,
                'supplier' => 4,
                'delivery_address' => 'Pharmacie Centrale, Hopital General',
                'payment_terms' => '45 jours',
                'ordered_at' => now()->subDay(),
                'expected_delivery' => now()->addDays(20),
                'notes' => 'Produits de saison - antipaludiques',
                'items' => [
                    ['medicine' => 'MED-004', 'qty' => 500, 'cost' => 1800],
                    ['medicine' => 'MED-013', 'qty' => 100, 'cost' => 3500],
                ],
            ],
        ];

        foreach ($pos as $poData) {
            $items = $poData['items'];
            unset($poData['items']);
            $receivedItems = $poData['received_items'] ?? [];
            unset($poData['received_items']);

            $approvedById = null;
            if (isset($poData['approved_by'])) {
                $approvedById = $this->users[$poData['approved_by']]->id;
                unset($poData['approved_by']);
            }

            $supplierModel = $this->supplier($poData['supplier']);
            $poData['supplier_id'] = $supplierModel->id;
            $poData['supplier_name'] = $supplierModel->name;
            $poData['supplier_contact'] = $supplierModel->phone;
            $poData['approved_by_id'] = $approvedById;
            unset($poData['supplier']);

            $po = PurchaseOrder::create($poData);
            $total = 0;

            foreach ($items as $i => $item) {
                $medicine = $this->med($item['medicine']);
                PurchaseOrderItem::create([
                    'purchase_order_id' => $po->id,
                    'medicine_id'       => $medicine->id,
                    'quantity_ordered'  => $item['qty'],
                    'quantity_received' => in_array($i, $receivedItems, true) ? $item['qty'] : 0,
                    'unit_cost'         => $item['cost'],
                ]);
                $total += $item['qty'] * $item['cost'];
            }

            $po->update(['total_amount' => $total]);
        }
    }

    // ============================================================
    //  5 - STOCK BATCHES (15)
    // ============================================================

    private function createStockBatches(): void
    {
        $batches = [
            ['medicine' => 'MED-001', 'qty' => 300, 'lot' => 'PARA-2026-R01', 'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-011', 'qty' => 500, 'lot' => 'VITC-2026-R01', 'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-002', 'qty' => 500, 'lot' => 'AMOX-2026-A01',  'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-003', 'qty' => 400, 'lot' => 'IBU-2026-B01',   'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-005', 'qty' => 300, 'lot' => 'METRO-2026-C01', 'wh' => 0, 'exp' => 360],
            ['medicine' => 'MED-006', 'qty' => 200, 'lot' => 'OMEP-2026-D01',  'wh' => 0, 'exp' => 300],
            ['medicine' => 'MED-004', 'qty' => 150, 'lot' => 'ART-2026-E01',   'wh' => 0, 'exp' => 330],
            ['medicine' => 'MED-017', 'qty' => 200, 'lot' => 'AML-2026-F01',   'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-015', 'qty' => 200, 'lot' => 'FURO-2026-G01',  'wh' => 0, 'exp' => 330],
            ['medicine' => 'MED-010', 'qty' => 200, 'lot' => 'CIPRO-2026-H01', 'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-021', 'qty' => 50,  'lot' => 'MOR-2026-J01',   'wh' => 0, 'exp' => 300],
            ['medicine' => 'MED-022', 'qty' => 40,  'lot' => 'FENT-2026-K01',  'wh' => 0, 'exp' => 300],
            ['medicine' => 'MED-023', 'qty' => 100, 'lot' => 'TRAM-2026-L01',  'wh' => 0, 'exp' => 365],
            ['medicine' => 'MED-024', 'qty' => 60,  'lot' => 'DIAZ-2026-M01',  'wh' => 0, 'exp' => 330],
            ['medicine' => 'MED-025', 'qty' => 30,  'lot' => 'PETH-2026-N01',  'wh' => 0, 'exp' => 300],
        ];

        $aggregate = [];

        foreach ($batches as $b) {
            $med = $this->med($b['medicine']);
            $this->ensureBatch($med, $b['qty'], $b['lot'], $b['wh'], $b['exp']);
            $this->adjustStock($med, $b['qty']);

            $key = $b['wh'] . '_' . $med->id;
            $aggregate[$key] = ($aggregate[$key] ?? 0) + $b['qty'];
        }

        foreach ($aggregate as $key => $qty) {
            [$whIdx, $medId] = explode('_', $key, 2);
            WarehouseStock::create([
                'warehouse_id'       => $this->warehouses[(int) $whIdx]->id,
                'medicine_id'        => (int) $medId,
                'quantity_available' => $qty,
                'minimum_quantity'   => 10,
                'maximum_quantity'   => 500,
                'last_updated_at'    => now(),
            ]);
        }
    }

    // ============================================================
    //  6 - FORMULARY COMMISSION DECISIONS (15)
    // ============================================================

    private function createFormularyDecisions(): void
    {
        $codes = ['MED-001', 'MED-002', 'MED-003', 'MED-004', 'MED-005', 'MED-006',
                   'MED-010', 'MED-011', 'MED-013', 'MED-015', 'MED-016', 'MED-021',
                   'MED-022', 'MED-023', 'MED-024'];

        foreach ($codes as $i => $code) {
            $med = $this->med($code);
            $isRestricted = in_array($code, ['MED-004', 'MED-013', 'MED-021', 'MED-022', 'MED-024']);
            FormularyCommissionDecision::create([
                'medicine_id'        => $med->id,
                'decided_by'         => $this->users['director']->id,
                'decision'           => $isRestricted ? 'inscrire_avec_reserve' : 'inscrire',
                'justification'      => $isRestricted
                    ? 'Utilisation restreinte aux services specialises. Prescription obligatoire.'
                    : 'Medicament essentiel selon liste OMS.',
                'decision_date'      => now()->subMonths(rand(1, 6))->format('Y-m-d'),
                'review_date'        => now()->addYear()->format('Y-m-d'),
                'reference_document' => 'PV-COM-' . str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT),
            ]);
        }
    }

    // ============================================================
    //  7 - THERAPEUTIC SUBSTITUTIONS (12)
    // ============================================================

    private function createTherapeuticSubstitutions(): void
    {
        $pairs = [
            ['MED-001', 'MED-003', 'alternative', 'Alternative en cas de douleur inflammatoire.'],
            ['MED-002', 'MED-010', 'therapeutic', "Substitution en cas d'allergie aux penicillines."],
            ['MED-003', 'MED-001', 'alternative', 'Alternative antalgique de palier 1.'],
            ['MED-005', 'MED-010', 'therapeutic', 'Spectre similaire pour infections mixtes.'],
            ['MED-015', 'MED-006', 'alternative', 'Retention hydrique alternative.'],
            ['MED-016', 'MED-013', 'therapeutic', "Alternative en cas d'intolerance."],
            ['MED-023', 'MED-021', 'therapeutic', 'Palier 2 vers palier 3 si douleurs severes.'],
            ['MED-024', 'MED-018', 'therapeutic', 'Alternative anxiolytique non medicamenteuse.'],
            ['MED-011', 'MED-009', 'alternative', 'Alternative anti-inflammatoire.'],
            ['MED-004', 'MED-005', 'therapeutic', 'Alternative antipaludeenne de deuxieme intention.'],
            ['MED-013', 'MED-016', 'therapeutic', 'Alternative hypogylcemiante.'],
            ['MED-007', 'MED-010', 'alternative', 'Alternative en cas de bronchite bacterienne.'],
        ];

        foreach ($pairs as $p) {
            TherapeuticSubstitution::create([
                'medicine_id'            => $this->med($p[0])->id,
                'substitute_medicine_id' => $this->med($p[1])->id,
                'substitution_type'      => $p[2],
                'reason'                 => $p[3],
                'is_active'              => true,
            ]);
        }
    }

    // ============================================================
    //  8 - CONSULTATIONS & PRESCRIPTIONS (12)
    // ============================================================

    private function createConsultationsAndPrescriptions(): void
    {
        $rxData = [
            ['patient' => 0,  'status' => 'pending',             'notes' => 'Cephalees tensionnelles', 'items' => [
                ['med' => 'MED-001', 'dosage' => '500mg', 'freq' => '3x/j', 'dur' => '5 jours',  'qty' => 20, 'route' => 'oral',      'instr' => '1 comprime si necessaire'],
            ]],
            ['patient' => 1,  'status' => 'validated',           'notes' => 'Infection respiratoire haute', 'items' => [
                ['med' => 'MED-002', 'dosage' => '500mg', 'freq' => '3x/j', 'dur' => '7 jours',  'qty' => 21, 'route' => 'oral',      'instr' => '1 gelule 3 fois par jour'],
            ]],
            ['patient' => 2,  'status' => 'validated',           'notes' => 'Paludisme simple confirme', 'items' => [
                ['med' => 'MED-004', 'dosage' => '20/120mg', 'freq' => '2x/j', 'dur' => '3 jours',  'qty' => 10, 'route' => 'oral',      'instr' => '2 comprimes matin et soir'],
                ['med' => 'MED-005', 'dosage' => '500mg',   'freq' => '3x/j', 'dur' => '5 jours',  'qty' => 10, 'route' => 'oral',      'instr' => '1 comprime 3 fois par jour'],
            ]],
            ['patient' => 3,  'status' => 'partially_dispensed', 'notes' => 'Douleurs post-operatoires severes', 'items' => [
                ['med' => 'MED-021', 'dosage' => '10mg',   'freq' => '4x/j', 'dur' => '3 jours',  'qty' => 12, 'disp' => 5, 'route' => 'IV',   'instr' => '1 ampoule IV toutes les 6h'],
                ['med' => 'MED-015', 'dosage' => '40mg',   'freq' => '1x/j', 'dur' => '5 jours',  'qty' => 10, 'disp' => 0, 'route' => 'oral', 'instr' => '1 comprime le matin'],
            ]],
            ['patient' => 4,  'status' => 'dispensed',           'notes' => 'Fievre et douleurs musculaires', 'items' => [
                ['med' => 'MED-001', 'dosage' => '500mg', 'freq' => '3x/j', 'dur' => '5 jours',  'qty' => 20, 'disp' => 20, 'route' => 'oral', 'instr' => '1 comprime 3 fois par jour'],
                ['med' => 'MED-003', 'dosage' => '400mg', 'freq' => '2x/j', 'dur' => '5 jours',  'qty' => 10, 'disp' => 10, 'route' => 'oral', 'instr' => '1 comprime matin et soir'],
            ]],
            ['patient' => 5,  'status' => 'dispensed',           'notes' => 'Lombalgie chronique', 'items' => [
                ['med' => 'MED-023', 'dosage' => '50mg',  'freq' => '3x/j', 'dur' => '7 jours',  'qty' => 21, 'disp' => 21, 'route' => 'oral', 'instr' => '1 capsule 3 fois par jour'],
                ['med' => 'MED-017', 'dosage' => '5mg',   'freq' => '1x/j', 'dur' => '30 jours', 'qty' => 30, 'disp' => 30, 'route' => 'oral', 'instr' => '1 comprime le matin'],
            ]],
            ['patient' => 6,  'status' => 'pending',             'notes' => "Diabete deseausolire - HbA1c 9.2%", 'items' => [
                ['med' => 'MED-016', 'dosage' => '850mg',  'freq' => '2x/j', 'dur' => '30 jours', 'qty' => 60, 'route' => 'oral', 'instr' => '1 comprime matin et soir'],
                ['med' => 'MED-013', 'dosage' => '100UI/ml', 'freq' => '2x/j', 'dur' => '30 jours', 'qty' => 2, 'route' => 'SC',   'instr' => 'Injection SC matin et soir'],
            ]],
            ['patient' => 7,  'status' => 'validated',           'notes' => "Crise d'asthme moderee", 'items' => [
                ['med' => 'MED-007', 'dosage' => '100mcg', 'freq' => '2x/j', 'dur' => '7 jours',  'qty' => 1, 'route' => 'inhalation', 'instr' => '1 bouffee matin et soir'],
                ['med' => 'MED-012', 'dosage' => '10mg',   'freq' => '1x/j', 'dur' => '10 jours', 'qty' => 10, 'route' => 'oral',       'instr' => '1 comprime le soir'],
            ]],
            ['patient' => 8,  'status' => 'partially_dispensed', 'notes' => 'Infection urinaire - E. coli sensible', 'items' => [
                ['med' => 'MED-010', 'dosage' => '500mg', 'freq' => '2x/j', 'dur' => '7 jours',  'qty' => 14, 'disp' => 7, 'route' => 'oral',   'instr' => '1 comprime 2 fois par jour'],
                ['med' => 'MED-009', 'dosage' => '1%',    'freq' => '3x/j', 'dur' => '5 jours',  'qty' => 1,  'disp' => 0, 'route' => 'topique', 'instr' => 'Appliquer 3 fois par jour'],
            ]],
            ['patient' => 9,  'status' => 'dispensed',           'notes' => 'Paludisme confirme - test rapide positif', 'items' => [
                ['med' => 'MED-004', 'dosage' => '20/120mg', 'freq' => '2x/j', 'dur' => '3 jours',  'qty' => 10, 'disp' => 10, 'route' => 'oral', 'instr' => '2 comprimes matin et soir'],
            ]],
            ['patient' => 10, 'status' => 'pending',             'notes' => 'Reflux gastro-oesophagien', 'items' => [
                ['med' => 'MED-006', 'dosage' => '20mg', 'freq' => '1x/j', 'dur' => '14 jours', 'qty' => 14, 'route' => 'oral', 'instr' => '1 capsule avant le petit-dejeuner'],
            ]],
            ['patient' => 11, 'status' => 'validated',           'notes' => 'Douleurs cancereuses - soins palliatifs', 'items' => [
                ['med' => 'MED-022', 'dosage' => '50mcg/ml', 'freq' => 'SOS',  'dur' => '7 jours',  'qty' => 5, 'route' => 'IV',   'instr' => '1 ampoule en perfusion si douleur > 7/10'],
                ['med' => 'MED-024', 'dosage' => '10mg',    'freq' => '1x/j', 'dur' => '7 jours',  'qty' => 7, 'route' => 'oral', 'instr' => '1 comprime le soir'],
            ]],
        ];

        foreach ($rxData as $rx) {
            $patient = $this->patient($rx['patient']);
            $consultation = $this->ensureConsultation($patient);

            $rxItems = $rx['items'];
            unset($rx['items']);

            $prescription = Prescription::create([
                'consultation_id'     => $consultation->id,
                'doctor_id'           => $this->users['doctor']->id,
                'patient_id'          => $patient->id,
                'prescription_number' => $this->nextRxNumber(),
                'issued_at'           => now()->subDays(rand(1, 10)),
                'valid_until'         => now()->addDays(28)->format('Y-m-d'),
                'status'              => $rx['status'],
                'notes'               => $rx['notes'],
            ]);

            foreach ($rxItems as $item) {
                $med = $this->med($item['med']);
                PrescriptionItem::create([
                    'prescription_id'     => $prescription->id,
                    'medicine_id'         => $med->id,
                    'medicine_name'       => $med->name,
                    'dosage'              => $item['dosage'],
                    'frequency'           => $item['freq'],
                    'duration'            => $item['dur'],
                    'quantity_prescribed' => $item['qty'],
                    'quantity_dispensed'  => $item['disp'] ?? 0,
                    'route'               => $item['route'],
                    'instructions'        => $item['instr'],
                ]);
            }
        }
    }

    private function nextRxNumber(): string
    {
        static $counter = 0;
        $counter++;
        return 'RX-2026-' . str_pad((string) $counter, 4, '0', STR_PAD_LEFT);
    }

    // ============================================================
    //  9 - PHARMACEUTICAL VALIDATIONS (4)
    // ============================================================

    private function createValidations(): void
    {
        $validationTargets = [1, 2, 4, 9];

        $validationData = [
            ['notes' => 'Prescription conforme au livret therapeutique. AVC OK.',                                                      'has_intervention' => false],
            ['notes' => 'Valide. Association artemether + metronidazole adaptee au tableau clinique.',                                  'has_intervention' => false],
            ['notes' => "Valide sans reserve. Posologie standard pour l'indication.",                                                   'has_intervention' => false],
            ['notes' => 'Valide avec intervention - ajustement posologique Fentanyl necessaire pour patient naif.',                    'has_intervention' => true],
        ];

        $prescriptions = Prescription::orderBy('id')->get();

        foreach ($validationTargets as $i => $idx) {
            $rx = $prescriptions[$idx] ?? null;
            if (!$rx) continue;

            $validation = PharmaceuticalValidation::create([
                'prescription_id' => $rx->id,
                'pharmacist_id'   => $this->users['pharmacist']->id,
                'validated_at'    => now()->subDays(rand(1, 5)),
                'status'          => 'approved',
                'notes'           => $validationData[$i]['notes'],
            ]);

            if ($validationData[$i]['has_intervention']) {
                $rxItem = $rx->items()->first();
                PharmaceuticalIntervention::create([
                    'validation_id'        => $validation->id,
                    'prescription_item_id' => $rxItem?->id,
                    'intervention_type'    => 'dosage_adjustment',
                    'severity'             => 'moderate',
                    'description'          => 'Dosage du Fentanyl trop eleve pour un patient naif. Reduction proposee de 100mcg a 50mcg.',
                    'action_taken'         => 'Medecin contacte. Nouveau dosage prescrit : 50mcg.',
                    'status'               => 'accepted',
                ]);
            }
        }
    }

    // ============================================================
    //  10 - DISPENSATIONS (6)
    // ============================================================

    private function createDispensations(): void
    {
        $prescriptions = Prescription::orderBy('id')->get();

        $dispData = [
            ['rx' => 3, 'notes' => '1ere dispensation - Morphine 5 ampoules', 'items' => [
                ['item_idx' => 0, 'qty' => 5, 'med' => 'MED-021'],
            ]],
            ['rx' => 4, 'notes' => 'Dispensation complete - Paracetamol + Ibuprofene', 'items' => [
                ['item_idx' => 0, 'qty' => 20, 'med' => 'MED-001'],
                ['item_idx' => 1, 'qty' => 10, 'med' => 'MED-003'],
            ]],
            ['rx' => 5, 'notes' => 'Dispensation complete - Tramadol + Amlodipine', 'items' => [
                ['item_idx' => 0, 'qty' => 21, 'med' => 'MED-023'],
                ['item_idx' => 1, 'qty' => 30, 'med' => 'MED-017'],
            ]],
            ['rx' => 8, 'notes' => '1ere dispensation partielle - Ciprofloxacine 7 comprimes', 'items' => [
                ['item_idx' => 0, 'qty' => 7, 'med' => 'MED-010'],
            ]],
            ['rx' => 9, 'notes' => 'Dispensation complete - Artemether/Lumefantrine', 'items' => [
                ['item_idx' => 0, 'qty' => 10, 'med' => 'MED-004'],
            ]],
            ['rx' => 3, 'notes' => '2eme dispensation - Furosemide 10 comprimes (reliquat)', 'items' => [
                ['item_idx' => 1, 'qty' => 10, 'med' => 'MED-015'],
            ]],
        ];

        $pharmacist = $this->users['pharmacist'];

        foreach ($dispData as $dd) {
            $rx = $prescriptions[$dd['rx']];
            $dispensation = Dispensation::create([
                'prescription_id' => $rx->id,
                'pharmacist_id'   => $pharmacist->id,
                'dispensed_at'    => now()->subDays(rand(0, 5)),
                'notes'           => $dd['notes'],
            ]);

            $rxItems = $rx->items()->orderBy('id')->get();

            foreach ($dd['items'] as $di) {
                $rxItem = $rxItems[$di['item_idx']] ?? null;
                if (!$rxItem) continue;

                $med = $this->med($di['med']);
                $batch = StockBatch::where('medicine_id', $med->id)
                    ->where('quantity_available', '>', 0)
                    ->orderBy('expiry_date')
                    ->first();

                DispensationItem::create([
                    'dispensation_id'      => $dispensation->id,
                    'prescription_item_id' => $rxItem->id,
                    'medicine_id'          => $med->id,
                    'quantity_dispensed'   => $di['qty'],
                    'lot_number'           => $batch?->lot_number ?? '',
                    'unit_price'           => $med->unit_price,
                    'stock_batch_id'       => $batch?->id,
                ]);

                if ($batch) {
                    $batch->consume($di['qty']);
                }
                $this->consumeStock($med, $di['qty']);
            }
        }
    }

    // ============================================================
    //  11 - MEDICATION EVENTS (4)
    // ============================================================

    private function createMedicationEvents(): void
    {
        $prescriptions = Prescription::orderBy('id')->get();

        $events = [
            [
                'type' => 'adverse_reaction',
                'severity' => 'moderate',
                'status' => 'resolved',
                'medicine' => 'MED-002',
                'patient' => $prescriptions[1]?->patient_id,
                'prescription_id' => $prescriptions[1]?->id,
                'reported_by' => 'nurse',
                'assigned_to' => 'pharmacist',
                'description' => "Reaction allergique cutane (urticaire) 2 jours apres le debut de l'amoxicilline.",
                'cause' => 'Allergie aux beta-lactamines non documentee dans le dossier patient.',
                'action_taken' => "Arret de l'amoxicilline. Relais par ciprofloxacine. Antihistaminique prescrit.",
                'corrective_actions' => 'Mise a jour du dossier patient avec allergie. Ajout alerte dans le systeme.',
                'occurred_at' => now()->subDays(6),
                'resolved_at' => now()->subDays(3),
            ],
            [
                'type' => 'medication_error',
                'severity' => 'low',
                'status' => 'resolved',
                'medicine' => 'MED-022',
                'patient' => $prescriptions[11]?->patient_id,
                'prescription_id' => $prescriptions[11]?->id,
                'reported_by' => 'pharmacist',
                'assigned_to' => 'doctor',
                'description' => 'Erreur de dosage sur la prescription de Fentanyl : 100mcg prescrits au lieu de 50mcg pour un patient naif aux opioides.',
                'cause' => "Medecin a utilise un dosage standard sans tenir compte du statut naif du patient.",
                'action_taken' => 'Intervention pharmaceutique : contact du medecin, reduction a 50mcg.',
                'corrective_actions' => "Sensibilisation de l'equipe medicale sur les paliers opioides.",
                'occurred_at' => now()->subDays(4),
                'resolved_at' => now()->subDays(3),
            ],
            [
                'type' => 'drug_interaction',
                'severity' => 'high',
                'status' => 'monitoring',
                'medicine' => 'MED-023',
                'patient' => $prescriptions[5]?->patient_id,
                'prescription_id' => $prescriptions[5]?->id,
                'reported_by' => 'pharmacist',
                'assigned_to' => 'nurse',
                'description' => "Interaction potentielle entre Tramadol et Amlodipine : risque majore de sedation et d'hypotension.",
                'cause' => 'Association medicamenteuse a risque chez le patient age.',
                'action_taken' => "Surveillance renforcee de la pression arterielle et de la sedation. Information du personnel soignant.",
                'corrective_actions' => 'Protocole de surveillance mis en place pour 48h.',
                'occurred_at' => now()->subDays(2),
                'resolved_at' => null,
            ],
            [
                'type' => 'near_miss',
                'severity' => 'moderate',
                'status' => 'resolved',
                'medicine' => 'MED-004',
                'patient' => $prescriptions[9]?->patient_id,
                'prescription_id' => $prescriptions[9]?->id,
                'reported_by' => 'pharmacist',
                'assigned_to' => 'pharmacist',
                'description' => "Quasi-erreur : dispensation d'Artemether/Lumefantrine preparee pour le mauvais patient (homonymie).",
                'cause' => "Deux patients avec le meme nom de famille dans la file d'attente. Verification d'identite insuffisante.",
                'action_taken' => "Erreur detectee avant remise du medicament. Double verification de l'identite mise en place.",
                'corrective_actions' => "Ajout de la date de naissance comme verification systematique avant toute dispensation.",
                'occurred_at' => now()->subDays(1),
                'resolved_at' => now()->subHours(20),
            ],
        ];

        foreach ($events as $ev) {
            MedicationEvent::create([
                'type'               => $ev['type'],
                'severity'           => $ev['severity'],
                'status'             => $ev['status'],
                'medicine_id'        => $this->med($ev['medicine'])->id,
                'patient_id'         => $ev['patient'],
                'prescription_id'    => $ev['prescription_id'],
                'dispensation_id'    => null,
                'reported_by'        => $this->users[$ev['reported_by']]->id,
                'assigned_to'        => $this->users[$ev['assigned_to']]->id,
                'description'        => $ev['description'],
                'cause'              => $ev['cause'],
                'action_taken'       => $ev['action_taken'],
                'corrective_actions' => $ev['corrective_actions'],
                'occurred_at'        => $ev['occurred_at'],
                'resolved_at'        => $ev['resolved_at'],
                'notes'              => null,
            ]);
        }
    }

    // ============================================================
    //  12 - NARCOTIC REGISTER (25 entries - 5 medicines x 5 each)
    // ============================================================

    private function createNarcoticRegister(): void
    {
        $narcoticMedicines = [
            'MED-021' => ['Morphine',  50, 10, 5, 30, 8],
            'MED-022' => ['Fentanyl',  40, 5, 10, 20, 3],
            'MED-023' => ['Tramadol', 100, 20, 15, 50, 10],
            'MED-024' => ['Diazepam',  60, 10, 5, 30, 7],
            'MED-025' => ['Pethidine', 30, 5, 3, 20, 4],
        ];

        $pharmacist = $this->users['pharmacist'];

        foreach ($narcoticMedicines as $code => $data) {
            [$name, $in1, $out1, $out2, $in2, $out3] = $data;
            $med = $this->med($code);

            $r1 = NarcoticRegister::create([
                'medicine_id'            => $med->id,
                'pharmacist_id'          => $pharmacist->id,
                'lot_number'             => strtoupper(substr($code, -3)) . '-2026-N01',
                'quantity_in'            => $in1,
                'quantity_out'           => 0,
                'balance_after'          => $in1,
                'prescriber_name'        => null,
                'prescription_number'    => null,
                'notes'                  => 'Reception initiale - ' . $name,
                'recorded_at'            => now()->subDays(30),
            ]);

            $b1 = $r1->balance_after - $out1;
            NarcoticRegister::create([
                'medicine_id'            => $med->id,
                'pharmacist_id'          => $pharmacist->id,
                'patient_id'             => $this->patient(3)->id,
                'lot_number'             => $r1->lot_number,
                'quantity_in'            => 0,
                'quantity_out'           => $out1,
                'balance_after'          => $b1,
                'prescriber_name'        => 'Dr. Martin',
                'prescription_number'    => 'RX-2026-0004',
                'notes'                  => 'Dispensation - ' . $name . ' (' . $out1 . ' unites)',
                'recorded_at'            => now()->subDays(25),
            ]);

            $b2 = $b1 - $out2;
            NarcoticRegister::create([
                'medicine_id'            => $med->id,
                'pharmacist_id'          => $pharmacist->id,
                'patient_id'             => $this->patient(7)->id,
                'lot_number'             => $r1->lot_number,
                'quantity_in'            => 0,
                'quantity_out'           => $out2,
                'balance_after'          => $b2,
                'prescriber_name'        => 'Dr. Martin',
                'prescription_number'    => 'RX-2026-0012',
                'notes'                  => 'Dispensation - ' . $name . ' (' . $out2 . ' unites)',
                'recorded_at'            => now()->subDays(18),
            ]);

            $b3 = $b2 + $in2;
            NarcoticRegister::create([
                'medicine_id'            => $med->id,
                'pharmacist_id'          => $pharmacist->id,
                'lot_number'             => strtoupper(substr($code, -3)) . '-2026-N02',
                'quantity_in'            => $in2,
                'quantity_out'           => 0,
                'balance_after'          => $b3,
                'prescriber_name'        => null,
                'prescription_number'    => null,
                'notes'                  => 'Reapprovisionnement - ' . $name,
                'recorded_at'            => now()->subDays(10),
            ]);

            $b4 = $b3 - $out3;
            NarcoticRegister::create([
                'medicine_id'            => $med->id,
                'pharmacist_id'          => $pharmacist->id,
                'patient_id'             => $this->patient(11)->id,
                'lot_number'             => $r1->lot_number,
                'quantity_in'            => 0,
                'quantity_out'           => $out3,
                'balance_after'          => $b4,
                'prescriber_name'        => 'Dr. Martin',
                'prescription_number'    => 'RX-2026-0012',
                'notes'                  => 'Dispensation - ' . $name . ' (' . $out3 . ' unites)',
                'recorded_at'            => now()->subDays(5),
            ]);
        }
    }

    // ============================================================
    //  13 - PHARMACY DOCUMENTS (12)
    // ============================================================

    private function createDocuments(): void
    {
        $docs = [
            ['title' => 'PV Commission du Livret 2026-01',         'type' => 'commission_report', 'ref' => 'PV-COM-001', 'desc' => 'Proces-verbal de la commission pharmaceutique du 15 janvier 2026.',                             's' => null,  'm' => null],
            ['title' => 'Fiche technique Paracetamol',              'type' => 'technical_sheet', 'ref' => 'FT-PARA-001',  'desc' => 'Fiche technique Paracetamol 500mg.',                                                            's' => null,  'm' => 'MED-001'],
            ['title' => 'Fiche technique Amoxicilline',             'type' => 'technical_sheet', 'ref' => 'FT-AMOX-001',  'desc' => 'Fiche technique Amoxicilline 500mg.',                                                           's' => null,  'm' => 'MED-002'],
            ['title' => 'Contrat-cadre Pharmacie 2026',             'type' => 'contract',        'ref' => 'CT-PHARMA-2026','desc' => 'Contrat-cadre annuel fournisseurs pharmacie.',                                                   's' => 0,     'm' => null],
            ['title' => 'Rapport trimestriel Q1 2026',              'type' => 'report',          'ref' => 'RPT-Q1-2026',  'desc' => "Rapport d'activite pharmaceutique du premier trimestre.",                                    's' => null,  'm' => null],
            ['title' => 'Bon de commande CMD-2026-0001',            'type' => 'purchase_order',  'ref' => 'BC-0001',      'desc' => 'Bon de commande fournisseur PharmaDistri SA.',                                                's' => 0,     'm' => null],
            ['title' => 'Certificat analyse Morphine',              'type' => 'quality_certificate','ref' => 'CQ-MOR-001','desc' => 'Certificat de controle qualite lot MOR-2026-J01.',                                             's' => null,  'm' => 'MED-021'],
            ['title' => 'Fiche conservation Insuline',              'type' => 'storage_guide',   'ref' => 'STO-INSU-001', 'desc' => 'Guide de conservation chaine du froid insuline.',                                               's' => null,  'm' => 'MED-013'],
            ['title' => 'Rapport annuel stupefiants 2025',          'type' => 'narcotic_report', 'ref' => 'NARC-RPT-2025','desc' => 'Bilan annuel de consommation des stupefiants.',                                                 's' => null,  'm' => null],
            ['title' => 'Procedure validation pharmaceutique',      'type' => 'procedure',       'ref' => 'PROC-PHARM-001','desc' => 'Procedure de validation pharmaceutique des prescriptions.',                                   's' => null,  'm' => null],
            ['title' => 'Catalogue fournisseurs 2026',              'type' => 'supplier_catalog', 'ref' => 'CAT-2026',    'desc' => 'Catalogue des fournisseurs agrees de la pharmacie.',                                           's' => 1,     'm' => null],
            ['title' => 'Convention Medicaments Stupefiants',       'type' => 'regulatory',      'ref' => 'REG-NARC-001', 'desc' => 'Convention de gestion des stupefiants signee par le directeur.',                               's' => null,  'm' => null],
        ];

        foreach ($docs as $d) {
            PharmacyDocument::create([
                'title'       => $d['title'],
                'type'        => $d['type'],
                'reference'   => $d['ref'],
                'description' => $d['desc'],
                'file_path'   => 'documents/pharmacy/' . $d['ref'] . '.pdf',
                'file_name'   => $d['ref'] . '.pdf',
                'file_type'   => 'application/pdf',
                'file_size'   => rand(100000, 500000),
                'version'     => 1,
                'uploaded_by' => $this->users['pharmacist']->id,
                'medicine_id' => $d['m'] ? $this->med($d['m'])->id : null,
                'supplier_id' => $d['s'] !== null ? $this->supplier($d['s'])->id : null,
                'is_active'   => true,
                'published_at' => now()->subDays(rand(1, 60)),
            ]);
        }
    }

    // ============================================================
    //  14 - SUPPLIER CONTRACTS (12) & EVALUATIONS (12)
    // ============================================================

    private function createSupplierContractsAndEvaluations(): void
    {
        for ($i = 0; $i < 12; $i++) {
            $supplier = $this->supplier($i);
            $startDate = now()->subMonths(rand(1, 12));

            SupplierContract::create([
                'supplier_id'      => $supplier->id,
                'contract_number'  => 'CTR-' . str_pad((string) ($i + 1), 4, '0', STR_PAD_LEFT) . '-2026',
                'start_date'       => $startDate->format('Y-m-d'),
                'end_date'         => $startDate->copy()->addYear()->format('Y-m-d'),
                'discount_percent' => rand(0, 15),
                'terms'            => match (rand(0, 2)) {
                    0 => 'Paiement a 30 jours fin de mois. Remise 2% pour reglement anticipe.',
                    1 => 'Paiement a 60 jours. Prix fermes pour le trimestre.',
                    2 => 'Paiement comptant. Remise 5% sur la premiere commande.',
                },
                'status'           => 'active',
            ]);

            SupplierEvaluation::create([
                'supplier_id'       => $supplier->id,
                'evaluated_by'      => $this->users['pharmacist']->id,
                'evaluation_date'   => now()->subDays(rand(1, 90))->format('Y-m-d'),
                'quality_score'     => rand(7, 10),
                'delivery_score'    => rand(6, 10),
                'price_score'       => rand(6, 9),
                'overall_score'     => rand(7, 10),
                'comments'          => match (true) {
                    $i < 3  => 'Excellent fournisseur. Conforme aux normes.',
                    $i < 6  => 'Bon rapport qualite-prix. Delais respectes.',
                    $i < 9  => 'Qualite satisfaisante. Delais parfois allonges.',
                    default => "Fournisseur en periode d'essai. A reevaluer.",
                },
            ]);
        }
    }

    // ============================================================
    //  REPORT
    // ============================================================

    private function report(): void
    {
        $this->command->info('');
        $this->command->info('+----------------------------------------------------+');
        $this->command->info('|   PharmacyDemoSeeder - Resume                     |');
        $this->command->info('+----------------------------------------------------+');
        $this->command->info('| Fournisseurs          : ' . str_pad((string) Supplier::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Depots                : ' . str_pad((string) Warehouse::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Commandes             : ' . str_pad((string) PurchaseOrder::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Lots                  : ' . str_pad((string) StockBatch::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Prescriptions         : ' . str_pad((string) Prescription::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Validations           : ' . str_pad((string) PharmaceuticalValidation::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Dispensations         : ' . str_pad((string) Dispensation::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Evenements            : ' . str_pad((string) MedicationEvent::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Registre stupefiants  : ' . str_pad((string) NarcoticRegister::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Decisions commission  : ' . str_pad((string) FormularyCommissionDecision::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Substitutions         : ' . str_pad((string) TherapeuticSubstitution::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Documents             : ' . str_pad((string) PharmacyDocument::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Contrats              : ' . str_pad((string) SupplierContract::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('| Evaluations           : ' . str_pad((string) SupplierEvaluation::count(), 32, ' ', STR_PAD_LEFT) . ' |');
        $this->command->info('+----------------------------------------------------+');

        $this->command->info('');
        $this->command->info('Statuts des ordonnances :');
        foreach (Prescription::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->orderBy('status')->get() as $row) {
            $this->command->info("  - {$row->status}: {$row->total}");
        }

        $this->command->info('');
        $this->command->info('Statuts des commandes :');
        foreach (PurchaseOrder::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')->orderBy('status')->get() as $row) {
            $this->command->info("  - {$row->status}: {$row->total}");
        }

        $this->command->info('');
        $this->command->info('Medicaments stupefiants actifs :');
        foreach (Medicine::where('is_narcotic', true)->get() as $m) {
            $stock = Stock::where('medicine_id', $m->id)->first();
            $this->command->info("  - {$m->name} ({$m->code}) - Stock: " . ($stock?->quantity_available ?? 0));
        }

        $this->command->info('');
        $this->command->info('Medicaments psychotropes actifs :');
        foreach (Medicine::where('is_psychotropic', true)->get() as $m) {
            $stock = Stock::where('medicine_id', $m->id)->first();
            $this->command->info("  - {$m->name} ({$m->code}) - Stock: " . ($stock?->quantity_available ?? 0));
        }

        $this->command->info('');
        $this->command->info('Workflow pharmacie complet - OK');
    }
}
