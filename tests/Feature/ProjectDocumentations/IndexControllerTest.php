<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use Inertia\Testing\AssertableInertia as Assert;

test('project members with view permission can view project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('project-documentations/Index')
            ->has('projectDocumentations.data', 0)
            ->where('can.create', false));
});
