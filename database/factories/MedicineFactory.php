<?php

namespace Database\Factories;

use App\Models\Medicine;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicineFactory extends Factory
{
    protected $model = Medicine::class;

    public function definition(): array
    {
        static $counter = 1;

        return [
            'code'                  => 'MED-' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
            'name'                  => $this->faker->unique()->word() . ' ' . $this->faker->randomElement(['500mg', '250mg', '1g']),
            'generic_name'          => $this->faker->optional(0.7)->word(),
            'category'              => $this->faker->randomElement(['Analgésique', 'Antibiotique', 'Antipaludique', 'Vitamine']),
            'form'                  => $this->faker->randomElement(['tablet', 'capsule', 'syrup', 'injection']),
            'strength'              => $this->faker->optional(0.8)->randomElement(['500mg', '250mg', '100mg', '1g']),
            'requires_prescription' => $this->faker->boolean(60),
            'is_active'             => true,
            'unit_price'            => $this->faker->randomFloat(2, 50, 5000),
        ];
    }
}
