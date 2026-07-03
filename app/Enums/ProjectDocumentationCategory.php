<?php

declare(strict_types = 1);

namespace App\Enums;

enum ProjectDocumentationCategory: int
{
    case Architecture   = 1;
    case Business       = 2;
    case API            = 3;
    case Database       = 4;
    case Infrastructure = 5;
    case Deploy         = 6;
    case Meeting        = 7;
    case Design         = 8;
    case Requirements   = 9;
    case Tutorial       = 10;
    case Other          = 11;

    public function label(): string
    {
        return match ($this) {
            self::Architecture   => 'Arquitetura',
            self::Business       => 'Negócio',
            self::API            => 'API',
            self::Database       => 'Banco de Dados',
            self::Infrastructure => 'Infraestrutura',
            self::Deploy         => 'Deploy',
            self::Meeting        => 'Reunião',
            self::Design         => 'Design',
            self::Requirements   => 'Requisitos',
            self::Tutorial       => 'Tutorial',
            self::Other          => 'Outro',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $category): array => [
            'value' => $category->value,
            'label' => $category->label(),
        ], self::cases());
    }

    /**
     * @return array<int, int>
     */
    public static function values(): array
    {
        return array_map(fn (self $category): int => $category->value, self::cases());
    }
}
