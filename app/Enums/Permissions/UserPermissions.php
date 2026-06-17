<?php

declare(strict_types = 1);

namespace App\Enums\Permissions;

enum UserPermissions: string
{
    case View    = 'users.view';
    case Create  = 'users.create';
    case Update  = 'users.update';
    case Delete  = 'users.delete';
    case Restore = 'users.restore';

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
