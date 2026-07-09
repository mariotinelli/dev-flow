<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Project\Settings;

enum GitlabPermissions: string
{
    case View   = 'project.settings.gitlab.view';
    case Update = 'project.settings.gitlab.update';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar configurações do Gitlab',
            self::Update => 'Editar configurações do Gitlab',
        };
    }

    public function group(): string
    {
        return 'Configurações - Gitlab';
    }
}
