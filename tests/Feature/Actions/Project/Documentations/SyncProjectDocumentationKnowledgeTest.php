<?php

declare(strict_types = 1);

use App\Actions\Project\Documentations\SyncProjectDocumentationKnowledge;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\ProjectKnowledgeChunk;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Support\Facades\Storage;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;

test('it creates a documentation knowledge source with vector chunks', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'architecture.md',
        content: "# Authentication\n\nUsers authenticate with email and password.",
    );

    app(SyncProjectDocumentationKnowledge::class)->handle($projectDocumentation->id);

    Embeddings::assertGenerated(fn (EmbeddingsPrompt $prompt): bool => $prompt->dimensions === 1536);

    $source = ProjectKnowledgeSource::query()->firstWhere('source_id', $projectDocumentation->id);
    $chunk  = ProjectKnowledgeChunk::query()->firstWhere('project_knowledge_source_id', $source->id);

    expect($source->source_type)->toBe('documentation')
        ->and($source->metadata)->toMatchArray([
            'documentation_id'     => $projectDocumentation->id,
            'category'             => 'Architecture',
            'embedding_provider'   => 'openai',
            'embedding_model'      => 'text-embedding-3-small',
            'embedding_dimensions' => 1536,
        ])
        ->and($chunk->metadata)->toMatchArray([
            'section_path'         => 'Authentication',
            'embedding_dimensions' => 1536,
        ]);
});

test('it skips embedding when file content has not changed', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'guide.md',
        content: "# Billing\n\nInvoices are generated monthly.",
    );

    app(SyncProjectDocumentationKnowledge::class)->handle($projectDocumentation->id);

    expect(ProjectKnowledgeChunk::query()->count())->toBe(1);

    $projectDocumentation->update(['title' => 'Updated billing guide']);

    Embeddings::fake();

    app(SyncProjectDocumentationKnowledge::class)->handle($projectDocumentation->id);

    Embeddings::assertNotGenerated(fn (EmbeddingsPrompt $prompt): bool => $prompt->contains('Updated billing guide'));
    expect(ProjectKnowledgeChunk::query()->count())->toBe(1);
});

test('it does not create knowledge for administrators only project documentations', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'admin-only.md',
        content: "# Private\n\nOnly administrators should see this.",
        visibility: ProjectDocumentationVisibility::AdministratorsOnly,
    );

    app(SyncProjectDocumentationKnowledge::class)->handle($projectDocumentation->id);

    Embeddings::assertNothingGenerated();
    expect(ProjectKnowledgeSource::query()->count())->toBe(0)
        ->and(ProjectKnowledgeChunk::query()->count())->toBe(0);
});

test('it ignores administrators only project documentations and removes existing knowledge', function () {
    [$user, $project] = projectMemberWithDocumentPermissions();

    Storage::fake('s3');
    Embeddings::fake();

    $projectDocumentation = createFileProjectDocumentationWithContent(
        userId: $user->id,
        projectId: $project->id,
        filename: 'guide.md',
        content: "# Shared\n\nProject members can search this.",
    );

    app(SyncProjectDocumentationKnowledge::class)->handle($projectDocumentation->id);

    expect(ProjectKnowledgeSource::query()->count())->toBe(1);

    $projectDocumentation->update(['visibility' => ProjectDocumentationVisibility::AdministratorsOnly]);

    Embeddings::fake();

    app(SyncProjectDocumentationKnowledge::class)->handle($projectDocumentation->id);

    expect(ProjectKnowledgeSource::query()->count())->toBe(0)
        ->and(ProjectKnowledgeChunk::query()->count())->toBe(0);
});
