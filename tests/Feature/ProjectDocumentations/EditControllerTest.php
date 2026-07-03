<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Inertia\Testing\AssertableInertia as Assert;

test('project members with update permission can view edit page', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Update);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Guide',
        'type'       => ProjectDocumentationType::Link,
        'category'   => ProjectDocumentationCategory::API,
        'url'        => 'https://example.com/guide',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.edit', $projectDocumentation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('project-documentations/Edit')
            ->where('projectDocumentation.id', $projectDocumentation->id)
            ->where('projectDocumentation.title', 'API Guide')
            ->where('projectDocumentation.type', ProjectDocumentationType::Link->value)
            ->where('projectDocumentation.url', 'https://example.com/guide'));
});

test('project members without update permission cannot view edit page', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Guide',
        'type'       => ProjectDocumentationType::Link,
        'category'   => ProjectDocumentationCategory::API,
        'url'        => 'https://example.com/guide',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.edit', $projectDocumentation))
        ->assertForbidden();
});
