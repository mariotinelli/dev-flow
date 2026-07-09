<?php

declare(strict_types = 1);

namespace App\Ai\Tools;

use App\Actions\Intelligence\AiChat\SearchProjectKnowledge;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProjectKnowledgeTool implements Tool
{
    /** @var list<array{id: int, chunk_id: int, title: string, source_type: string, source_id: int, position: int, score: float, metadata: array<string, mixed>|null}> */
    private array $sources = [];

    public function description(): Stringable | string
    {
        return 'Search the selected project knowledge base for context related to the user question. Use this before answering project questions.';
    }

    public function handle(Request $request): Stringable | string
    {
        $result = app(SearchProjectKnowledge::class)
            ->handle(question: $request->string('question')->toString());

        $this->sources = $result['sources'];

        if ($result['context'] === '') {
            return json_encode([
                'context' => '',
                'sources' => [],
                'message' => 'No relevant project knowledge chunks were found for this question.',
            ], JSON_THROW_ON_ERROR);
        }

        return json_encode($result, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * @return list<array{id: int, chunk_id: int, title: string, source_type: string, source_id: int, position: int, score: float, metadata: array<string, mixed>|null}>
     */
    public function sources(): array
    {
        return $this->sources;
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'question' => $schema
                ->string()
                ->description('The user question to search for in the selected project knowledge base.')
                ->required(),
        ];
    }
}
