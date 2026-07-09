<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Project\Settings;

enum LoomPermissions: string
{
    case View   = 'project.settings.loom.view';
    case Update = 'project.settings.loom.update';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar configurações do Loom',
            self::Update => 'Editar configurações do Loom',
        };
    }

    public function group(): string
    {
        return 'Configurações - Loom';
    }
}
