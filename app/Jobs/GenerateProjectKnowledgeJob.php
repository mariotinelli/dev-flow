<?php

declare(strict_types = 1);

namespace App\Jobs;

use App\Actions\ProjectDocumentations\SyncProjectDocumentationKnowledge;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Throwable;

class GenerateProjectKnowledgeJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [1, 5, 10];

    public function __construct(public int $projectDocumentationId)
    {
    }

    public function handle(SyncProjectDocumentationKnowledge $syncProjectDocumentationKnowledge): void
    {
        Cache::lock("project-knowledge:documentation:{$this->projectDocumentationId}", 300)
            ->block(10, fn () => $syncProjectDocumentationKnowledge->handle($this->projectDocumentationId));
    }

    public function failed(?Throwable $exception): void
    {
        if ($exception) {
            report($exception);
        }
    }
}
