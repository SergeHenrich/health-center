<?php

namespace Database\Factories;

use App\Models\Medicine;
use App\Models\StockBatch;
use Illuminate\Database\Eloquent\Factories\Factory;

class StockBatchFactory extends Factory
{
    protected $model = StockBatch::class;

    public function definition(): array
    {
        return [
            'medicine_id' => Medicine::factory(),
            'lot_number' => strtoupper($this->faker->bothify('LOT-####-??')),
            'expiry_date' => $this->faker->dateTimeBetween('+1 month', '+2 years')->format('Y-m-d'),
            'quantity_available' => $this->faker->numberBetween(10, 500),
            'initial_quantity' => 500,
            'status' => 'active',
            'unit_cost' => $this->faker->randomFloat(2, 100, 5000),
            'received_at' => now(),
        ];
    }

    public function expired(): static
    {
        return $this->state(fn() => [
            'expiry_date' => now()->subDays($this->faker->numberBetween(1, 180))->format('Y-m-d'),
        ]);
    }

    public function depleted(): static
    {
        return $this->state(fn() => [
            'quantity_available' => 0,
            'status' => 'depleted',
        ]);
    }
}
