<?php

declare(strict_types = 1);

namespace App\Jobs;

use App\Actions\ProjectDocumentations\SyncProjectDocumentationKnowledge;
use App\Enums\QueuePriority;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Attributes\Backoff;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Tries;
use Illuminate\Support\Facades\Cache;
use Throwable;

#[Tries(3)]
#[Backoff([1, 5, 10])]
#[Queue(QueuePriority::LowPriority)]
class GenerateProjectKnowledgeJob implements ShouldQueue
{
    use Queueable;

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
