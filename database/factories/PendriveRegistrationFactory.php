<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\PendriveRegistration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PendriveRegistration>
 */
class PendriveRegistrationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'serial_number' => strtoupper(fake()->bothify('USB-##??-####')),
            'pendrive_model' => fake()->randomElement(['SanDisk Ultra 64GB', 'Kingston DataTraveler 128GB', 'HP v236w 32GB']),
            'label' => 'Main Billing Counter USB',
            'status' => 'active',
            'registered_at' => now(),
            'last_verified_at' => null,
            'verification_count' => 0,
            'registered_by' => 'Super Admin',
        ];
    }
}
