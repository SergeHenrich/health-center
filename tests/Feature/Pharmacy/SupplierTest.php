<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->admin = User::factory()->create([
            'first_name' => 'Admin',
            'last_name'  => 'Supplier',
            'email'      => 'admin@supplier.test',
            'username'   => 'admin_supplier',
            'is_active'  => true,
        ]);
        $this->admin->assignRole('administrator');
    }

    public function test_supplier_list_can_be_viewed(): void
    {
        Supplier::factory()->count(3)->create();

        $this->actingAs($this->admin)
            ->get(route('suppliers.index'))
            ->assertOk();
    }

    public function test_supplier_can_be_created(): void
    {
        $this->actingAs($this->admin);

        $response = $this->post(route('suppliers.store'), [
            'code' => 'SUP-001',
            'name' => 'CamPharma SARL',
            'contact_name' => 'Jean Kamga',
            'email' => 'contact@campharma.cm',
            'phone' => '+237 655 000 000',
            'city' => 'Douala',
            'category' => 'Grossiste',
        ]);

        $response->assertSessionHas('success');

        $supplier = Supplier::where('code', 'SUP-001')->first();
        $this->assertNotNull($supplier);
        $this->assertEquals('CamPharma SARL', $supplier->name);
    }

    public function test_supplier_can_be_viewed(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin)
            ->get(route('suppliers.show', $supplier))
            ->assertOk()
            ->assertSee($supplier->name);
    }

    public function test_supplier_can_be_updated(): void
    {
        $supplier = Supplier::factory()->create(['name' => 'Ancien Nom']);

        $this->actingAs($this->admin)
            ->put(route('suppliers.update', $supplier), [
                'name' => 'Nouveau Nom',
                'is_active' => true,
            ])
            ->assertSessionHas('success');

        $this->assertEquals('Nouveau Nom', $supplier->fresh()->name);
    }

    public function test_supplier_can_be_soft_deleted(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin)
            ->delete(route('suppliers.destroy', $supplier))
            ->assertRedirect(route('suppliers.index'));

        $this->assertSoftDeleted($supplier);
    }

    public function test_contract_can_be_added_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('suppliers.contracts.store', $supplier), [
                'contract_number' => 'CTR-2026-001',
                'start_date' => now()->format('Y-m-d'),
                'end_date' => now()->addYear()->format('Y-m-d'),
                'discount_percent' => 5,
            ])
            ->assertSessionHas('success');

        $this->assertCount(1, $supplier->fresh()->contracts);
    }

    public function test_evaluation_can_be_added_to_supplier(): void
    {
        $supplier = Supplier::factory()->create();

        $this->actingAs($this->admin)
            ->post(route('suppliers.evaluations.store', $supplier), [
                'quality_score' => 8,
                'delivery_score' => 7,
                'price_score' => 6,
            ])
            ->assertSessionHas('success');

        $eval = $supplier->fresh()->evaluations->first();
        $this->assertNotNull($eval);
        $this->assertEquals(7, $eval->overall_score);
    }
}
