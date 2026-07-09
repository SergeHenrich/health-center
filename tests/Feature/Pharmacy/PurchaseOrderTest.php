<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Stock;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private PurchaseOrder $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'Cmd',
            'email'      => 'admin@order.test',
            'username'   => 'admin_order',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');

        $medicine = Medicine::factory()->create();

        $this->order = PurchaseOrder::create([
            'order_number'    => 'PO-2026-00001',
            'pharmacist_id'   => $this->admin->id,
            'supplier_name'   => 'Pharma Fournisseur',
            'status'          => 'draft',
            'total_amount'    => 5000,
            'ordered_at'      => now(),
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $this->order->id,
            'medicine_id'       => $medicine->id,
            'quantity_ordered'  => 10,
            'quantity_received' => 0,
            'unit_cost'         => 500,
        ]);
    }

    public function test_pharmacist_can_view_orders(): void
    {
        $this->actingAs($this->admin)
            ->get(route('purchase-orders.index'))
            ->assertOk()
            ->assertSee('PO-2026-00001');
    }

    public function test_pharmacist_can_view_order_create_form(): void
    {
        $this->actingAs($this->admin)
            ->get(route('purchase-orders.create'))
            ->assertOk();
    }

    public function test_order_can_be_created(): void
    {
        $medicine = Medicine::factory()->create();
        $supplier = Supplier::factory()->create(['name' => 'Nouveau Fournisseur']);

        $this->actingAs($this->admin)
            ->post(route('purchase-orders.store'), [
                'supplier_id'      => $supplier->id,
                'expected_delivery' => now()->addDays(7)->format('Y-m-d'),
                'items' => [
                    [
                        'medicine_id'      => $medicine->id,
                        'quantity_ordered' => 20,
                        'unit_cost'        => 250,
                    ],
                ],
            ])
            ->assertSessionHas('success');

        $order = PurchaseOrder::where('supplier_name', 'Nouveau Fournisseur')->first();
        $this->assertNotNull($order);
        $this->assertEquals(5000, (int) $order->total_amount);
        $this->assertCount(1, $order->items);
    }

    public function test_order_can_be_approved(): void
    {
        $this->actingAs($this->admin)
            ->post(route('purchase-orders.approve', $this->order))
            ->assertSessionHas('success');

        $this->assertEquals('approved', $this->order->fresh()->status);
        $this->assertEquals($this->admin->id, $this->order->fresh()->approved_by_id);
    }

    public function test_order_can_be_received(): void
    {
        $medicine = $this->order->items->first()->medicine;
        Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 0,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 100,
            'last_updated_at'    => now(),
        ]);

        $this->order->approve($this->admin);

        $this->actingAs($this->admin)
            ->post(route('purchase-orders.receive', $this->order))
            ->assertSessionHas('success');

        $this->assertEquals('received', $this->order->fresh()->status);
        $this->assertEquals(10, $medicine->stock->fresh()->quantity_available);
    }

    public function test_order_can_be_viewed(): void
    {
        $this->actingAs($this->admin)
            ->get(route('purchase-orders.show', $this->order))
            ->assertOk()
            ->assertSee('PO-2026-00001');
    }

    public function test_purchase_order_approve_method(): void
    {
        $user = User::factory()->create();
        $this->order->approve($user);

        $this->assertEquals('approved', $this->order->status);
        $this->assertEquals($user->id, $this->order->approved_by_id);
    }

    public function test_purchase_order_receive_method(): void
    {
        $this->order->receive();

        $this->assertEquals('received', $this->order->status);
        $this->assertNotNull($this->order->received_at);
    }
}
