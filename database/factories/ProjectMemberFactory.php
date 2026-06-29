<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectMember;
use App\Models\ProjectRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectMember>
 */
class ProjectMemberFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'project_id'      => Project::factory(),
            'user_id'         => User::factory(),
            'project_role_id' => fn (array $attributes): int => ProjectRole::factory()
                ->create(['project_id' => $attributes['project_id']])
                ->id,
        ];
    }

    public function trashed(): static
    {
        return $this->state(fn (array $attributes) => [
            'deleted_at' => now(),
        ]);
    }
}
