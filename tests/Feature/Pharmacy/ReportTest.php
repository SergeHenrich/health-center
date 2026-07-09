<?php

namespace Tests\Feature\Pharmacy;

use App\Models\Medicine;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportTest extends TestCase
{
    use RefreshDatabase;

    private User $pharmacist;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleSeeder::class);

        $this->pharmacist = User::factory()->create([
            'first_name' => 'Paul',
            'last_name'  => 'Pharmacien',
            'email'      => 'pharmacist_report@test.test',
            'username'   => 'pharmacist_report',
            'is_active'  => true,
        ]);
        $this->pharmacist->assignRole('pharmacist');
    }

    public function test_pharmacist_can_view_stock_report(): void
    {
        $medicine = Medicine::factory()->create();
        StockBatch::factory()->count(3)->create(['medicine_id' => $medicine->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('reports.stock'))
            ->assertOk();
    }

    public function test_pharmacist_can_view_consumption_report(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('reports.consumption'))
            ->assertOk();
    }

    public function test_stock_report_shows_totals(): void
    {
        $medicine = Medicine::factory()->create();
        StockBatch::factory()->count(2)->create([
            'medicine_id'       => $medicine->id,
            'quantity_available' => 100,
            'initial_quantity'  => 200,
        ]);

        $this->actingAs($this->pharmacist)
            ->get(route('reports.stock'))
            ->assertOk();
    }

    public function test_pharmacist_can_export_stock_pdf(): void
    {
        $medicine = Medicine::factory()->create();
        StockBatch::factory()->create(['medicine_id' => $medicine->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('reports.stock.pdf'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pharmacist_can_export_stock_excel(): void
    {
        $medicine = Medicine::factory()->create();
        StockBatch::factory()->create(['medicine_id' => $medicine->id]);

        $this->actingAs($this->pharmacist)
            ->get(route('reports.stock.excel'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    public function test_pharmacist_can_export_consumption_pdf(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('reports.consumption.pdf'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');
    }

    public function test_pharmacist_can_export_consumption_excel(): void
    {
        $this->actingAs($this->pharmacist)
            ->get(route('reports.consumption.excel'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }
}
