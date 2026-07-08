<?php

declare(strict_types = 1);

namespace App\Enums;

use App\Enums\Permissions\ProjectPermissions;
use App\Enums\Permissions\Projects\DocumentPermissions;
use App\Enums\Permissions\Projects\MemberPermissions;
use App\Enums\Permissions\Projects\MetricPermissions;
use App\Enums\Permissions\Projects\OverviewPermissions;
use App\Enums\Permissions\Projects\SettingPermissions;
use App\Enums\Permissions\Projects\TaskPermissions;
use App\Enums\Permissions\RolePermissions;
use App\Enums\Permissions\UserPermissions;

final class Permission
{
    /**
     * @return list<RolePermissions|ProjectPermissions|UserPermissions>
     */
    public static function systemCases(): array
    {
        return [
            ...RolePermissions::cases(),
            ...ProjectPermissions::cases(),
            ...UserPermissions::cases(),
        ];
    }

    /**
     * @return list<OverviewPermissions|TaskPermissions|MemberPermissions|DocumentPermissions|MetricPermissions|SettingPermissions>
     */
    public static function projectCases(): array
    {
        return [
            ...OverviewPermissions::cases(),
            ...TaskPermissions::cases(),
            ...MemberPermissions::cases(),
            ...DocumentPermissions::cases(),
            ...MetricPermissions::cases(),
            ...SettingPermissions::cases(),
        ];
    }

    /**
     * @return list<RolePermissions|ProjectPermissions|UserPermissions|OverviewPermissions|TaskPermissions|MemberPermissions|DocumentPermissions|MetricPermissions|SettingPermissions>
     */
    public static function cases(): array
    {
        return [
            ...self::systemCases(),
            ...self::projectCases(),
        ];
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (RolePermissions | ProjectPermissions | UserPermissions | OverviewPermissions | TaskPermissions | MemberPermissions | DocumentPermissions | MetricPermissions | SettingPermissions $permission): string => $permission->value, self::cases());
    }

    /**
     * @return list<string>
     */
    public static function systemValues(): array
    {
        return array_map(fn (RolePermissions | ProjectPermissions | UserPermissions $permission): string => $permission->value, self::systemCases());
    }

    /**
     * @return list<string>
     */
    public static function projectValues(): array
    {
        return array_map(fn (OverviewPermissions | TaskPermissions | MemberPermissions | DocumentPermissions | MetricPermissions | SettingPermissions $permission): string => $permission->value, self::projectCases());
    }

    /**
     * @return array<string, list<array{name: string, label: string}>>
     */
    public static function groupedOptions(): array
    {
        return self::groupedOptionsFor(self::cases());
    }

    /**
     * @return array<string, list<array{name: string, label: string}>>
     */
    public static function systemGroupedOptions(): array
    {
        return self::groupedOptionsFor(self::systemCases());
    }

    /**
     * @return array<string, list<array{name: string, label: string}>>
     */
    public static function projectGroupedOptions(): array
    {
        return self::groupedOptionsFor(self::projectCases());
    }

    /**
     * @param  list<RolePermissions|ProjectPermissions|UserPermissions|OverviewPermissions|TaskPermissions|MemberPermissions|DocumentPermissions|MetricPermissions|SettingPermissions>  $permissions
     * @return array<string, list<array{name: string, label: string}>>
     */
    private static function groupedOptionsFor(array $permissions): array
    {
        $groups = [];

        foreach ($permissions as $permission) {
            $groups[$permission->group()][] = [
                'name'  => $permission->value,
                'label' => $permission->label(),
            ];
        }

        return $groups;
    }
}
