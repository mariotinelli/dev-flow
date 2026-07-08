<?php

declare(strict_types = 1);

namespace App\Actions\ProjectDocumentations;

use App\Models\ProjectDocumentation;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Support\Facades\DB;
use Laravel\Ai\Embeddings;

class SyncProjectDocumentationKnowledge
{
    public function __construct(
        private ExtractProjectDocumentationText $extractProjectDocumentationText,
        private ChunkProjectDocumentationText $chunkProjectDocumentationText,
    ) {
    }

    public function handle(int $projectDocumentationId): void
    {
        $projectDocumentation = ProjectDocumentation::query()->find($projectDocumentationId);

        if (!$projectDocumentation instanceof ProjectDocumentation) {
            return;
        }

        if (!$projectDocumentation->isIndexableFile()) {
            $this->deleteKnowledgeSource($projectDocumentation);

            return;
        }

        [$text, $sourceHash] = $this->extractProjectDocumentationText->handle($projectDocumentation);

        if (!$text || !$sourceHash) {
            $this->deleteKnowledgeSource($projectDocumentation);

            return;
        }

        $provider   = (string) config('semantic-search.embeddings.provider');
        $model      = (string) config('semantic-search.embeddings.model');
        $dimensions = (int) config('semantic-search.embeddings.dimensions', 1536);

        $knowledgeSource = ProjectKnowledgeSource::query()->updateOrCreate([
            'project_id'  => $projectDocumentation->project_id,
            'source_type' => 'documentation',
            'source_id'   => $projectDocumentation->id,
        ], [
            'title'    => $projectDocumentation->title,
            'metadata' => $this->sourceMetadata($projectDocumentation, $sourceHash, $provider, $model, $dimensions),
        ]);

        $metadata = $knowledgeSource->metadata ?? [];

        if (($metadata['source_hash'] ?? null) === $sourceHash
            && ($metadata['embedding_provider'] ?? null) === $provider
            && ($metadata['embedding_model'] ?? null) === $model
            && ($metadata['embedding_dimensions'] ?? null) === $dimensions
            && $knowledgeSource->chunks()->exists()) {
            return;
        }

        $chunks = $this->chunkProjectDocumentationText->handle($projectDocumentation, $text);

        if ($chunks === []) {
            $this->deleteKnowledgeSource($projectDocumentation);

            return;
        }

        $response = Embeddings::for(array_column($chunks, 'embedded_content'))
            ->dimensions($dimensions)
            ->generate($provider, $model);

        DB::transaction(function () use ($projectDocumentation, $chunks, $response, $sourceHash, $provider, $model, $dimensions): void {
            $knowledgeSource = $this->knowledgeSource($projectDocumentation);

            if (!$knowledgeSource instanceof ProjectKnowledgeSource) {
                return;
            }

            $knowledgeSource->chunks()->delete();

            foreach ($chunks as $position => $chunk) {
                $knowledgeSource->chunks()->create([
                    'project_id' => $projectDocumentation->project_id,
                    'content'    => $chunk['content'],
                    'embedding'  => $response->embeddings[$position],
                    'position'   => $position,
                    'metadata'   => [
                        'section_path'         => $chunk['section_path'],
                        'content_hash'         => $chunk['content_hash'],
                        'source_hash'          => $sourceHash,
                        'embedding_provider'   => $provider,
                        'embedding_model'      => $model,
                        'embedding_dimensions' => $dimensions,
                    ],
                ]);
            }
        });
    }

    private function knowledgeSource(ProjectDocumentation $projectDocumentation): ?ProjectKnowledgeSource
    {
        return ProjectKnowledgeSource::query()
            ->where('project_id', $projectDocumentation->project_id)
            ->where('source_type', 'documentation')
            ->where('source_id', $projectDocumentation->id)
            ->first();
    }

    private function deleteKnowledgeSource(ProjectDocumentation $projectDocumentation): void
    {
        $this->knowledgeSource($projectDocumentation)?->delete();
    }

    /**
     * @return array<string, mixed>
     */
    private function sourceMetadata(ProjectDocumentation $projectDocumentation, string $sourceHash, string $provider, string $model, int $dimensions): array
    {
        return [
            'documentation_id'     => $projectDocumentation->id,
            'documentation_type'   => $projectDocumentation->type->name,
            'category'             => $projectDocumentation->category->name,
            'visibility'           => $projectDocumentation->visibility->name,
            'file_path'            => $projectDocumentation->file_path,
            'file_original_name'   => $projectDocumentation->file_original_name,
            'source_hash'          => $sourceHash,
            'embedding_provider'   => $provider,
            'embedding_model'      => $model,
            'embedding_dimensions' => $dimensions,
        ];
    }
}
