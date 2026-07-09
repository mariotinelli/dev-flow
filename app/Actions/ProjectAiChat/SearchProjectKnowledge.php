<?php

declare(strict_types = 1);

namespace App\Actions\ProjectAiChat;

use App\Enums\ProjectDocumentationVisibility;
use App\Models\Project;
use App\Models\ProjectKnowledgeChunk;
use App\Models\User;
use App\Support\CurrentProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;

class SearchProjectKnowledge
{
    /**
     * @return array{context: string, sources: list<array{id: int, chunk_id: int, title: string, source_type: string, source_id: int, position: int, score: float, metadata: array<string, mixed>|null}>, chunks: list<array{id: int, content: string, score: float}>}
     */
    public function handle(string $question): array
    {
        $user    = Auth::user();
        $project = $user instanceof User ? $this->resolveProject($user) : null;

        if (!$project instanceof Project || !$user instanceof User) {
            return [
                'context' => '',
                'sources' => [],
                'chunks'  => [],
            ];
        }

        $provider      = (string) config('semantic-search.embeddings.provider');
        $model         = (string) config('semantic-search.embeddings.model');
        $dimensions    = (int) config('semantic-search.embeddings.dimensions', 1536);
        $limit         = (int) config('semantic-search.retrieval.limit', 10);
        $minSimilarity = (float) config('semantic-search.retrieval.min_similarity', 0.4);

        $embedding = Embeddings::for([$question])
            ->dimensions($dimensions)
            ->generate($provider, $model)
            ->embeddings[0];

        $chunks = DB::connection()->getDriverName() === 'pgsql'
            ? $this->searchWithPgvector($project, $user, $embedding, $minSimilarity, $limit)
            : $this->searchInMemory($project, $user, $embedding, $minSimilarity, $limit);

        $sources = $chunks
            ->map(function (ProjectKnowledgeChunk $chunk): array {
                $source = $chunk->knowledgeSource;
                $score  = round(1 - (float) $chunk->getAttribute('distance'), 4);

                return [
                    'id'          => $source->id,
                    'chunk_id'    => $chunk->id,
                    'title'       => $source->title,
                    'source_type' => $source->source_type,
                    'source_id'   => $source->source_id,
                    'position'    => $chunk->position,
                    'score'       => $score,
                    'metadata'    => $chunk->metadata,
                ];
            })
            ->groupBy('id')
            ->map(function (Collection $sources): array {
                return $sources->sortByDesc('score')->first();
            })
            ->values()
            ->all();

        return [
            'context' => $this->context($chunks),
            'sources' => $sources,
            'chunks'  => $chunks
                ->map(fn (ProjectKnowledgeChunk $chunk): array => [
                    'id'      => $chunk->id,
                    'content' => $chunk->content,
                    'score'   => round(1 - (float) $chunk->getAttribute('distance'), 4),
                ])
                ->values()
                ->all(),
        ];
    }

    private function resolveProject(User $user): ?Project
    {
        $request = request();

        if ($request->user() instanceof User) {
            return app(CurrentProject::class)->resolve($request);
        }

        return app(CurrentProject::class)->availableFor($user)->first();
    }

    private function excludeAdministratorOnlySources(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query
                ->where('project_knowledge_sources.source_type', '!=', 'documentation')
                ->orWhereNull('project_knowledge_sources.metadata->visibility')
                ->orWhere('project_knowledge_sources.metadata->visibility', '!=', ProjectDocumentationVisibility::AdministratorsOnly->name);
        });
    }

    /**
     * @param  array<int, float>  $embedding
     */
    private function searchWithPgvector(Project $project, User $user, array $embedding, float $minSimilarity, int $limit): \Illuminate\Database\Eloquent\Collection
    {
        return ProjectKnowledgeChunk::query()
            ->select('project_knowledge_chunks.*')
            ->selectVectorDistance('project_knowledge_chunks.embedding', $embedding, as: 'distance')
            ->join('project_knowledge_sources', 'project_knowledge_sources.id', '=', 'project_knowledge_chunks.project_knowledge_source_id')
            ->where('project_knowledge_chunks.project_id', $project->id)
            ->whereVectorSimilarTo('project_knowledge_chunks.embedding', $embedding, minSimilarity: $minSimilarity)
            ->when(!$user->hasRole('admin'), fn (Builder $query) => $this->excludeAdministratorOnlySources($query))
            ->with('knowledgeSource')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  array<int, float>  $embedding
     */
    private function searchInMemory(Project $project, User $user, array $embedding, float $minSimilarity, int $limit): Collection
    {
        return ProjectKnowledgeChunk::query()
            ->where('project_id', $project->id)
            ->with('knowledgeSource')
            ->get()
            ->filter(function (ProjectKnowledgeChunk $chunk) use ($user): bool {
                if ($user->hasRole('admin')) {
                    return true;
                }

                $source = $chunk->knowledgeSource;

                return $source->source_type !== 'documentation'
                    || ($source->metadata['visibility'] ?? null) !== ProjectDocumentationVisibility::AdministratorsOnly->name;
            })
            ->map(function (ProjectKnowledgeChunk $chunk) use ($embedding): ProjectKnowledgeChunk {
                $score = $this->cosineSimilarity($embedding, $chunk->embedding);

                return $chunk->setAttribute('distance', 1 - $score);
            })
            ->filter(fn (ProjectKnowledgeChunk $chunk): bool => (1 - (float) $chunk->getAttribute('distance')) >= $minSimilarity)
            ->sortBy(fn (ProjectKnowledgeChunk $chunk): float => (float) $chunk->getAttribute('distance'))
            ->take($limit)
            ->values();
    }

    /**
     * @param  array<int, float>  $left
     * @param  array<int, float>  $right
     */
    private function cosineSimilarity(array $left, array $right): float
    {
        $dot = $leftMagnitude = $rightMagnitude = 0.0;

        foreach ($left as $index => $leftValue) {
            $rightValue = (float) ($right[$index] ?? 0.0);
            $dot += $leftValue * $rightValue;
            $leftMagnitude += $leftValue * $leftValue;
            $rightMagnitude += $rightValue * $rightValue;
        }

        if ($leftMagnitude === 0.0 || $rightMagnitude === 0.0) {
            return 0.0;
        }

        return $dot / (sqrt($leftMagnitude) * sqrt($rightMagnitude));
    }

    /**
     * @param  iterable<int, ProjectKnowledgeChunk>  $chunks
     */
    private function context(iterable $chunks): string
    {
        return collect($chunks)
            ->map(function (ProjectKnowledgeChunk $chunk, int $index): string {
                $source = $chunk->knowledgeSource;
                $number = $index + 1;
                $score  = round(1 - (float) $chunk->getAttribute('distance'), 4);

                return <<<CONTEXT
                <source id="{$number}" source_id="{$source->id}" chunk_id="{$chunk->id}" score="{$score}">
                Title: {$source->title}
                Type: {$source->source_type}
                Position: {$chunk->position}
                Content:
                {$chunk->content}
                </source>
                CONTEXT;
            })
            ->implode("\n\n");
    }
}
