<?php

declare(strict_types = 1);

use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectDocumentation;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

test('non-admin users cannot view AdministratorsOnly project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Admin secret',
        'type'       => ProjectDocumentationType::Link,
        'visibility' => ProjectDocumentationVisibility::AdministratorsOnly,
        'url'        => 'https://example.com/secret',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.show', $projectDocumentation))
        ->assertForbidden();
});

test('admin users can view AdministratorsOnly project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $user->assignRole('admin');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Admin secret',
        'type'       => ProjectDocumentationType::Link,
        'visibility' => ProjectDocumentationVisibility::AdministratorsOnly,
        'url'        => 'https://example.com/secret',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.show', $projectDocumentation))
        ->assertOk();
});

test('image project documentation includes download_url', function () {
    Storage::fake('s3');

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $file = UploadedFile::fake()->image('screenshot.png');

    $filePath = $file->store('project-documentations', 's3');

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'Screenshot',
        'type'       => ProjectDocumentationType::Image,
        'category'   => ProjectDocumentationCategory::Other,
        'file_path'  => $filePath,
        'url'        => null,
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.show', $projectDocumentation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('projectDocumentation.type', ProjectDocumentationType::Image->value)
            ->where('projectDocumentation.download_url', fn (string $url) => str_starts_with($url, 'http')));
});

test('link project documentation has no download_url', function () {
    [$user, $project] = projectMemberWithDocumentPermissions(DocumentPermissions::View);

    $projectDocumentation = ProjectDocumentation::factory()->create([
        'project_id' => $project->id,
        'author_id'  => $user->id,
        'title'      => 'API Guide',
        'type'       => ProjectDocumentationType::Link,
        'category'   => ProjectDocumentationCategory::API,
        'url'        => 'https://example.com/api-guide',
    ]);

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->get(route('project.documentations.show', $projectDocumentation))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('projectDocumentation.download_url', null));
});

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
