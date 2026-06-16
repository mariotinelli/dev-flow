<?php

declare(strict_types = 1);

namespace App\Enums;

enum ContractType: int
{
    case Freelance = 1;
    case Fixed     = 2;

    public function label(): string
    {
        return match ($this) {
            self::Freelance => 'Freelance',
            self::Fixed     => 'Contrato fixo',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $contractType): array => [
            'value' => $contractType->value,
            'label' => $contractType->label(),
        ], self::cases());
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $contractType): int => $contractType->value, self::cases());
    }
}
