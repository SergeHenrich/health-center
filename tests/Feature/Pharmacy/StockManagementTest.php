<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private Stock $stock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'Stock',
            'email'      => 'admin@stock.test',
            'username'   => 'admin_stock',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');

        $medicine = Medicine::factory()->create();
        $this->stock = Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 50,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 100,
            'last_updated_at'    => now(),
        ]);
    }

    public function test_stock_list_can_be_viewed(): void
    {
        $this->actingAs($this->admin)
            ->get(route('stock.index'))
            ->assertOk();
    }

    public function test_stock_can_be_adjusted_in(): void
    {
        $this->actingAs($this->admin)
            ->post(route('stock.adjust', $this->stock), [
                'type'     => 'in',
                'quantity' => 10,
                'reason'   => 'Réapprovisionnement',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(60, $this->stock->fresh()->quantity_available);
        $this->assertCount(1, StockMovement::where('stock_id', $this->stock->id)->get());
    }

    public function test_stock_can_be_adjusted_out(): void
    {
        $this->actingAs($this->admin)
            ->post(route('stock.adjust', $this->stock), [
                'type'     => 'out',
                'quantity' => 20,
                'reason'   => 'Retour fournisseur',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(30, $this->stock->fresh()->quantity_available);
    }

    public function test_stock_out_fails_when_insufficient(): void
    {
        $this->actingAs($this->admin)
            ->post(route('stock.adjust', $this->stock), [
                'type'     => 'out',
                'quantity' => 999,
                'reason'   => 'Trop',
            ])
            ->assertSessionHas('error');

        $this->assertEquals(50, $this->stock->fresh()->quantity_available);
    }

    public function test_stock_adjustment_sets_value_directly(): void
    {
        $this->actingAs($this->admin)
            ->post(route('stock.adjust', $this->stock), [
                'type'     => 'adjustment',
                'quantity' => 25,
                'reason'   => 'Inventaire',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(25, $this->stock->fresh()->quantity_available);
    }

    public function test_stock_add_stock_method(): void
    {
        $this->stock->addStock(30);
        $this->assertEquals(80, $this->stock->quantity_available);
    }

    public function test_stock_remove_stock_method(): void
    {
        $this->stock->removeStock(15);
        $this->assertEquals(35, $this->stock->quantity_available);
    }

    public function test_stock_is_low_when_below_minimum(): void
    {
        $this->stock->update(['quantity_available' => 5]);
        $this->assertTrue($this->stock->fresh()->isLow());

        $this->stock->update(['quantity_available' => 50]);
        $this->assertFalse($this->stock->fresh()->isLow());
    }

    public function test_stock_low_stock_scope(): void
    {
        Stock::where('id', $this->stock->id)->update(['quantity_available' => 5]);
        $this->assertCount(1, Stock::lowStock()->get());

        Stock::where('id', $this->stock->id)->update(['quantity_available' => 50]);
        $this->assertCount(0, Stock::lowStock()->get());
    }
}
