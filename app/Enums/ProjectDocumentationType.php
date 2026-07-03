<?php

declare(strict_types = 1);

namespace App\Enums;

enum ProjectDocumentationType: int
{
    case File  = 1;
    case Image = 2;
    case Link  = 3;

    public function label(): string
    {
        return match ($this) {
            self::File  => 'Arquivo',
            self::Image => 'Imagem',
            self::Link  => 'Link',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $type): array => [
            'value' => $type->value,
            'label' => $type->label(),
        ], self::cases());
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $type): int => $type->value, self::cases());
    }
}
