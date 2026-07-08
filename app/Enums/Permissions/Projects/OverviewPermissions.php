<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum OverviewPermissions: string
{
    case View = 'project.overview.view';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Visualizar visão geral',
        };
    }

    public function group(): string
    {
        return 'Visão Geral';
    }
}
