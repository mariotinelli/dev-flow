<?php

declare(strict_types = 1);

use App\Enums\Permissions\Project\DocumentationPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Models\ProjectDocumentation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('project members with delete permission can delete link project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::Delete);

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
        ->delete(route('project.documentations.destroy', $projectDocumentation))
        ->assertRedirect(route('project.documentations.index', absolute: false))
        ->assertToast('success', 'Documentação removida.');

    $this->assertDatabaseMissing('project_documentations', ['id' => $projectDocumentation->id]);
});

test('project members with delete permission can delete file project documentations and clean up S3', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::Delete);

    $filePath = UploadedFile::fake()->create('spec.pdf', 1024)->store('project-documentations', 's3');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Project spec',
        'type'       => ProjectDocumentationType::File,
        'category'   => ProjectDocumentationCategory::Requirements,
        'file_path'  => $filePath,
        'url'        => null,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->delete(route('project.documentations.destroy', $projectDocumentation))
        ->assertRedirect(route('project.documentations.index', absolute: false));

    $this->assertDatabaseMissing('project_documentations', ['id' => $projectDocumentation->id]);

    Storage::disk('s3')->assertMissing($filePath);
});

test('project members without delete permission cannot delete project documentations', function () {
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
        ->delete(route('project.documentations.destroy', $projectDocumentation))
        ->assertForbidden();
});
