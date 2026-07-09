<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseStock;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Warehouse $warehouse;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'Warehouse',
            'email'      => 'admin@wh.test',
            'username'   => 'admin_wh',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');

        $this->warehouse = Warehouse::create([
            'code' => 'WH-CENTRAL',
            'name' => 'Pharmacie Centrale',
            'type' => 'central',
            'location' => 'RDC Bâtiment A',
            'is_active' => true,
        ]);
    }

    public function test_warehouse_list_can_be_viewed(): void
    {
        $this->actingAs($this->admin)
            ->get(route('warehouses.index'))
            ->assertOk()
            ->assertSee('Pharmacie Centrale');
    }

    public function test_warehouse_can_be_created(): void
    {
        $this->actingAs($this->admin)
            ->post(route('warehouses.store'), [
                'code' => 'WH-URG',
                'name' => 'Pharmacie Urgences',
                'type' => 'emergency',
                'location' => 'RDC Urgences',
            ])
            ->assertSessionHas('success');

        $this->assertDatabaseHas('warehouses', ['code' => 'WH-URG']);
    }

    public function test_warehouse_can_be_updated(): void
    {
        $this->actingAs($this->admin)
            ->put(route('warehouses.update', $this->warehouse), [
                'name' => 'Pharmacie Centrale Rénovée',
                'type' => 'central',
                'is_active' => true,
            ])
            ->assertSessionHas('success');

        $this->assertEquals('Pharmacie Centrale Rénovée', $this->warehouse->fresh()->name);
    }

    public function test_warehouse_stock_can_be_viewed(): void
    {
        $medicine = Medicine::factory()->create();
        WarehouseStock::create([
            'warehouse_id' => $this->warehouse->id,
            'medicine_id' => $medicine->id,
            'quantity_available' => 100,
            'minimum_quantity' => 10,
            'maximum_quantity' => 500,
        ]);

        $this->actingAs($this->admin)
            ->get(route('warehouses.show', $this->warehouse))
            ->assertOk()
            ->assertSee($medicine->name);
    }

    public function test_stock_can_be_transferred_between_warehouses(): void
    {
        $medicine = Medicine::factory()->create();

        $warehouseFrom = $this->warehouse;
        $warehouseTo = Warehouse::create([
            'code' => 'WH-UNIT',
            'name' => 'Unité de Soins',
            'type' => 'unit_care',
            'is_active' => true,
        ]);

        WarehouseStock::create([
            'warehouse_id' => $warehouseFrom->id,
            'medicine_id' => $medicine->id,
            'quantity_available' => 50,
            'minimum_quantity' => 10,
            'maximum_quantity' => 200,
        ]);

        $this->actingAs($this->admin)
            ->post(route('warehouses.transfer'), [
                'from_warehouse_id' => $warehouseFrom->id,
                'to_warehouse_id' => $warehouseTo->id,
                'medicine_id' => $medicine->id,
                'quantity' => 20,
                'reason' => 'Réapprovisionnement',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(30, $warehouseFrom->stocks()->first()->quantity_available);
        $this->assertEquals(20, $warehouseTo->stocks()->first()->quantity_available);
    }

    public function test_warehouse_stock_low_scope(): void
    {
        $medicine = Medicine::factory()->create();
        $stock = WarehouseStock::create([
            'warehouse_id' => $this->warehouse->id,
            'medicine_id' => $medicine->id,
            'quantity_available' => 5,
            'minimum_quantity' => 10,
            'maximum_quantity' => 100,
        ]);

        $this->assertTrue($stock->isLow());
    }

    public function test_warehouse_stock_adjust_in(): void
    {
        $medicine = Medicine::factory()->create();
        $stock = WarehouseStock::create([
            'warehouse_id' => $this->warehouse->id,
            'medicine_id' => $medicine->id,
            'quantity_available' => 30,
            'minimum_quantity' => 10,
            'maximum_quantity' => 100,
        ]);

        $stock->addStock(20);
        $this->assertEquals(50, $stock->fresh()->quantity_available);
    }

    public function test_warehouse_stock_adjust_out(): void
    {
        $medicine = Medicine::factory()->create();
        $stock = WarehouseStock::create([
            'warehouse_id' => $this->warehouse->id,
            'medicine_id' => $medicine->id,
            'quantity_available' => 30,
            'minimum_quantity' => 10,
            'maximum_quantity' => 100,
        ]);

        $stock->removeStock(10);
        $this->assertEquals(20, $stock->fresh()->quantity_available);
    }
}
