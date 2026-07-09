<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\Project\Settings;

enum RolePermissions: string
{
    case View    = 'project.settings.roles.view';
    case Create  = 'project.settings.roles.create';
    case Update  = 'project.settings.roles.update';
    case Delete  = 'project.settings.roles.delete';
    case Restore = 'project.settings.roles.restore';
    case Copy    = 'project.settings.roles.copy';

    public function label(): string
    {
        return match ($this) {
            self::View    => 'Visualizar papéis do projeto',
            self::Create  => 'Criar papéis do projeto',
            self::Update  => 'Editar papéis do projeto',
            self::Delete  => 'Inativar papéis do projeto',
            self::Restore => 'Ativar papéis do projeto',
            self::Copy    => 'Copiar papéis de outro projeto',
        };
    }

    public function group(): string
    {
        return 'Configurações - Papéis';
    }
}
