<?php

declare(strict_types = 1);

namespace App\Enums;

enum Seniority: int
{
    case Junior = 1;
    case Mid    = 2;
    case Senior = 3;
    case Lead   = 4;

    public function label(): string
    {
        return match ($this) {
            self::Junior => 'Júnior',
            self::Mid    => 'Pleno',
            self::Senior => 'Sênior',
            self::Lead   => 'Líder técnico',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $seniority): array => [
            'value' => $seniority->value,
            'label' => $seniority->label(),
        ], self::cases());
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $seniority): int => $seniority->value, self::cases());
    }
}
