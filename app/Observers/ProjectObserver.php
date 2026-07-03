<?php

declare(strict_types = 1);

namespace App\Observers;

use App\Models\Project;
use App\Support\CurrentProject;

final class ProjectObserver
{
    public function created(Project $project): void
    {
        CurrentProject::clearCache();
    }

    public function updated(Project $project): void
    {
        if ($project->wasChanged($project->getDeletedAtColumn())) {
            return;
        }

        CurrentProject::clearCache();
    }

    public function deleted(Project $project): void
    {
        CurrentProject::clearCache();
    }

    public function restored(Project $project): void
    {
        CurrentProject::clearCache();
    }
}
