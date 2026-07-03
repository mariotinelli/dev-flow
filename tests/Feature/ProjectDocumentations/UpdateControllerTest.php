<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('project members with update permission can update link project documentation metadata', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Update);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id'  => $project->id,
        'author_id'   => $user->id,
        'title'       => 'Old title',
        'description' => 'Old description.',
        'type'        => ProjectDocumentationType::Link,
        'category'    => ProjectDocumentationCategory::API,
        'url'         => 'https://example.com/old',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.update', $projectDocumentation), [
            'title'       => 'Updated title',
            'description' => 'Updated description.',
            'type'        => ProjectDocumentationType::Link->value,
            'category'    => ProjectDocumentationCategory::Architecture->value,
            'visibility'  => ProjectDocumentationVisibility::ProjectMembers->value,
            'url'         => 'https://example.com/new',
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false))
        ->assertToast('success', 'Documentação atualizada.');

    $this->assertDatabaseHas('project_documentations', [
        'id'          => $projectDocumentation->id,
        'title'       => 'Updated title',
        'description' => 'Updated description.',
        'type'        => ProjectDocumentationType::Link->value,
        'category'    => ProjectDocumentationCategory::Architecture->value,
        'url'         => 'https://example.com/new',
    ]);
});

test('project members with update permission can replace file in file project documentation', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Update);

    $oldFilePath = UploadedFile::fake()->create('old.pdf', 512)->store('project-documentations', 's3');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Old spec',
        'type'       => ProjectDocumentationType::File,
        'category'   => ProjectDocumentationCategory::Requirements,
        'file_path'  => $oldFilePath,
        'url'        => null,
    ]);

    $newFile = UploadedFile::fake()->create('new-spec.pdf', 1024);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.update', $projectDocumentation), [
            'title'       => 'Updated spec',
            'description' => 'Updated description.',
            'type'        => ProjectDocumentationType::File->value,
            'category'    => ProjectDocumentationCategory::Requirements->value,
            'visibility'  => ProjectDocumentationVisibility::ProjectMembers->value,
            'file'        => $newFile,
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false))
        ->assertToast('success', 'Documentação atualizada.');

    $projectDocumentation->refresh();

    expect($projectDocumentation->file_path)->not->toBe($oldFilePath);
    expect($projectDocumentation->title)->toBe('Updated spec');

    Storage::disk('s3')->assertMissing($oldFilePath);
    Storage::disk('s3')->assertExists($projectDocumentation->file_path);
});

test('project members can change type from link to file', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Update);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Guide',
        'type'       => ProjectDocumentationType::Link,
        'category'   => ProjectDocumentationCategory::API,
        'url'        => 'https://example.com/guide',
    ]);

    $file = UploadedFile::fake()->create('guide.pdf', 1024);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.update', $projectDocumentation), [
            'title'      => 'API Guide',
            'type'       => ProjectDocumentationType::File->value,
            'category'   => ProjectDocumentationCategory::API->value,
            'visibility' => ProjectDocumentationVisibility::ProjectMembers->value,
            'file'       => $file,
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false));

    $projectDocumentation->refresh();

    expect($projectDocumentation->file_path)->not->toBeNull();
    expect($projectDocumentation->url)->toBeNull();
    expect($projectDocumentation->type)->toBe(ProjectDocumentationType::File);

    Storage::disk('s3')->assertExists($projectDocumentation->file_path);
});

test('project members can change type from file to link', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::Update);

    $oldFilePath = UploadedFile::fake()->create('spec.pdf', 1024)->store('project-documentations', 's3');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Project spec',
        'type'       => ProjectDocumentationType::File,
        'category'   => ProjectDocumentationCategory::Requirements,
        'file_path'  => $oldFilePath,
        'url'        => null,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.update', $projectDocumentation), [
            'title'      => 'Project spec',
            'type'       => ProjectDocumentationType::Link->value,
            'category'   => ProjectDocumentationCategory::Requirements->value,
            'visibility' => ProjectDocumentationVisibility::ProjectMembers->value,
            'url'        => 'https://example.com/new-link',
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false));

    $projectDocumentation->refresh();

    expect($projectDocumentation->file_path)->toBeNull();
    expect($projectDocumentation->url)->toBe('https://example.com/new-link');
    expect($projectDocumentation->type)->toBe(ProjectDocumentationType::Link);

    Storage::disk('s3')->assertMissing($oldFilePath);
});
