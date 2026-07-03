<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Inertia\Testing\AssertableInertia as Assert;

test('project members with view permission can view link project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API onboarding guide',
        'type'       => ProjectDocumentationType::Link,
        'category'   => ProjectDocumentationCategory::API,
        'url'        => 'https://example.com/api-guide',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.show', $projectDocumentation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('project-documentations/Show')
            ->where('projectDocumentation.id', $projectDocumentation->id)
            ->where('projectDocumentation.title', 'API onboarding guide')
            ->where('projectDocumentation.type', ProjectDocumentationType::Link->value)
            ->where('projectDocumentation.category', ProjectDocumentationCategory::API->value)
            ->where('projectDocumentation.url', 'https://example.com/api-guide'));
});
