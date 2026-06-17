<?php

declare(strict_types = 1);

namespace App\Enums\Permissions;

enum RolePermissions: string
{
    case View   = 'roles.view';
    case Create = 'roles.create';
    case Update = 'roles.update';
    case Delete = 'roles.delete';

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
