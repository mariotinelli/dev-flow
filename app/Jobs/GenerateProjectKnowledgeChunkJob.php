<?php

declare(strict_types = 1);

namespace App\Jobs;

use App\Enums\QueuePriority;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Tries;
use Laravel\Ai\Embeddings;
use Throwable;

#[Tries(3)]
#[Backoff([1, 5, 10])]
#[Queue(QueuePriority::LowPriority)]
class GenerateProjectKnowledgeChunkJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $projectKnowledgeSourceId,
        public int $projectId,
        public int $position,
        public string $content,
        public string $embeddedContent,
        public ?string $sectionPath,
        public string $contentHash,
        public string $sourceHash,
        public string $provider,
        public string $model,
        public int $dimensions,
    ) {
    }

    public function handle(): void
    {
        $knowledgeSource = ProjectKnowledgeSource::query()->find($this->projectKnowledgeSourceId);

        if (!$knowledgeSource instanceof ProjectKnowledgeSource || !$this->matchesCurrentSource($knowledgeSource)) {
            return;
        }

        $response = Embeddings::for([$this->embeddedContent])
            ->dimensions($this->dimensions)
            ->generate($this->provider, $this->model);

        $knowledgeSource->chunks()->updateOrCreate([
            'position' => $this->position,
        ], [
            'project_id' => $this->projectId,
            'content'    => $this->content,
            'embedding'  => $response->embeddings[0],
            'metadata'   => [
                'section_path'         => $this->sectionPath,
                'content_hash'         => $this->contentHash,
                'source_hash'          => $this->sourceHash,
                'embedding_provider'   => $this->provider,
                'embedding_model'      => $this->model,
                'embedding_dimensions' => $this->dimensions,
            ],
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }
    }

    private function matchesCurrentSource(ProjectKnowledgeSource $knowledgeSource): bool
    {
        $metadata = $knowledgeSource->metadata ?? [];

        return ($metadata['source_hash'] ?? null) === $this->sourceHash
            && ($metadata['embedding_provider'] ?? null) === $this->provider
            && ($metadata['embedding_model'] ?? null) === $this->model
            && ($metadata['embedding_dimensions'] ?? null) === $this->dimensions;
    }
}
