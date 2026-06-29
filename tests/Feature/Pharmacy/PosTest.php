<?php

namespace Tests\Feature\Pharmacy;

use App\Exceptions\InsufficientStockException;
use App\Models\Invoice;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use App\Services\Pharmacy\PosService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Medicine $medicine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->user = User::factory()->create([
            'first_name' => 'POS',
            'last_name'  => 'Test',
            'email'      => 'pos@test.test',
            'username'   => 'pos_test',
            'is_active'  => true,
        ]);
        $this->user->assignRole('pharmacist');

        $this->medicine = Medicine::factory()->create(['unit_price' => 1000]);

        Stock::create([
            'medicine_id'        => $this->medicine->id,
            'quantity_available' => 50,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 500,
            'last_updated_at'    => now(),
        ]);

        StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
            'initial_quantity'  => 50,
            'expiry_date'       => now()->addYear(),
        ]);

        Patient::factory()->create([
            'patient_code' => 'WALK-IN',
            'first_name'   => 'Client',
            'last_name'    => 'De Passage',
        ]);
    }

    private function makeCart(): array
    {
        return [
            $this->medicine->id => [
                'medicine_id'            => $this->medicine->id,
                'name'                   => $this->medicine->name,
                'form'                   => $this->medicine->form,
                'strength'               => $this->medicine->strength,
                'unit_price'             => (float) $this->medicine->unit_price,
                'quantity'               => 10,
                'requires_prescription'  => false,
            ],
        ];
    }

    public function test_pos_checkout_processes_sale(): void
    {
        $this->actingAs($this->user);

        $result = app(PosService::class)->checkout(
            $this->makeCart(),
            ['amount' => 10000, 'method' => 'cash'],
        );

        $this->assertArrayHasKey('invoice', $result);
        $this->assertArrayHasKey('payment', $result);
        $this->assertInstanceOf(Invoice::class, $result['invoice']);
        $this->assertInstanceOf(Payment::class, $result['payment']);
        $this->assertSame(1, Invoice::count());
        $this->assertSame(1, Payment::count());
        $this->assertSame(40, Stock::first()->quantity_available);
    }

    public function test_pos_checkout_rollbacks_on_stock_failure(): void
    {
        $this->actingAs($this->user);

        Stock::first()->update(['quantity_available' => 5]);

        $threwException = false;

        try {
            app(PosService::class)->checkout(
                $this->makeCart(),
                ['amount' => 10000, 'method' => 'cash'],
            );
        } catch (InsufficientStockException $e) {
            $threwException = true;
            $this->assertStringContainsString('Stock insuffisant', $e->getMessage());
        }

        $this->assertTrue($threwException, 'Expected InsufficientStockException was not thrown.');
        $this->assertSame(0, Invoice::count());
        $this->assertSame(0, Payment::count());
        $this->assertSame(5, Stock::first()->quantity_available);
    }
}
