<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class MedicalRecordFactory extends Factory
{
    protected $model = \App\Models\MedicalRecord::class;

    public function definition(): array
    {
        static $counter = 1;

        return [
            'patient_id'           => \App\Models\Patient::factory(),
            'record_number'        => 'MR-' . str_pad($counter++, 6, '0', STR_PAD_LEFT),
            'blood_type'           => $this->faker->randomElement(['A+','A-','B+','B-','AB+','AB-','O+','O-', null]),
            'height_cm'            => $this->faker->randomFloat(2, 140, 200),
            'weight_kg'            => $this->faker->randomFloat(2, 40, 120),
            'chronic_diseases'     => $this->faker->optional(0.2)->randomElement(['Hypertension', 'Diabète type 2', 'Asthme']),
            'allergies'            => $this->faker->optional(0.15)->randomElement(['Pénicilline', 'Arachides', 'Aucune connue']),
            'family_history'       => $this->faker->optional(0.3)->sentence(),
            'surgical_history'     => $this->faker->optional(0.2)->sentence(),
            'current_medications'  => $this->faker->optional(0.25)->word(),
            'notes'                => null,
        ];
    }
}
