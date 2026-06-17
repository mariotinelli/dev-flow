<?php

declare(strict_types = 1);

namespace App\Enums;

use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\UserPermissions;

final class Permission
{
    /**
     * @return list<RolePermissions|UserPermissions>
     */
    public static function cases(): array
    {
        return [
            ...RolePermissions::cases(),
            ...UserPermissions::cases(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (RolePermissions | UserPermissions $permission): string => $permission->value, self::cases());
    }

    /**
     * @return array<string, list<array{name: string, label: string}>>
     */
    public static function groupedOptions(): array
    {
        $groups = [];

        foreach (self::cases() as $permission) {
            $groups[$permission->group()][] = [
                'name'  => $permission->value,
                'label' => $permission->label(),
            ];
        }

        return $groups;
    }
}
