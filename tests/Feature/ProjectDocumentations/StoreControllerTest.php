<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('project members with create permission can create file project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Create);

    Storage::fake('s3');

    $file = UploadedFile::fake()->create('spec.pdf', 1024);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.store'), [
            'title'       => 'Project spec',
            'description' => 'The full project specification.',
            'type'        => ProjectDocumentationType::File->value,
            'category'    => ProjectDocumentationCategory::Requirements->value,
            'file'        => $file,
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false));

    $this->assertDatabaseHas('project_documentations', [
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Project spec',
        'type'       => ProjectDocumentationType::File->value,
        'category'   => ProjectDocumentationCategory::Requirements->value,
        'url'        => null,
    ]);

    $documentation = ProjectDocumentation::firstWhere('title', 'Project spec');
    expect($documentation->file_path)->not->toBeNull();

    Storage::disk('s3')->assertExists($documentation->file_path);
});
