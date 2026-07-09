<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\Stock;
use App\Models\StockBatch;
use App\Models\User;
use App\Services\Pharmacy\BatchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BatchTest extends TestCase
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
            'last_name'  => 'Pharmacien',
            'email'      => 'pharmacist_batch@test.test',
            'username'   => 'pharmacist_batch',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');

        $this->medicine = Medicine::factory()->create();
        Stock::create([
            'medicine_id'        => $this->medicine->id,
            'quantity_available' => 500,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 500,
            'last_updated_at'    => now(),
        ]);
    }

    public function test_pharmacist_can_view_batches(): void
    {
        StockBatch::factory()->create(['medicine_id' => $this->medicine->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('batches.index'))
            ->assertOk();
    }

    public function test_pharmacist_can_view_batch_details(): void
    {
        $batch = StockBatch::factory()->create(['medicine_id' => $this->medicine->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('batches.show', $batch))
            ->assertOk()
            ->assertSee($batch->lot_number);
    }

    public function test_batch_service_receive(): void
    {
        $this->actingAs($this->pharmacist);
        $service = app(BatchService::class);
        $batch = $service->receiveBatch(
            $this->medicine,
            [
                'lot_number' => 'LOT-TEST-001',
                'quantity' => 200,
                'unit_cost' => 1500.0,
                'expiry_date' => now()->addYear(),
            ],
        );

        $this->assertNotNull($batch);
        $this->assertEquals(200, $batch->quantity_available);
        $this->assertEquals('active', $batch->status);
    }

    public function test_batch_service_pick_fefo(): void
    {
        StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
            'expiry_date'       => now()->addMonths(6),
        ]);
        $soonBatch = StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
            'expiry_date'       => now()->addMonth(),
        ]);

        $service = app(BatchService::class);
        $picks = $service->pickBatches($this->medicine, 30);

        $this->assertCount(1, $picks);
        $this->assertEquals($soonBatch->id, $picks[0]['batch']->id);
    }

    public function test_batch_service_write_off(): void
    {
        $this->actingAs($this->pharmacist);

        $batch = StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 100,
        ]);

        $service = app(BatchService::class);
        $service->writeOff($batch, 10, 'damaged');

        $this->assertEquals(90, $batch->fresh()->quantity_available);
        $this->assertDatabaseHas('stock_movements', [
            'medicine_id'  => $this->medicine->id,
            'type'         => 'out',
            'reason'       => 'Mise au rebut (damaged)',
        ]);
    }

    public function test_batch_write_off_via_http(): void
    {
        $batch = StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
        ]);

        $this->actingAs($this->pharmacist)
            ->post(route('batches.write-off', $batch), [
                'quantity' => 10,
                'reason' => 'expired',
            ])
            ->assertSessionHas('success');

        $this->assertEquals(40, $batch->fresh()->quantity_available);
    }

    public function test_batch_return_to_supplier_via_http(): void
    {
        $batch = StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
        ]);

        $this->actingAs($this->pharmacist)
            ->post(route('batches.return-supplier', $batch), [
                'quantity' => 20,
            ])
            ->assertSessionHas('success');

        $this->assertEquals(30, $batch->fresh()->quantity_available);
    }

    public function test_batch_get_expiring_batches(): void
    {
        StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
            'expiry_date'       => now()->addDays(30),
        ]);
        StockBatch::factory()->create([
            'medicine_id'       => $this->medicine->id,
            'quantity_available' => 50,
            'expiry_date'       => now()->addYear(),
        ]);

        $service = app(BatchService::class);
        $expiring = $service->getExpiringBatches(60);

        $this->assertCount(1, $expiring);
    }
}
