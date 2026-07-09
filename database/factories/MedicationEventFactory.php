<?php

namespace Database\Factories;

use App\Models\MedicationEvent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationEventFactory extends Factory
{
    protected $model = MedicationEvent::class;

    public function definition(): array
    {
        return [
            'type' => $this->faker->randomElement(['medication_error', 'adverse_drug_reaction', 'near_miss', 'quality_incident']),
            'severity' => $this->faker->randomElement(['low', 'medium', 'high', 'critical']),
            'status' => 'open',
            'description' => $this->faker->sentence(8),
            'reported_by' => User::factory(),
            'occurred_at' => now(),
        ];
    }

    public function medicationError(): static
    {
        return $this->state(fn() => ['type' => 'medication_error']);
    }

    public function adverseDrugReaction(): static
    {
        return $this->state(fn() => ['type' => 'adverse_drug_reaction']);
    }

    public function critical(): static
    {
        return $this->state(fn() => ['severity' => 'critical']);
    }

    public function resolved(): static
    {
        return $this->state(fn() => [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }
}
