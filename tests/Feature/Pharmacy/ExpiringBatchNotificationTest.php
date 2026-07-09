<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\StockBatch;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpiringBatchNotificationTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Test',
            'last_name'  => 'Pharmacist',
            'email'      => 'pharmacist_expiring@test.test',
            'username'   => 'pharmacist_expiring',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');
    }

    public function test_command_detects_expiring_batches(): void
    {
        $medicine = Medicine::factory()->create();

        StockBatch::factory()->create([
            'medicine_id'       => $medicine->id,
            'lot_number'        => 'EXP-SOON-001',
            'expiry_date'       => Carbon::now()->addDays(10),
            'quantity_available' => 100,
            'initial_quantity'  => 100,
            'status'            => 'active',
        ]);

        $this->artisan('pharmacy:check-expiring-batches', ['--days' => 30])
            ->expectsOutputToContain('Lots expirant dans moins de 30 jours')
            ->assertExitCode(0);
    }

    public function test_command_detects_expired_batches(): void
    {
        $medicine = Medicine::factory()->create();

        StockBatch::factory()->create([
            'medicine_id'       => $medicine->id,
            'lot_number'        => 'EXPIRED-001',
            'expiry_date'       => Carbon::now()->subDays(5),
            'quantity_available' => 50,
            'initial_quantity'  => 100,
            'status'            => 'active',
        ]);

        $this->artisan('pharmacy:check-expiring-batches', ['--days' => 30])
            ->expectsOutputToContain('Lots périmés encore en stock')
            ->assertExitCode(0);
    }

    public function test_command_creates_notifications_for_pharmacists(): void
    {
        $medicine = Medicine::factory()->create();

        StockBatch::factory()->create([
            'medicine_id'       => $medicine->id,
            'lot_number'        => 'NOTIF-001',
            'expiry_date'       => Carbon::now()->addDays(10),
            'quantity_available' => 100,
            'initial_quantity'  => 100,
            'status'            => 'active',
        ]);

        $this->artisan('pharmacy:check-expiring-batches', ['--days' => 30])
            ->assertExitCode(0);

        $this->assertDatabaseHas('notifications', [
            'type' => 'expiring_batches',
        ]);

        $this->assertDatabaseHas('notification_user', [
            'user_id' => $this->pharmacist->id,
        ]);
    }

    public function test_command_no_batches_found(): void
    {
        $this->artisan('pharmacy:check-expiring-batches', ['--days' => 30])
            ->expectsOutputToContain('Aucun lot proche de péremption ou périmé trouvé')
            ->assertExitCode(0);
    }
}
