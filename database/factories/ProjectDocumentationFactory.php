<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectDocumentation>
 */
class ProjectDocumentationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title'       => fake()->sentence(3),
            'description' => fake()->optional()->paragraph(),
            'type'        => ProjectDocumentationType::Link,
            'category'    => ProjectDocumentationCategory::Other,
            'visibility'  => ProjectDocumentationVisibility::ProjectMembers,
            'url'         => fake()->url(),
        ];
    }
}
