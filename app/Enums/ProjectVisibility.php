<?php

declare(strict_types = 1);

namespace App\Enums;

enum ProjectVisibility: int
{
    case Private  = 1;
    case Internal = 2;
    case Public   = 3;

    public function label(): string
    {
        return match ($this) {
            self::Private  => 'Privado',
            self::Internal => 'Interno',
            self::Public   => 'Público',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $visibility): array => [
            'value' => $visibility->value,
            'label' => $visibility->label(),
        ], self::cases());
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $visibility): int => $visibility->value, self::cases());
    }
}
