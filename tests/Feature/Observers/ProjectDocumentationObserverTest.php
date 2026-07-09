<?php

declare(strict_types = 1);

use App\Actions\Project\Documentations\SyncProjectDocumentationKnowledge;
use App\Enums\Permissions\Project\DocumentationPermissions;
use App\Enums\ProjectDocumentationCategory;
use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Jobs\GenerateProjectKnowledgeJob;
use App\Models\ProjectKnowledgeChunk;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;

test('it dispatches knowledge generation when a file project documentation is created', function () {
    config(['semantic-search.enabled' => true]);

    [$user, $project] = projectMemberWithDocumentPermissions(DocumentationPermissions::Create);

    Queue::fake();
    Storage::fake('s3');

    $this->actingAs($user)
        ->withSession(['selected_project_id' => $project->id])
        ->post(route('project.documentations.store'), [
            'title'      => 'Runbook',
            'type'       => ProjectDocumentationType::File->value,
            'category'   => ProjectDocumentationCategory::Infrastructure->value,
            'visibility' => ProjectDocumentationVisibility::ProjectMembers->value,
            'file'       => UploadedFile::fake()->createWithContent('runbook.md', '# Deployments'),
        ])
        ->assertRedirect(route('project.documentations.index', absolute: false));

    Queue::assertPushed(GenerateProjectKnowledgeJob::class);
});

test('it deletes knowledge when a file documentation stops being indexable', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'api.md',
        content: "# API\n\nUse bearer tokens for API access.",
    );

    (new GenerateProjectKnowledgeJob($projectDocumentation->id))->handle(app(SyncProjectDocumentationKnowledge::class));

    expect(ProjectKnowledgeChunk::query()->count())->toBe(1);

    $projectDocumentation->update([
        'type' => ProjectDocumentationType::Link,
        'url'  => 'https://example.com/api',
    ]);

    expect(ProjectKnowledgeSource::query()->count())->toBe(0)
        ->and(ProjectKnowledgeChunk::query()->count())->toBe(0);
});

test('it deletes knowledge when a project documentation is deleted', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'requirements.md',
        content: "# Requirements\n\nThe dashboard must load quickly.",
    );

    (new GenerateProjectKnowledgeJob($projectDocumentation->id))->handle(app(SyncProjectDocumentationKnowledge::class));

    expect(ProjectKnowledgeChunk::query()->count())->toBe(1);

    $projectDocumentation->delete();

    expect(ProjectKnowledgeSource::query()->count())->toBe(0)
        ->and(ProjectKnowledgeChunk::query()->count())->toBe(0);
});
