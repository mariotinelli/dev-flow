<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectRole>
 */
class ProjectRoleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'name'       => fake()->unique()->jobTitle(),
        ];
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
