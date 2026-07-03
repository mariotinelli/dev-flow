<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Models\Project;
use App\Support\CurrentProject;

final class ProjectObserver
{
    public function __construct(
        private CurrentProject $currentProject,
    ) {
    }

    public function created(Project $project): void
    {
        $this->currentProject->clearCache();
    }

    public function updated(Project $project): void
    {
        if ($project->wasChanged($project->getDeletedAtColumn())) {
            return;
        }

        $this->currentProject->clearCache();
    }

    public function deleted(Project $project): void
    {
        $this->currentProject->clearCache();
    }

    public function restored(Project $project): void
    {
        $this->currentProject->clearCache();
    }
}
