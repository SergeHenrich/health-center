<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\User;
use App\Services\Pharmacy\ClinicalPharmacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PharmaceuticalValidationTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private Prescription $prescription;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Paul',
            'last_name'  => 'Validateur',
            'email'      => 'pharmacist@valid.test',
            'username'   => 'pharmacist_valid',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $doctor = User::factory()->create(['is_active' => true]);
        $doctor->assignRole('general_practitioner');
        $patient = Patient::factory()->create();

        $medicine = Medicine::factory()->create([
            'formulary_status' => 'inscrit',
            'unit_price' => 500,
        ]);

        $this->prescription = Prescription::create([
            'consultation_id' => Consultation::factory()->create()->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'prescription_number' => 'RX-2026-VAL-001',
            'issued_at' => now(),
            'status' => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $this->prescription->id,
            'medicine_id' => $medicine->id,
            'medicine_name' => $medicine->name,
            'dosage' => '500mg',
            'frequency' => '3x/jour',
            'quantity_prescribed' => 10,
            'quantity_dispensed' => 0,
            'route' => 'oral',
        ]);
    }

    public function test_validation_list_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('validations.index'))
            ->assertOk()
            ->assertSee($this->prescription->prescription_number);
    }

    public function test_validation_show_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('validations.show', $this->prescription))
            ->assertOk()
            ->assertSee($this->prescription->prescription_number);
    }

    public function test_prescription_can_be_validated(): void
    {
        $this->actingAs($this->pharmacist)
            ->post(route('validations.validate', $this->prescription), [
                'status' => 'approved',
                'notes' => 'Ordonnance conforme.',
            ])
            ->assertSessionHas('success');

        $this->assertEquals('validated', $this->prescription->fresh()->status);
        $this->assertCount(1, $this->prescription->fresh()->pharmaceuticalValidations);
    }

    public function test_prescription_with_non_formulary_drug_generates_intervention(): void
    {
        $nonFormularyMedicine = Medicine::factory()->create([
            'formulary_status' => 'non_inscrit',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $this->prescription->id,
            'medicine_id' => $nonFormularyMedicine->id,
            'medicine_name' => $nonFormularyMedicine->name,
            'dosage' => '500mg',
            'frequency' => '2x/jour',
            'quantity_prescribed' => 5,
            'quantity_dispensed' => 0,
            'route' => 'oral',
        ]);

        $service = app(ClinicalPharmacyService::class);

        $this->actingAs($this->pharmacist);
        $validation = $service->validatePrescription($this->prescription, 'approved');

        $interventions = $validation->interventions;
        $hasNonFormulary = $interventions->contains('intervention_type', 'non_formulary');

        $this->assertTrue($hasNonFormulary);
    }

    public function test_prescription_with_narcotic_generates_alert(): void
    {
        $narcoticMedicine = Medicine::factory()->create([
            'is_narcotic' => true,
            'formulary_status' => 'inscrit',
        ]);

        PrescriptionItem::create([
            'prescription_id' => $this->prescription->id,
            'medicine_id' => $narcoticMedicine->id,
            'medicine_name' => $narcoticMedicine->name,
            'dosage' => '10mg',
            'frequency' => '2x/jour',
            'quantity_prescribed' => 5,
            'quantity_dispensed' => 0,
            'route' => 'oral',
        ]);

        $service = app(ClinicalPharmacyService::class);

        $this->actingAs($this->pharmacist);
        $validation = $service->validatePrescription($this->prescription, 'approved');

        $interventions = $validation->interventions;
        $hasNarcoticAlert = $interventions->contains('intervention_type', 'narcotic_alert');

        $this->assertTrue($hasNarcoticAlert);
    }
}
