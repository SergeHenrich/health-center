<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Consultation;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\PrescriptionItem;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use App\Services\PharmacyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientMedicationProfileTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Paul',
            'last_name'  => 'Pharmacien',
            'email'      => 'pharmacist_profile@test.test',
            'username'   => 'pharmacist_profile',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $this->patient = Patient::factory()->create();
    }

    public function test_pharmacist_can_view_patient_medication_profile(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('patients.medication-profile', $this->patient))
            ->assertOk()
            ->assertSee($this->patient->getFullName());
    }

    public function test_medication_profile_shows_active_prescriptions(): void
    {
        $doctor = User::factory()->create(['is_active' => true]);
        $doctor->assignRole('general_practitioner');

        $medicine = Medicine::factory()->create();
        Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 100,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 500,
            'last_updated_at'    => now(),
        ]);

        $prescription = Prescription::create([
            'consultation_id'     => Consultation::factory()->create()->id,
            'doctor_id'           => $doctor->id,
            'patient_id'          => $this->patient->id,
            'prescription_number' => 'RX-PROFILE-001',
            'issued_at'           => now(),
            'status'              => 'active',
        ]);

        PrescriptionItem::create([
            'prescription_id'     => $prescription->id,
            'medicine_id'         => $medicine->id,
            'medicine_name'       => $medicine->name,
            'dosage'              => '500mg',
            'frequency'           => '3x/jour',
            'quantity_prescribed' => 10,
            'quantity_dispensed'  => 0,
            'route'               => 'oral',
        ]);

        $this->actingAs($this->pharmacist)
            ->get(route('patients.medication-profile', $this->patient))
            ->assertOk()
            ->assertSee($prescription->prescription_number);
    }

    public function test_medication_profile_shows_dispensation_history(): void
    {
        $doctor = User::factory()->create(['is_active' => true]);
        $doctor->assignRole('general_practitioner');

        $medicine = Medicine::factory()->create();
        Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 100,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 500,
            'last_updated_at'    => now(),
        ]);

        StockBatch::factory()->create([
            'medicine_id'       => $medicine->id,
            'quantity_available' => 100,
            'initial_quantity'  => 100,
            'expiry_date'       => now()->addYear(),
        ]);

        $prescription = Prescription::create([
            'consultation_id'     => Consultation::factory()->create()->id,
            'doctor_id'           => $doctor->id,
            'patient_id'          => $this->patient->id,
            'prescription_number' => 'RX-PROFILE-002',
            'issued_at'           => now(),
            'status'              => 'pending',
        ]);

        PrescriptionItem::create([
            'prescription_id'     => $prescription->id,
            'medicine_id'         => $medicine->id,
            'medicine_name'       => $medicine->name,
            'dosage'              => '500mg',
            'frequency'           => '3x/jour',
            'quantity_prescribed' => 10,
            'quantity_dispensed'  => 0,
            'route'               => 'oral',
        ]);

        $this->actingAs($this->pharmacist);

        $validation = app(\App\Services\Pharmacy\ClinicalPharmacyService::class)
            ->validatePrescription($prescription, 'approved');
        $service = app(PharmacyService::class);
        $dispensation = $service->dispense($prescription->fresh());

        $this->actingAs($this->pharmacist)
            ->get(route('patients.medication-profile', $this->patient))
            ->assertOk();
    }
}
