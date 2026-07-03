<?php

declare(strict_types = 1);

namespace App\Policies\Traits;

use App\Models\Project;
use App\Support\CurrentProject;
use Illuminate\Database\Eloquent\Model;

trait BelongsToCurrentProject
{
    private ?CurrentProject $currentProject = null;

    private function resolveCurrentProject(): ?Project
    {
        if (!$this->currentProject) {
            $this->currentProject = app(CurrentProject::class);
        }

        return $this->currentProject->resolve();
    }

    private function belongsToCurrentProject(Model $model): bool
    {
        return $model->getAttribute('project_id') === $this->resolveCurrentProject()?->id;
    }
}
