<?php

declare(strict_types = 1);

namespace App\Enums\Permissions;

enum DeveloperPermissions: string
{
    case View    = 'developers.view';
    case Create  = 'developers.create';
    case Update  = 'developers.update';
    case Delete  = 'developers.delete';
    case Restore = 'developers.restore';

    public function label(): string
    {
        return match ($this) {
            self::View    => 'Visualizar desenvolvedores',
            self::Create  => 'Criar desenvolvedores',
            self::Update  => 'Editar desenvolvedores',
            self::Delete  => 'Inativar desenvolvedores',
            self::Restore => 'Ativar desenvolvedores',
        };
    }

    public function group(): string
    {
        return 'Desenvolvedores';
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission): string => $permission->value, self::cases());
    }
}
