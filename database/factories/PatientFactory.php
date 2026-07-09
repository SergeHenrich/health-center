<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = \App\Models\Patient::class;

    public function definition(): array
    {
        static $counter = 1;

        $gender = $this->faker->randomElement(['male', 'female']);
        $year   = now()->year;
        $code   = sprintf('PAT-%d-%05d', $year, $counter++);

        return [
            'user_id'                  => null,
            'patient_code'             => $code,
            'first_name'               => $gender === 'male' ? $this->faker->firstNameMale() : $this->faker->firstNameFemale(),
            'last_name'                => $this->faker->lastName(),
            'date_of_birth'            => $this->faker->dateTimeBetween('-90 years', '-1 year')->format('Y-m-d'),
            'gender'                   => $gender,
            'blood_type'               => $this->faker->randomElement(['A+','A-','B+','B-','AB+','AB-','O+','O-', null]),
            'address'                  => $this->faker->address(),
            'city'                     => $this->faker->randomElement(['Douala', 'Yaoundé', 'Bafoussam', 'Garoua', 'Bamenda']),
            'phone'                    => '6' . $this->faker->numerify('########'),
            'email'                    => $this->faker->optional(0.6)->safeEmail(),
            'emergency_contact_name'   => $this->faker->name(),
            'emergency_contact_phone'  => '6' . $this->faker->numerify('########'),
            'insurance_number'         => $this->faker->optional(0.4)->numerify('INS-#######'),
            'insurance_provider'       => $this->faker->optional(0.4)->randomElement(['CNPS', 'AXA', 'Saham', 'Activa']),
            'is_active'                => true,
        ];
    }
}
