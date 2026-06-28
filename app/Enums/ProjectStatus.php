<?php

declare(strict_types = 1);

namespace App\Enums;

enum ProjectStatus: int
{
    case Active    = 1;
    case Paused    = 2;
    case Archived  = 3;
    case Completed = 4;

    public function label(): string
    {
        return match ($this) {
            self::Active    => 'Ativo',
            self::Paused    => 'Pausado',
            self::Archived  => 'Arquivado',
            self::Completed => 'Concluído',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $status): array => [
            'value' => $status->value,
            'label' => $status->label(),
        ], self::cases());
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $status): int => $status->value, self::cases());
    }
}
