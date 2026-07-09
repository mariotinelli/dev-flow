<?php

declare(strict_types = 1);

namespace App\Actions\ProjectDocumentations;

use App\Jobs\GenerateProjectKnowledgeChunkJob;
use App\Models\ProjectDocumentation;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Support\Facades\DB;

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

        $knowledgeSource  = $this->knowledgeSource($projectDocumentation);
        $previousMetadata = $knowledgeSource?->metadata ?? [];

        $knowledgeSource = ProjectKnowledgeSource::query()->updateOrCreate([
            'project_id'  => $projectDocumentation->project_id,
            'source_type' => 'documentation',
            'source_id'   => $projectDocumentation->id,
        ], [
            'title'    => $projectDocumentation->title,
            'metadata' => $this->sourceMetadata($projectDocumentation, $sourceHash, $provider, $model, $dimensions),
        ]);

        if (($previousMetadata['source_hash'] ?? null) === $sourceHash
            && ($previousMetadata['embedding_provider'] ?? null) === $provider
            && ($previousMetadata['embedding_model'] ?? null) === $model
            && ($previousMetadata['embedding_dimensions'] ?? null) === $dimensions
            && $knowledgeSource->chunks()->exists()) {
            return;
        }

        $chunks = $this->chunkProjectDocumentationText->handle($projectDocumentation, $text);

        if ($chunks === []) {
            $this->deleteKnowledgeSource($projectDocumentation);

            return;
        }

        $knowledgeSourceId = DB::transaction(function () use ($projectDocumentation): ?int {
            $knowledgeSource = $this->knowledgeSource($projectDocumentation);

            if (!$knowledgeSource instanceof ProjectKnowledgeSource) {
                return null;
            }

            $knowledgeSource->chunks()->delete();

            return $knowledgeSource->id;
        });

        if ($knowledgeSourceId === null) {
            return;
        }

        foreach ($chunks as $position => $chunk) {
            GenerateProjectKnowledgeChunkJob::dispatch(
                projectKnowledgeSourceId: $knowledgeSourceId,
                projectId: $projectDocumentation->project_id,
                position: $position,
                content: $chunk['content'],
                embeddedContent: $chunk['embedded_content'],
                sectionPath: $chunk['section_path'],
                contentHash: $chunk['content_hash'],
                sourceHash: $sourceHash,
                provider: $provider,
                model: $model,
                dimensions: $dimensions,
            )->afterCommit();
        }
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
