<?php

namespace Database\Factories;

use App\Models\Consultation;
use App\Models\MedicalRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ConsultationFactory extends Factory
{
    protected $model = Consultation::class;

    public function definition(): array
    {
        return [
            'medical_record_id' => MedicalRecord::factory(),
            'doctor_id'         => User::factory(),
            'chief_complaint'   => $this->faker->sentence(),
            'status'            => 'in_progress',
            'consultation_date' => now(),
        ];
    }
}
