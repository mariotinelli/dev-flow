<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Enums\ProjectDocumentationType;
use App\Enums\ProjectDocumentationVisibility;
use App\Jobs\GenerateProjectKnowledgeJob;
use App\Models\ProjectDocumentation;
use App\Models\ProjectKnowledgeSource;
use Illuminate\Support\Facades\Storage;

final class ProjectDocumentationObserver
{
    public function creating(ProjectDocumentation $projectDocumentation): void
    {
        if (in_array($projectDocumentation->type, [ProjectDocumentationType::File, ProjectDocumentationType::Image], true)) {
            $projectDocumentation->url = null;

            return;
        }

        $projectDocumentation->file_path          = null;
        $projectDocumentation->file_original_name = null;
    }

    public function updating(ProjectDocumentation $projectDocumentation): void
    {
        if (in_array($projectDocumentation->type, [ProjectDocumentationType::File, ProjectDocumentationType::Image], true)) {
            $projectDocumentation->url = null;

            return;
        }

        if ($projectDocumentation->file_path) {
            Storage::disk('s3')->delete($projectDocumentation->file_path);
        }

        $projectDocumentation->file_path          = null;
        $projectDocumentation->file_original_name = null;
    }

    public function created(ProjectDocumentation $projectDocumentation): void
    {
        if ($this->shouldGenerateKnowledge() && $projectDocumentation->isIndexableFile()) {
            GenerateProjectKnowledgeJob::dispatch($projectDocumentation->id)->afterCommit();
        }
    }

    public function updated(ProjectDocumentation $projectDocumentation): void
    {
        if ($this->shouldGenerateKnowledge() && $projectDocumentation->isIndexableFile() && $projectDocumentation->wasChanged(['title', 'category', 'visibility', 'file_path'])) {
            GenerateProjectKnowledgeJob::dispatch($projectDocumentation->id)->afterCommit();

            return;
        }

        if (!$projectDocumentation->isIndexableFile() && $projectDocumentation->wasChanged(['type', 'visibility', 'file_path'])) {
            ProjectKnowledgeSource::query()
                ->where('project_id', $projectDocumentation->project_id)
                ->where('source_type', 'documentation')
                ->where('source_id', $projectDocumentation->id)
                ->delete();
        }
    }

    public function deleted(ProjectDocumentation $projectDocumentation): void
    {
        ProjectKnowledgeSource::query()
            ->where('project_id', $projectDocumentation->project_id)
            ->where('source_type', 'documentation')
            ->where('source_id', $projectDocumentation->id)
            ->delete();
    }

    private function shouldGenerateKnowledge(): bool
    {
        return (bool) config('semantic-search.enabled', true);
    }
}
