<?php

declare(strict_types = 1);

namespace App\Enums\Permissions;

enum ProjectPermissions: string
{
    case View    = 'projects.view';
    case Create  = 'projects.create';
    case Update  = 'projects.update';
    case Delete  = 'projects.delete';
    case Restore = 'projects.restore';

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
