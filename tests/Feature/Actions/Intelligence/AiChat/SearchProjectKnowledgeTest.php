<?php

declare(strict_types = 1);

use App\Actions\Intelligence\AiChat\SearchProjectKnowledge;
use App\Enums\ProjectDocumentationVisibility;
use App\Models\Project;
use App\Models\ProjectKnowledgeChunk;
use App\Models\ProjectKnowledgeSource;
use App\Models\User;
use Laravel\Ai\Embeddings;

beforeEach(function (): void {
    config([
        'semantic-search.retrieval.limit'          => 10,
        'semantic-search.retrieval.min_similarity' => 0.8,
        'semantic-search.embeddings.dimensions'    => 1536,
    ]);
});

test('it returns no context when the project has no chunks', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Como funciona o deploy?');

    expect($result['context'])->toBe('')
        ->and($result['sources'])->toBe([])
        ->and($result['chunks'])->toBe([]);
});

test('it returns a few chunks above the minimum score', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    createKnowledgeChunk($project, 'Deploy usa pipeline automatizado.', queryVector(), 0);
    createKnowledgeChunk($project, 'Rollback restaura a versão anterior.', queryVector(), 1);
    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Como funciona deploy e rollback?');

    expect($result['sources'])->toHaveCount(2)
        ->and($result['context'])->toContain('Deploy usa pipeline automatizado.')
        ->and($result['context'])->toContain('Rollback restaura a versão anterior.');
});

test('it limits many chunks to the configured top results', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    foreach (range(0, 11) as $position) {
        createKnowledgeChunk($project, "Chunk {$position}", queryVector(), $position);
    }

    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Liste os chunks');

    expect($result['sources'])->toHaveCount(10)
        ->and($result['chunks'])->toHaveCount(10);
});

test('it deduplicates sources while preserving the best score', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    $source = ProjectKnowledgeSource::query()->create([
        'project_id'  => $project->id,
        'source_type' => 'documentation',
        'source_id'   => 1,
        'title'       => 'Embedding 6',
        'metadata'    => [],
    ]);

    foreach (range(0, 2) as $position) {
        ProjectKnowledgeChunk::query()->create([
            'project_id'                  => $project->id,
            'project_knowledge_source_id' => $source->id,
            'content'                     => "Chunk {$position}",
            'embedding'                   => queryVector(),
            'position'                    => $position,
            'metadata'                    => [],
        ]);
    }

    fakeQuestionEmbedding();
    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Quais custos existem?');

    expect($result['chunks'])->toHaveCount(3)
        ->and($result['sources'])->toHaveCount(1)
        ->and($result['sources'][0]['title'])->toBe('Embedding 6')
        ->and($result['sources'][0]['score'])->toBe(1.0);
});

test('it excludes chunks below the minimum score', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    createKnowledgeChunk($project, 'Conteúdo irrelevante.', orthogonalVector(), 0);
    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Como funciona deploy?');

    expect($result['sources'])->toBe([])
        ->and($result['context'])->toBe('');
});

test('it includes chunks above the minimum score', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    createKnowledgeChunk($project, 'Conteúdo relevante.', queryVector(), 0);
    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Como funciona deploy?');

    expect($result['sources'])->toHaveCount(1)
        ->and($result['sources'][0]['score'])->toBeGreaterThanOrEqual(0.8);
});

test('it never returns chunks from another project', function () {
    [$user, $project] = projectMemberWithAiChatPermission();
    $otherProject     = Project::factory()->create();

    createKnowledgeChunk($otherProject, 'Segredo de outro projeto.', queryVector(), 0);
    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('Qual o segredo?');

    expect($result['sources'])->toBe([])
        ->and($result['context'])->not->toContain('Segredo de outro projeto.');
});

test('it excludes administrator only documentation chunks for non admins', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    createKnowledgeChunk(
        project: $project,
        content: 'Documento restrito a administradores.',
        embedding: queryVector(),
        position: 0,
        sourceMetadata: ['visibility' => ProjectDocumentationVisibility::AdministratorsOnly->name],
    );

    fakeQuestionEmbedding();

    $this->actingAs($user);

    $result = app(SearchProjectKnowledge::class)->handle('O que diz o documento restrito?');

    expect($result['sources'])->toBe([])
        ->and($result['context'])->toBe('');
});

test('it includes administrator only documentation chunks for admins', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $project = Project::factory()->create();

    createKnowledgeChunk(
        project: $project,
        content: 'Documento restrito a administradores.',
        embedding: queryVector(),
        position: 0,
        sourceMetadata: ['visibility' => ProjectDocumentationVisibility::AdministratorsOnly->name],
    );

    fakeQuestionEmbedding();

    $this->actingAs($admin);

    $result = app(SearchProjectKnowledge::class)->handle('O que diz o documento restrito?');

    expect($result['sources'])->toHaveCount(1)
        ->and($result['context'])->toContain('Documento restrito a administradores.');
});

function fakeQuestionEmbedding(): void
{
    Embeddings::fake([[queryVector()]]);
}
