<?php

declare(strict_types = 1);

use App\Enums\QueuePriority;
use App\Jobs\GenerateProjectKnowledgeChunkJob;
use App\Jobs\GenerateProjectKnowledgeJob;
use App\Models\ProjectKnowledgeChunk;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

test('it dispatches a knowledge generation job for each project documentation chunk', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Queue::fake();
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'runbook.md',
        content: "# Deployments\n\nDeployments run through the release pipeline.\n\n# Rollbacks\n\nRollbacks restore the previous release.",
    );

    app()->call([(new GenerateProjectKnowledgeJob($projectDocumentation->id)), 'handle']);

    Queue::assertPushedOn(QueuePriority::KnowledgeChunks->value, GenerateProjectKnowledgeChunkJob::class);
    Queue::assertPushedTimes(GenerateProjectKnowledgeChunkJob::class, 2);
    Embeddings::assertNothingGenerated();
    expect(ProjectKnowledgeChunk::query()->count())->toBe(0);
});

test('it generates project knowledge for a single project documentation chunk', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'runbook.md',
        content: "# Deployments\n\nDeployments run through the release pipeline.",
    );

    $sourceHash = hash('sha256', 'runbook.md');
    $source     = ProjectKnowledgeSource::query()->create([
        'project_id'  => $project->id,
        'source_type' => 'documentation',
        'source_id'   => $projectDocumentation->id,
        'title'       => $projectDocumentation->title,
        'metadata'    => [
            'source_hash'          => $sourceHash,
            'embedding_provider'   => 'openai',
            'embedding_model'      => 'text-embedding-3-small',
            'embedding_dimensions' => 1536,
        ],
    ]);

    app()->call([(new GenerateProjectKnowledgeChunkJob(
        projectKnowledgeSourceId: $source->id,
        projectId: $project->id,
        position: 0,
        content: 'Deployments run through the release pipeline.',
        embeddedContent: "Documento: {$projectDocumentation->title}\nCategoria: {$projectDocumentation->category->name}\nSeção: Deployments\n\nDeployments run through the release pipeline.",
        sectionPath: 'Deployments',
        contentHash: 'chunk-hash',
        sourceHash: $sourceHash,
        provider: 'openai',
        model: 'text-embedding-3-small',
        dimensions: 1536,
    )), 'handle']);

    Embeddings::assertGenerated(fn (EmbeddingsPrompt $prompt): bool => $prompt->dimensions === 1536);
    expect(ProjectKnowledgeChunk::query()->count())->toBe(1);
});
