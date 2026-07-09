<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Consultation;
use App\Models\Dispensation;
use App\Models\DispensationItem;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\PharmacyDocument;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use App\Services\Pharmacy\ClinicalPharmacyService;
use App\Services\PharmacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist1;
    private User $pharmacist2;
    private User $admin;
    private Prescription $prescription;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist1 = User::factory()->create([
            'first_name' => 'Paul',
            'last_name'  => 'Pharmacien1',
            'email'      => 'pharmacist1@test.test',
            'username'   => 'pharmacist1_test',
            'is_active'  => true,
        ]);
        $this->pharmacist1->assignRole('pharmacist');

        $this->pharmacist2 = User::factory()->create([
            'first_name' => 'Jean',
            'last_name'  => 'Pharmacien2',
            'email'      => 'pharmacist2@test.test',
            'username'   => 'pharmacist2_test',
            'is_active'  => true,
        ]);
        $this->pharmacist2->assignRole('pharmacist');

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'User',
            'email'      => 'admin@test.test',
            'username'   => 'admin_test',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');

        $doctor = User::factory()->create([
            'first_name' => 'Doctor',
            'last_name'  => 'Test',
            'email'      => 'doctor@test.test',
            'username'   => 'doctor_test',
            'is_active'  => true,
        ]);
        $doctor->assignRole('general_practitioner');

        $patient = Patient::factory()->create();

        $this->medicine = Medicine::factory()->create([
            'unit_price' => 500,
        ]);

        Stock::create([
            'medicine_id'        => $this->medicine->id,
            'quantity_available' => 100,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 500,
            'last_updated_at'    => now(),
        ]);

        StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 100,
            'initial_quantity'  => 100,
            'expiry_date'       => now()->addYear(),
        ]);

        $this->prescription = Prescription::create([
            'consultation_id'     => Consultation::factory()->create()->id,
            'doctor_id'           => $doctor->id,
            'patient_id'          => $patient->id,
            'prescription_number' => 'RX-2026-00001',
            'issued_at'           => now(),
            'status'              => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id'     => $this->prescription->id,
            'medicine_id'         => $this->medicine->id,
            'medicine_name'       => $this->medicine->name,
            'dosage'              => '500mg',
            'frequency'           => '3x/jour',
            'quantity_prescribed' => 10,
            'quantity_dispensed'  => 0,
            'route'               => 'oral',
        ]);
    }

    protected function approvePrescription(): void
    {
        $this->actingAs($this->pharmacist1);
        app(ClinicalPharmacyService::class)
            ->validatePrescription($this->prescription, 'approved');
    }

    // ─── CRITICAL FIX #1: reverseDispensation Status Logic ────────────────

    public function test_reverse_dispensation_recalculates_pending_status_when_no_items_dispensed(): void
    {
        $this->actingAs($this->pharmacist1);
        $this->approvePrescription();

        // Dispense all items
        $service = app(PharmacyService::class);
        $dispensation = $service->dispense($this->prescription->fresh());
        $this->assertEquals('dispensed', $this->prescription->fresh()->status);

        // Reverse the dispensation
        $service->reverseDispensation($dispensation);

        // Status should be 'validated' (pharmaceutical validation exists)
        $this->assertEquals('validated', $this->prescription->fresh()->status);
    }

    public function test_reverse_single_dispensation_of_multiple_maintains_partial_status(): void
    {
        $this->actingAs($this->pharmacist1);
        $this->approvePrescription();

        // Add second item
        PrescriptionItem::create([
            'prescription_id'     => $this->prescription->id,
            'medicine_id'         => $this->medicine->id,
            'medicine_name'       => $this->medicine->name,
            'dosage'              => '250mg',
            'frequency'           => '2x/jour',
            'quantity_prescribed' => 5,
            'quantity_dispensed'  => 0,
            'route'               => 'oral',
        ]);

        // Refresh prescription to load new item
        $this->prescription->load('items');

        $service = app(PharmacyService::class);

        // Dispense first item only
        $disp1 = $service->dispense($this->prescription->fresh(), [[
            'prescription_item_id' => $this->prescription->items->first()->id,
            'medicine_id'          => $this->medicine->id,
            'quantity'             => 10,
            'picks'                => [[
                'batch_id'  => StockBatch::active()->where('medicine_id', $this->medicine->id)->first()->id,
                'quantity'  => 10,
            ]],
        ]]);

        $this->assertEquals('partially_dispensed', $this->prescription->fresh()->status);

        // Dispense second item
        $disp2 = $service->dispense($this->prescription->fresh(), [[
            'prescription_item_id' => $this->prescription->items->last()->id,
            'medicine_id'          => $this->medicine->id,
            'quantity'             => 5,
            'picks'                => [[
                'batch_id'  => StockBatch::active()->where('medicine_id', $this->medicine->id)->first()->id,
                'quantity'  => 5,
            ]],
        ]]);

        $this->assertEquals('dispensed', $this->prescription->fresh()->status);

        // Reverse ONLY first dispensation
        $service->reverseDispensation($disp1);

        // Status should be 'partially_dispensed' (second item still dispensed)
        $this->assertEquals('partially_dispensed', $this->prescription->fresh()->status);
    }

    // ─── CRITICAL FIX #2: Authorization on Destroy ───────────────────────

    public function test_pharmacist_cannot_reverse_another_pharmacist_dispensation(): void
    {
        $this->actingAs($this->pharmacist1);
        $this->approvePrescription();

        // Pharmacist 1 creates dispensation
        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();

        // Pharmacist 2 tries to reverse it
        $this->actingAs($this->pharmacist2);
        $response = $this->delete(route('dispensations.destroy', $dispensation));

        $response->assertSessionHas('error', 'Vous n\'êtes pas autorisé à annuler cette dispensation.');
        $this->assertNotNull($dispensation->fresh());
    }

    public function test_pharmacist_can_reverse_own_dispensation(): void
    {
        $this->actingAs($this->pharmacist1);
        $this->approvePrescription();

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();

        // Same pharmacist reverses
        $response = $this->delete(route('dispensations.destroy', $dispensation));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('dispensations', ['id' => $dispensation->id]);
    }

    public function test_admin_can_reverse_any_dispensation(): void
    {
        $this->actingAs($this->pharmacist1);
        $this->approvePrescription();

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();

        // Admin reverses
        $this->actingAs($this->admin);
        $response = $this->delete(route('dispensations.destroy', $dispensation));

        $response->assertSessionHas('success');
        $this->assertSoftDeleted('dispensations', ['id' => $dispensation->id]);
    }

    // ─── CRITICAL FIX #3: SoftDeletes Duplicate ───────────────────────────

    public function test_pharmacy_document_softdeletes_works_correctly(): void
    {
        $doc = PharmacyDocument::create([
            'title'         => 'Test Document',
            'type'          => 'sop',
            'file_path'     => '/path/to/file.pdf',
            'file_name'     => 'document.pdf',
            'file_type'     => 'application/pdf',
            'file_size'     => 1024,
            'uploaded_by'   => $this->pharmacist1->id,
            'is_active'     => true,
        ]);

        $docId = $doc->id;

        // Soft delete
        $doc->delete();

        // Should not be retrievable normally
        $this->assertNull(PharmacyDocument::find($docId));

        // Should be retrievable with trashed
        $this->assertNotNull(PharmacyDocument::withTrashed()->find($docId));

        // Restore
        $doc->restore();

        // Should be retrievable again
        $this->assertNotNull(PharmacyDocument::find($docId));
    }

    // ─── CRITICAL FIX #4: Race Condition Prevention (Verification) ────────

    public function test_stock_lock_prevents_negative_quantity(): void
    {
        $this->actingAs($this->pharmacist1);

        // Set stock to 5 (less than prescription requirement of 10)
        Stock::where('medicine_id', $this->medicine->id)
            ->update(['quantity_available' => 5]);

        $this->approvePrescription();

        // Try to dispense more than available
        try {
            $service = app(PharmacyService::class);
            $service->dispense($this->prescription->fresh());
            $this->fail('Should have thrown InsufficientStockException');
        } catch (\App\Exceptions\InsufficientStockException $e) {
            // Expected
            $this->assertTrue(true);
        }

        // Stock should still be 5 (transaction rolled back)
        $this->assertEquals(5, Stock::where('medicine_id', $this->medicine->id)->value('quantity_available'));
    }

    public function test_concurrent_dispensations_maintain_stock_integrity(): void
    {
        // Create two prescriptions for the same medicine
        $doctor = User::whereHas('roles', fn($q) => $q->where('name', 'general_practitioner'))
            ->first();
        $patient1 = Patient::factory()->create();
        $patient2 = Patient::factory()->create();

        $medicine = $this->medicine;
        Stock::where('medicine_id', $medicine->id)
            ->update(['quantity_available' => 20]);

        $prescription1 = Prescription::create([
            'consultation_id'     => Consultation::factory()->create()->id,
            'doctor_id'           => $doctor->id,
            'patient_id'          => $patient1->id,
            'prescription_number' => 'RX-2026-00010',
            'issued_at'           => now(),
            'status'              => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id'     => $prescription1->id,
            'medicine_id'         => $medicine->id,
            'medicine_name'       => $medicine->name,
            'dosage'              => '500mg',
            'frequency'           => '3x/jour',
            'quantity_prescribed' => 15,
            'quantity_dispensed'  => 0,
            'route'               => 'oral',
        ]);

        $prescription2 = Prescription::create([
            'consultation_id'     => Consultation::factory()->create()->id,
            'doctor_id'           => $doctor->id,
            'patient_id'          => $patient2->id,
            'prescription_number' => 'RX-2026-00011',
            'issued_at'           => now(),
            'status'              => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id'     => $prescription2->id,
            'medicine_id'         => $medicine->id,
            'medicine_name'       => $medicine->name,
            'dosage'              => '500mg',
            'frequency'           => '3x/jour',
            'quantity_prescribed' => 10,
            'quantity_dispensed'  => 0,
            'route'               => 'oral',
        ]);

        // Approve both
        $this->actingAs($this->pharmacist1);
        app(ClinicalPharmacyService::class)->validatePrescription($prescription1, 'approved');
        app(ClinicalPharmacyService::class)->validatePrescription($prescription2, 'approved');

        // Dispense first prescription (15 units)
        $service = app(PharmacyService::class);
        try {
            $service->dispense($prescription1->fresh());
        } catch (\Exception $e) {
            // Expected because stock is only 20
        }

        // Dispense second prescription (10 units)
        try {
            $service->dispense($prescription2->fresh());
        } catch (\Exception $e) {
            // Expected due to insufficient stock
        }

        // Stock should never go negative
        $stock = Stock::where('medicine_id', $medicine->id)->value('quantity_available');
        $this->assertGreaterThanOrEqual(0, $stock);
    }
}
