<?php

declare(strict_types = 1);

use App\Enums\Permissions\Project\DocumentationPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('non-admin users cannot download AdministratorsOnly project documentations', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::View);

    $file = UploadedFile::fake()->create('secret.pdf', 1024);

    $filePath = $file->store('project-documentations', 's3');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Admin secret',
        'type'       => ProjectDocumentationType::File,
        'visibility' => ProjectDocumentationVisibility::AdministratorsOnly,
        'file_path'  => $filePath,
        'url'        => null,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.download', $projectDocumentation))
        ->assertForbidden();
});

test('admin users can download AdministratorsOnly project documentations', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::View);

    $user->assignRole('admin');

    $file = UploadedFile::fake()->create('secret.pdf', 1024);

    $filePath = $file->store('project-documentations', 's3');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Admin secret',
        'type'       => ProjectDocumentationType::File,
        'visibility' => ProjectDocumentationVisibility::AdministratorsOnly,
        'file_path'  => $filePath,
        'url'        => null,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.download', $projectDocumentation))
        ->assertSuccessful();
});

test('project members with view permission can download file project documentations', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::View);

    $file = UploadedFile::fake()->create('spec.pdf', 1024);

    $filePath = $file->store('project-documentations', 's3');

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
        ->get(route('project.documentations.download', $projectDocumentation))
        ->assertSuccessful();
});

test('project members without view permission cannot download file project documentations', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions();

    $file = UploadedFile::fake()->create('spec.pdf', 1024);

    $filePath = $file->store('project-documentations', 's3');

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
        ->get(route('project.documentations.download', $projectDocumentation))
        ->assertForbidden();
});

test('download returns 404 when project documentation has no file', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::View);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Guide',
        'type'       => ProjectDocumentationType::Link,
        'category'   => ProjectDocumentationCategory::API,
        'url'        => 'https://example.com/api-guide',
        'file_path'  => null,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.download', $projectDocumentation))
        ->assertNotFound();
});
