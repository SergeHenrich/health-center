<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MedicineManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'Test',
            'email'      => 'admin@pharmacy.test',
            'username'   => 'admin_pharmacy',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');
    }

    public function test_admin_can_view_medicine_list(): void
    {
        Medicine::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('medicines.index'))
            ->assertOk();
    }

    public function test_medicine_can_be_created_with_stock(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('medicines.store'), [
            'code'                  => 'MED-TEST-001',
            'name'                  => 'Paracétamol Test',
            'form'                  => 'tablet',
            'unit_price'            => 100,
            'min_stock'             => 15,
            'requires_prescription' => false,
        ]);

        $response->assertRedirect(route('medicines.index'));

        $medicine = Medicine::where('code', 'MED-TEST-001')->first();
        $this->assertNotNull($medicine);
        $this->assertEquals('Paracétamol Test', $medicine->name);

        $stock = $medicine->stock;
        $this->assertNotNull($stock);
        $this->assertEquals(0, $stock->quantity_available);
        $this->assertEquals(15, $stock->minimum_quantity);
    }

    public function test_medicine_can_be_updated(): void
    {
        $medicine = Medicine::factory()->create(['name' => 'Ancien Nom']);

        $this->actingAs($this->admin)
            ->put(route('medicines.update', $medicine), [
                'name'                  => 'Nouveau Nom',
                'form'                  => 'capsule',
                'unit_price'            => 200,
                'requires_prescription' => true,
            ])
            ->assertSessionHas('success');

        $this->assertEquals('Nouveau Nom', $medicine->fresh()->name);
    }

    public function test_medicine_can_be_soft_deleted(): void
    {
        $medicine = Medicine::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('medicines.destroy', $medicine))
            ->assertRedirect(route('medicines.index'));

        $this->assertSoftDeleted($medicine);
    }

    public function test_medicine_search_scope(): void
    {
        Medicine::factory()->create(['name' => 'Doliprane', 'code' => 'MED-001']);
        Medicine::factory()->create(['name' => 'Efferalgan', 'code' => 'MED-002']);

        $results = Medicine::search('Doliprane')->get();
        $this->assertCount(1, $results);
        $this->assertEquals('Doliprane', $results->first()->name);
    }

    public function test_medicine_active_scope(): void
    {
        Medicine::factory()->create(['name' => 'Actif', 'is_active' => true]);
        Medicine::factory()->create(['name' => 'Inactif', 'is_active' => false]);

        $this->assertCount(1, Medicine::active()->get());
    }

    public function test_medicine_stock_methods(): void
    {
        $medicine = Medicine::factory()->create();
        Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 5,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 100,
            'last_updated_at'    => now(),
        ]);

        $medicine->load('stock');

        $this->assertEquals(5, $medicine->getCurrentStock());
        $this->assertTrue($medicine->isLowStock());
        $this->assertFalse($medicine->isOutOfStock());

        $medicine->stock->update(['quantity_available' => 0]);
        $medicine->refresh();

        $this->assertTrue($medicine->isOutOfStock());
    }

    public function test_unauthenticated_user_cannot_access_pharmacy(): void
    {
        $this->get(route('medicines.index'))->assertRedirect(route('login'));
    }
}
