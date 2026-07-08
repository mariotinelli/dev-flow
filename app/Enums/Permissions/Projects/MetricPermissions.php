<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum MetricPermissions: string
{
    case View = 'project.metrics.view';

    public function label(): string
    {
        return match ($this) {
            self::View => 'Visualizar métricas',
        };
    }

    public function group(): string
    {
        return 'Métricas';
    }
}
