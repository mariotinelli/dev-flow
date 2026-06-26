<?php

declare(strict_types = 1);

namespace App\Enums;

enum JobTitle: int
{
    case BackendDeveloper   = 1;
    case FrontendDeveloper  = 2;
    case FullStackDeveloper = 3;
    case MobileDeveloper    = 4;
    case QAEngineer         = 5;
    case DevOpsEngineer     = 6;
    case SoftwareArchitect  = 7;
    case ProductManager     = 8;
    case ProductOwner       = 9;
    case UIUXDesigner       = 10;
    case DataEngineer       = 11;
    case AIEngineer         = 12;

    public function label(): string
    {
        return match ($this) {
            self::BackendDeveloper   => 'Desenvolvedor back-end',
            self::FrontendDeveloper  => 'Desenvolvedor front-end',
            self::FullStackDeveloper => 'Desenvolvedor full stack',
            self::MobileDeveloper    => 'Desenvolvedor Mobile',
            self::QAEngineer         => 'Engenheiro de QA',
            self::DevOpsEngineer     => 'Engenheiro DevOps',
            self::SoftwareArchitect  => 'Arquiteto de Software',
            self::ProductManager     => 'Gerente de Produto',
            self::ProductOwner       => 'Dono do produto',
            self::UIUXDesigner       => 'Designer UI/UX',
            self::DataEngineer       => 'Engenheiro de Dados',
            self::AIEngineer         => 'Engenheiro de IA',
        };
    }

    /**
     * @return array<int, array{value: int, label: string}>
     */
    public static function options(): array
    {
        return array_map(fn (self $jobtitle): array => [
            'value' => $jobtitle->value,
            'label' => $jobtitle->label(),
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
