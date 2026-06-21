<?php

declare(strict_types = 1);

namespace App\Actions;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GetSelectOptions
{
    public function handle(Builder $query, string $keyValue, string $keyLabel): array
    {
        return $query->orderBy($keyLabel)->get()->map(fn (Model $item) => [
            'value' => $item->{$keyValue},
            'label' => Str::headline($item->{$keyLabel}),
        ])->toArray();
    }
}
