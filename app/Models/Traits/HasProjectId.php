<?php

declare(strict_types = 1);

namespace App\Models\Traits;

use App\Support\CurrentProject;
use Illuminate\Database\Eloquent\Model;

trait HasProjectId
{
    public static function bootHasProjectId(): void
    {
        static::creating(function (Model $model): void {
            if (is_null($model->project_id)) {
                $project = app(CurrentProject::class)->resolve();

                $model->project_id = $project?->id;
            }
        });
    }
}
