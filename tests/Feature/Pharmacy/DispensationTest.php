<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Consultation;
use App\Models\Dispensation;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use App\Services\BillingService;
use App\Services\Pharmacy\ClinicalPharmacyService;
use App\Services\PharmacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DispensationTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private Prescription $prescription;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Paul',
            'last_name'  => 'Pharmacien',
            'email'      => 'pharmacist@test.test',
            'username'   => 'pharmacist_test',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

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
        $this->actingAs($this->pharmacist);
        app(ClinicalPharmacyService::class)
            ->validatePrescription($this->prescription, 'approved');
    }

    public function test_pharmacist_can_view_dispensations(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('dispensations.index'))
            ->assertOk();
    }

    public function test_dispensation_create_filters_by_validation(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('dispensations.create'))
            ->assertOk()
            ->assertDontSee($this->prescription->prescription_number);

        $this->approvePrescription();

        $this->actingAs($this->pharmacist)
            ->get(route('dispensations.create'))
            ->assertOk()
            ->assertSee($this->prescription->prescription_number);
    }

    public function test_dispensation_fails_without_validation(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('dispensations.store'), [
                'prescription_id' => $this->prescription->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_pharmacist_can_dispense_prescription(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $response = $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $response->assertSessionHas('success');

        $this->assertEquals('dispensed', $this->prescription->fresh()->status);
        $this->assertCount(1, Dispensation::all());
        $this->assertEquals(90, StockBatch::active()->where('medicine_id', $this->medicine->id)->sum('quantity_available'));
    }

    public function test_dispensation_fails_when_stock_insufficient(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        StockBatch::where('medicine_id', $this->medicine->id)->update(['quantity_available' => 5]);

        $this->actingAs($this->pharmacist)
            ->post(route('dispensations.store'), [
                'prescription_id' => $this->prescription->id,
            ])
            ->assertSessionHas('error');
    }

    public function test_pharmacist_can_dispense_with_batch_picks(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $batch = StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
            'initial_quantity'  => 50,
        ]);

        $response = $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
            'items' => [[
                'prescription_item_id' => $this->prescription->items->first()->id,
                'medicine_id'          => $this->medicine->id,
                'quantity'             => 10,
                'picks'                => [['batch_id' => $batch->id, 'quantity' => 10]],
            ]],
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(40, $batch->fresh()->quantity_available);
    }

    public function test_pharmacist_can_reverse_dispensation(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();
        $this->assertNotNull($dispensation);

        $this->delete(route('dispensations.destroy', $dispensation))
            ->assertSessionHas('success');

        $this->assertEquals(100, StockBatch::active()->where('medicine_id', $this->medicine->id)->sum('quantity_available'));
        $this->assertEquals('validated', $this->prescription->fresh()->status);
    }

    public function test_pharmacy_service_dispense_validated(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $service = app(PharmacyService::class);
        $dispensation = $service->dispense($this->prescription->fresh());

        $this->assertNotNull($dispensation);
        $this->assertEquals('dispensed', $this->prescription->fresh()->status);
    }

    public function test_pharmacy_service_reverse(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $service = app(PharmacyService::class);
        $dispensation = $service->dispense($this->prescription->fresh());

        $service->reverseDispensation($dispensation);

        $this->assertEquals(100, $this->medicine->stock->fresh()->quantity_available);
        $this->assertEquals('validated', $this->prescription->fresh()->status);
    }

    public function test_prescription_item_remaining_quantity(): void
    {
        $item = $this->prescription->items->first();
        $this->assertEquals(10, $item->getRemainingQuantity());

        $item->update(['quantity_dispensed' => 4]);
        $this->assertEquals(6, $item->fresh()->getRemainingQuantity());
    }

    public function test_dispensation_form_hides_unvalidated_prescriptions(): void
    {
        $this->actingAs($this->pharmacist);

        $response = $this->get(route('dispensations.create'));
        $response->assertOk();
        $response->assertDontSee($this->prescription->prescription_number);
    }

    public function test_dispensation_creates_invoice(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();
        $this->assertNotNull($dispensation);

        $invoice = $dispensation->fresh()->invoice;
        $this->assertNotNull($invoice, 'Aucune facture créée pour la dispensation.');
        $this->assertEquals($dispensation->id, $invoice->invoiceable_id);
        $this->assertEquals(Dispensation::class, $invoice->invoiceable_type);
        $this->assertEquals($this->prescription->patient_id, $invoice->patient_id);

        $this->assertCount(1, $invoice->items);
        $this->assertStringContainsString($this->medicine->name, $invoice->items->first()->description);
        $this->assertEquals(10, $invoice->items->first()->quantity);
        $this->assertEquals(500, $invoice->items->first()->unit_price);

        $expectedTotal = 10 * 500;
        $this->assertEquals($expectedTotal, (int) $invoice->total_amount);
        $this->assertEquals('issued', $invoice->status);
    }

    public function test_dispensation_show_displays_invoice(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();

        $this->get(route('dispensations.show', $dispensation))
            ->assertOk()
            ->assertSee($dispensation->invoice->invoice_number);
    }

    public function test_dispensation_index_shows_invoice_status(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ]);

        $dispensation = Dispensation::first();

        $this->get(route('dispensations.index'))
            ->assertOk()
            ->assertSee(strtoupper($dispensation->invoice->status));
    }

    public function test_dispensation_rollbacks_on_invoice_failure(): void
    {
        $this->actingAs($this->pharmacist);
        $this->approvePrescription();

        $this->mock(BillingService::class, function ($mock) {
            $mock->shouldReceive('generateInvoice')
                ->once()
                ->andThrow(new \RuntimeException('Échec simulation génération facture'));
        });

        $this->post(route('dispensations.store'), [
            'prescription_id' => $this->prescription->id,
        ])->assertSessionHas('error');

        $this->assertCount(0, Dispensation::all());
        $this->assertCount(0, Invoice::all());
        $this->assertEquals(100, StockBatch::active()->where('medicine_id', $this->medicine->id)->sum('quantity_available'));
    }
}
