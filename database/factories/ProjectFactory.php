<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Enums\ProjectVisibility;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name'              => str($name)->title()->toString(),
            'key'               => Str::upper(fake()->unique()->bothify('???##')),
            'description'       => fake()->optional()->paragraph(),
            'parent_project_id' => null,
            'color'             => fake()->hexColor(),
            'status'            => fake()->randomElement(ProjectStatus::cases()),
            'visibility'        => fake()->randomElement(ProjectVisibility::cases()),
            'starts_at'         => fake()->optional()->dateTimeBetween('-1 month', '+1 month'),
            'due_at'            => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
        ];
    }
}
