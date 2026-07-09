<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\User;
use App\Services\Pharmacy\NarcoticService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NarcoticRegisterTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Paul',
            'last_name'  => 'Narcotique',
            'email'      => 'pharmacist@narc.test',
            'username'   => 'pharmacist_narc',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $this->medicine = Medicine::factory()->create([
            'is_narcotic' => true,
            'unit_price' => 5000,
        ]);

        Stock::create([
            'medicine_id' => $this->medicine->id,
            'quantity_available' => 100,
            'minimum_quantity' => 5,
            'maximum_quantity' => 200,
            'last_updated_at' => now(),
        ]);
    }

    public function test_narcotic_register_list_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('narcotics.index'))
            ->assertOk()
            ->assertSee($this->medicine->name);
    }

    public function test_narcotic_medicine_detail_can_be_viewed(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('narcotics.show', $this->medicine))
            ->assertOk()
            ->assertSee($this->medicine->name);
    }

    public function test_narcotic_receipt_can_be_recorded(): void
    {
        $this->actingAs($this->pharmacist);

        $service = app(NarcoticService::class);
        $entry = $service->recordEntry($this->medicine, [
            'quantity_in' => 50,
            'lot_number' => 'LOT-NARC-001',
            'notes' => 'Réception hebdomadaire',
        ]);

        $this->assertNotNull($entry);
        $this->assertEquals(50, $entry->balance_after);
        $this->assertEquals(150, $this->medicine->stock->fresh()->quantity_available);
    }

    public function test_narcotic_dispensation_can_be_recorded(): void
    {
        $this->actingAs($this->pharmacist);

        $service = app(NarcoticService::class);

        $service->recordEntry($this->medicine, [
            'quantity_in' => 100,
            'lot_number' => 'LOT-001',
        ]);

        $entry = $service->recordEntry($this->medicine, [
            'quantity_out' => 10,
            'prescriber_name' => 'Dr. Ngono',
            'prescription_number' => 'RX-NARC-001',
            'lot_number' => 'LOT-001',
        ]);

        $this->assertNotNull($entry);
        $this->assertEquals(90, $entry->balance_after);
    }

    public function test_narcotic_current_balance(): void
    {
        $this->actingAs($this->pharmacist);

        $service = app(NarcoticService::class);

        $service->recordEntry($this->medicine, ['quantity_in' => 100]);
        $service->recordEntry($this->medicine, ['quantity_in' => 50]);
        $service->recordEntry($this->medicine, ['quantity_out' => 30]);

        $balance = $service->getCurrentBalance($this->medicine);
        $this->assertEquals(120, $balance);
    }

    public function test_narcotic_medicines_scope(): void
    {
        Medicine::factory()->count(3)->create(['is_narcotic' => true]);
        Medicine::factory()->count(2)->create(['is_narcotic' => false]);

        $service = app(NarcoticService::class);
        $narcotics = $service->getNarcoticMedicines();

        $this->assertCount(4, $narcotics);
    }
}
