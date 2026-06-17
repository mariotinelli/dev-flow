<?php

declare(strict_types = 1);

namespace App\Enums;

use App\Enums\Permissions\DeveloperPermissions;
use App\Enums\Permissions\RolePermissions;

final class Permission
{
    /**
     * @return list<RolePermissions|DeveloperPermissions>
     */
    public static function cases(): array
    {
        return [
            ...RolePermissions::cases(),
            ...DeveloperPermissions::cases(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (RolePermissions | DeveloperPermissions $permission): string => $permission->value, self::cases());
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
