<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\ContractType;
use App\Enums\Seniority;
use App\Models\Developer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Developer>
 */
class DeveloperFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'       => User::factory(),
            'avatar_path'   => null,
            'role'          => fake()->jobTitle(),
            'contract_type' => fake()->randomElement(ContractType::cases()),
            'seniority'     => fake()->randomElement(Seniority::cases()),
        ];
    }
}
