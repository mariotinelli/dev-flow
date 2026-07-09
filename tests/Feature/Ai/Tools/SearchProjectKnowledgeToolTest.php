<?php

declare(strict_types = 1);

use App\Ai\Tools\SearchProjectKnowledgeTool;
use Laravel\Ai\Embeddings;
use Laravel\Ai\Prompts\EmbeddingsPrompt;
use Laravel\Ai\Tools\Request;

test('it searches project knowledge and exposes the selected sources', function () {
    [$user, $project] = projectMemberWithAiChatPermission();

    config([
        'semantic-search.retrieval.min_similarity' => 0.8,
        'semantic-search.embeddings.dimensions'    => 1536,
    ]);

    createKnowledgeChunk($project, 'Deploy usa pipeline automatizado.', queryVector(), 0);
    Embeddings::fake([[queryVector()]]);

    $this->actingAs($user);

    $tool = new SearchProjectKnowledgeTool();

    $result = json_decode($tool->handle(new Request(['question' => 'Como funciona deploy?'])), true, flags: JSON_THROW_ON_ERROR);

    Embeddings::assertGenerated(fn (EmbeddingsPrompt $prompt): bool => $prompt->inputs === ['Como funciona deploy?']);
    expect($result['context'])->toContain('Deploy usa pipeline automatizado.')
        ->and($tool->sources())->toHaveCount(1)
        ->and($tool->sources()[0]['title'])->toBe('Fonte 0');
});
