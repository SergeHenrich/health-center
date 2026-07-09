<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockObserverTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);
    }

    public function test_low_stock_creates_notification(): void
    {
        $pharmacist = User::factory()->create([
            'first_name' => 'Pharmacien',
            'last_name'  => 'Obs',
            'email'      => 'pharmacist@obs.test',
            'username'   => 'pharmacist_obs',
            'is_active'  => true,
        ]);
        $pharmacist->assignRole('pharmacist');

        $medicine = Medicine::factory()->create(['name' => 'Test Med']);
        $stock = Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 50,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 100,
            'last_updated_at'    => now(),
        ]);

        $stock->update(['quantity_available' => 5]);

        $notification = Notification::where('title', 'like', '%Test Med%')->first();
        $this->assertNotNull($notification);
        $this->assertTrue(
            $notification->users()->where('user_id', $pharmacist->id)->exists()
        );
    }

    public function test_normal_stock_does_not_create_notification(): void
    {
        $medicine = Medicine::factory()->create();
        $stock = Stock::create([
            'medicine_id'        => $medicine->id,
            'quantity_available' => 50,
            'minimum_quantity'   => 10,
            'maximum_quantity'   => 100,
            'last_updated_at'    => now(),
        ]);

        $stock->update(['quantity_available' => 60]);

        $this->assertEquals(0, Notification::count());
    }
}
