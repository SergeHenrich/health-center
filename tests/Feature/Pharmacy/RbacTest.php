<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RbacTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;
    private User $preparateur;
    private User $stockManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Pharma', 'last_name' => 'Adj', 'email' => 'pharma@test.test',
            'username' => 'pharma_test', 'is_active' => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $this->preparateur = User::factory()->create([
            'first_name' => 'Prep', 'last_name' => 'Test', 'email' => 'prep@test.test',
            'username' => 'prep_test', 'is_active' => true,
        ]);
        $this->preparateur->assignRole('preparateur');

        $this->stockManager = User::factory()->create([
            'first_name' => 'Stock', 'last_name' => 'Mgr', 'email' => 'stock@test.test',
            'username' => 'stock_test', 'is_active' => true,
        ]);
        $this->stockManager->assignRole('stock_manager');

        $medicine = Medicine::factory()->create(['unit_price' => 500]);
        Stock::create([
            'medicine_id' => $medicine->id, 'quantity_available' => 100,
            'minimum_quantity' => 10, 'maximum_quantity' => 500, 'last_updated_at' => now(),
        ]);
        StockBatch::factory()->create([
            'medicine_id' => $medicine->id, 'quantity_available' => 100,
            'initial_quantity' => 100, 'expiry_date' => now()->addYear(),
        ]);

        Patient::factory()->create([
            'patient_code' => 'WALK-IN', 'first_name' => 'Client', 'last_name' => 'De Passage',
        ]);
    }

    // ── Role seeding ├─────────────────────────────────────

    public function test_preparateur_has_correct_permissions(): void
    {
        $role = Role::findByName('preparateur');
        $perms = $role->permissions->pluck('name')->toArray();

        $this->assertContains('pharmacy.pos.sell', $perms);
        $this->assertContains('pharmacy.stock.view', $perms);
        $this->assertContains('pharmacy.stock.receive', $perms);
        $this->assertContains('pharmacy.stock.inventory', $perms);
        $this->assertContains('pharmacy.patient.view', $perms);
        $this->assertContains('patients.view', $perms);
        $this->assertContains('patients.create', $perms);
        $this->assertContains('patients.edit', $perms);

        $this->assertNotContains('pharmacy.dispense', $perms);
        $this->assertNotContains('pharmacy.validation', $perms);
        $this->assertNotContains('pharmacy.narcotics', $perms);
        $this->assertNotContains('pharmacy.pos.annul', $perms);
        $this->assertNotContains('pharmacy.pos.discount', $perms);
        $this->assertNotContains('pharmacy.price.edit', $perms);
    }

    public function test_stock_manager_has_correct_permissions(): void
    {
        $role = Role::findByName('stock_manager');
        $perms = $role->permissions->pluck('name')->toArray();

        $this->assertContains('pharmacy.stock.view', $perms);
        $this->assertContains('pharmacy.stock.edit', $perms);
        $this->assertContains('pharmacy.stock.receive', $perms);
        $this->assertContains('pharmacy.stock.inventory', $perms);
        $this->assertContains('pharmacy.orders.create', $perms);

        $this->assertNotContains('pharmacy.pos.sell', $perms);
        $this->assertNotContains('pharmacy.dispense', $perms);
        $this->assertNotContains('pharmacy.validation', $perms);
        $this->assertNotContains('pharmacy.suppliers.manage', $perms);
        $this->assertNotContains('pharmacy.patient.view', $perms);
    }

    // ── Route access ──────────────────────────────────────

    public function test_preparateur_can_access_pos(): void
    {
        $this->actingAs($this->preparateur)
            ->get(route('pharmacy.pos.index'))
            ->assertOk();
    }

    public function test_preparateur_can_access_stock(): void
    {
        $this->actingAs($this->preparateur)
            ->get(route('stock.index'))
            ->assertOk();
    }

    public function test_preparateur_cannot_access_dispensation(): void
    {
        $this->actingAs($this->preparateur)
            ->get(route('dispensations.index'))
            ->assertStatus(403);
    }

    public function test_preparateur_cannot_access_validation(): void
    {
        $this->actingAs($this->preparateur)
            ->get(route('validations.index'))
            ->assertStatus(403);
    }

    public function test_preparateur_cannot_access_narcotics(): void
    {
        $this->actingAs($this->preparateur)
            ->get(route('narcotics.index'))
            ->assertStatus(403);
    }

    public function test_stock_manager_can_access_stock(): void
    {
        $this->actingAs($this->stockManager)
            ->get(route('stock.index'))
            ->assertOk();
    }

    public function test_stock_manager_can_access_purchase_orders(): void
    {
        $this->actingAs($this->stockManager)
            ->get(route('purchase-orders.index'))
            ->assertOk();
    }

    public function test_stock_manager_cannot_access_pos(): void
    {
        $this->actingAs($this->stockManager)
            ->post(route('pharmacy.pos.checkout'), [
                'method' => 'cash', 'amount' => 100,
            ])
            ->assertStatus(403);
    }

    public function test_stock_manager_cannot_access_dispensation(): void
    {
        $this->actingAs($this->stockManager)
            ->get(route('dispensations.index'))
            ->assertStatus(403);
    }

    public function test_pharmacist_can_access_all_pharmacy_sections(): void
    {
        $this->actingAs($this->pharmacist);

        $this->get(route('pharmacy.pos.index'))->assertOk();
        $this->get(route('stock.index'))->assertOk();
        $this->get(route('dispensations.index'))->assertOk();
        $this->get(route('validations.index'))->assertOk();
        $this->get(route('narcotics.index'))->assertOk();
        $this->get(route('suppliers.index'))->assertOk();
    }
}
