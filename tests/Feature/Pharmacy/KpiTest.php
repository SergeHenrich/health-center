<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Dispensation;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Stock;
use App\Models\User;
use App\Services\Pharmacy\KpiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KpiTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private User $doctor;
    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Kpi',
            'last_name'  => 'Test',
            'email'      => 'kpi@test.test',
            'username'   => 'kpi_test',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $this->doctor = User::factory()->create([
            'first_name' => 'Dr',
            'last_name'  => 'Kpi',
            'email'      => 'dr.kpi@test.test',
            'username'   => 'dr_kpi',
            'is_active'  => true,
        ]);

        $this->patient = Patient::factory()->create();
    }

    public function test_kpi_dashboard_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('kpi.index'))
            ->assertOk()
            ->assertSee('Tableau de bord Pharmacie');
    }

    public function test_kpi_service_returns_structured_data(): void
    {
        $service = app(KpiService::class);
        $data = $service->getDashboardData();

        $this->assertArrayHasKey('dispensation', $data);
        $this->assertArrayHasKey('stock', $data);
        $this->assertArrayHasKey('quality', $data);
        $this->assertArrayHasKey('narcotic', $data);
        $this->assertArrayHasKey('formulary', $data);
        $this->assertArrayHasKey('events', $data);
        $this->assertArrayHasKey('charts', $data);
    }

    public function test_kpi_reflects_dispensation_data(): void
    {
        Medicine::factory()->count(2)->create(['unit_price' => 1000]);
        foreach (Medicine::all() as $med) {
            Stock::create([
                'medicine_id' => $med->id,
                'quantity_available' => 50,
                'minimum_quantity' => 10,
                'maximum_quantity' => 100,
                'last_updated_at' => now(),
            ]);
        }

        $consultation = \App\Models\Consultation::factory()->create([
            'doctor_id' => $this->doctor->id,
        ]);

        $prescription = Prescription::create([
            'consultation_id' => $consultation->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'prescription_number' => 'RX-KPI-001',
            'issued_at' => now(),
            'status' => 'dispensed',
        ]);

        foreach (range(1, 3) as $i) {
            Dispensation::create([
                'prescription_id' => $prescription->id,
                'pharmacist_id' => $this->pharmacist->id,
                'dispensed_at' => now(),
            ]);
        }

        $service = app(KpiService::class);
        $data = $service->getDashboardData();

        $this->assertEquals(3, $data['dispensation']['total']);
    }

    public function test_kpi_reflects_stock_health(): void
    {
        $medicine = Medicine::factory()->create(['unit_price' => 500]);
        Stock::create([
            'medicine_id' => $medicine->id,
            'quantity_available' => 3,
            'minimum_quantity' => 10,
            'maximum_quantity' => 100,
            'last_updated_at' => now(),
        ]);

        $service = app(KpiService::class);
        $data = $service->getDashboardData();

        $this->assertEquals(1, $data['stock']['low_stock']);
    }
}
