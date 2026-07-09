<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\System;

enum RolePermissions: string
{
    case View   = 'system.roles.view';
    case Create = 'system.roles.create';
    case Update = 'system.roles.update';
    case Delete = 'system.roles.delete';

    public function label(): string
    {
        return match ($this) {
            self::View   => 'Visualizar perfis',
            self::Create => 'Criar perfis',
            self::Update => 'Editar perfis',
            self::Delete => 'Excluir perfis',
        };
    }

    public function group(): string
    {
        return 'Perfis e permissões';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission): string => $permission->value, self::cases());
    }
}
