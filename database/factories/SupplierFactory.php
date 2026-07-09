<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        static $counter = 1;

        return [
            'code' => 'SUP-' . str_pad($counter++, 3, '0', STR_PAD_LEFT),
            'name' => $this->faker->company(),
            'contact_name' => $this->faker->name(),
            'email' => $this->faker->companyEmail(),
            'phone' => $this->faker->phoneNumber(),
            'address' => $this->faker->address(),
            'city' => $this->faker->randomElement(['Douala', 'Yaoundé', 'Bafoussam', 'Garoua']),
            'category' => $this->faker->randomElement(['Grossiste', 'Laboratoire', 'Distributeur', 'Fabricant']),
            'is_active' => true,
        ];
    }
}
