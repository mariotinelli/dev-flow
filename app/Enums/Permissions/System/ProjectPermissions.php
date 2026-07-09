<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\System;

enum ProjectPermissions: string
{
    case View    = 'system.projects.view';
    case Create  = 'system.projects.create';
    case Update  = 'system.projects.update';
    case Delete  = 'system.projects.delete';
    case Restore = 'system.projects.restore';

    public function label(): string
    {
        return match ($this) {
            self::View    => 'Visualizar projetos',
            self::Create  => 'Criar projetos',
            self::Update  => 'Editar projetos',
            self::Delete  => 'Inativar projetos',
            self::Restore => 'Ativar projetos',
        };
    }

    public function group(): string
    {
        return 'Projetos';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission): string => $permission->value, self::cases());
    }
}
