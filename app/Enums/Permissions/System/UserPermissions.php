<?php

declare(strict_types = 1);

namespace App\Enums\Permissions\System;

enum UserPermissions: string
{
    case View    = 'system.users.view';
    case Create  = 'system.users.create';
    case Update  = 'system.users.update';
    case Delete  = 'system.users.delete';
    case Restore = 'system.users.restore';

    public function label(): string
    {
        return match ($this) {
            self::View    => 'Visualizar usuários',
            self::Create  => 'Criar usuários',
            self::Update  => 'Editar usuários',
            self::Delete  => 'Inativar usuários',
            self::Restore => 'Ativar usuários',
        };
    }

    public function group(): string
    {
        return 'Usuários';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission): string => $permission->value, self::cases());
    }
}
