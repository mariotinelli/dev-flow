<?php

declare(strict_types = 1);

namespace App\Models\Scopes;

use App\Support\CurrentProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class BelongsToCurrentProjectScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $project = app(CurrentProject::class)->resolve();

        if ($project) {
            $builder->where($model->getTable() . '.project_id', $project->id);
        }
    }
}
