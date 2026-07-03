<?php

declare(strict_types = 1);

namespace App\Enums;

enum ProjectDocumentationVisibility: int
{
    case ProjectMembers     = 1;
    case AdministratorsOnly = 2;

    public function label(): string
    {
        return match ($this) {
            self::ProjectMembers     => 'Membros do projeto',
            self::AdministratorsOnly => 'Apenas administradores',
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
