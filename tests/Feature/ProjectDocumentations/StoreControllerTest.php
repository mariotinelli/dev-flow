<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;

test('project members with create permission can create link project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Create);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.store'), [
            'title'       => 'API onboarding guide',
            'description' => 'Reference material for new API contributors.',
            'type'        => ProjectDocumentationType::Link->value,
            'category'    => ProjectDocumentationCategory::API->value,
            'url'         => 'https://example.com/api-guide',
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false))
        ->assertToast('success', 'Documentação cadastrada.');

    $this->assertDatabaseHas('project_documentations', [
        'project_id'  => $project->id,
        'author_id'   => $user->id,
        'title'       => 'API onboarding guide',
        'description' => 'Reference material for new API contributors.',
        'type'        => ProjectDocumentationType::Link->value,
        'category'    => ProjectDocumentationCategory::API->value,
        'url'         => 'https://example.com/api-guide',
    ]);
});
