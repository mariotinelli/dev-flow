<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Projects;

enum SettingPermissions: string
{
    case Manage = 'project.settings.manage';

    public function label(): string
    {
        return match ($this) {
            self::Manage => 'Gerenciar configurações do projeto',
        };
    }

    public function group(): string
    {
        return 'Configurações';
    }
}
