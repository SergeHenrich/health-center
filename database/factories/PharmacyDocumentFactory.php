<?php

namespace Database\Factories;

use App\Models\PharmacyDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PharmacyDocumentFactory extends Factory
{
    protected $model = PharmacyDocument::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(4),
            'type' => $this->faker->randomElement(['sop', 'contract', 'regulatory', 'reference', 'other']),
            'file_path' => 'documents/' . $this->faker->uuid() . '.pdf',
            'file_name' => $this->faker->word() . '.pdf',
            'file_type' => 'application/pdf',
            'file_size' => $this->faker->numberBetween(10000, 5000000),
            'version' => '1.0',
            'uploaded_by' => User::factory(),
            'is_active' => true,
        ];
    }

    public function sop(): static
    {
        return $this->state(fn() => ['type' => 'sop']);
    }

    public function contract(): static
    {
        return $this->state(fn() => ['type' => 'contract']);
    }
}
