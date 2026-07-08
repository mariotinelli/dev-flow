<?php

declare(strict_types = 1);

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Number;

function user(): ?User
{
    if (Auth::check()) {
        return Auth::user();
    }

    return null;
}

function currency(float | int | string | null $value): string
{
    if (is_string($value)) {
        $value = str($value)
            ->replace('.', '')
            ->replace(',', '.')
            ->toFloat();
    }

    return Number::currency($value ?? 0, in: 'BRL', locale: 'pt_BR');
}

function percentage(float | int | null $value): string
{
    return Number::percentage($value ?? 0);
}

function compareMoney(float | int | string | null $oldValue, float | int | string | null $newValue, string $operator = '!=='): bool
{
    return match ($operator) {
        '!=='   => currency($oldValue) !== currency($newValue),
        default => currency($oldValue) === currency($newValue),
    };
}
