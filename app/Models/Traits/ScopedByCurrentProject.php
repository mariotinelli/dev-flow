<?php

declare(strict_types = 1);

namespace App\Models\Traits;

use App\Models\Scopes\BelongsToCurrentProjectScope;

trait ScopedByCurrentProject
{
    public static function bootScopedByCurrentProject(): void
    {
        static::addGlobalScope(new BelongsToCurrentProjectScope());
    }
}
