<?php

declare(strict_types = 1);

namespace App\Enums;

use App\Enums\Permissions\Intelligence\AiChatPermissions;
use App\Enums\Permissions\Project\DocumentationPermissions;
use App\Enums\Permissions\Project\MemberPermissions;
use App\Enums\Permissions\Project\MetricPermissions;
use App\Enums\Permissions\Project\OverviewPermissions;
use App\Enums\Permissions\Project\Settings\GitlabPermissions;
use App\Enums\Permissions\Project\Settings\LoomPermissions;
use App\Enums\Permissions\Project\Settings\RolePermissions as ProjectSettingsRolePermissions;
use App\Enums\Permissions\Project\TaskPermissions;
use App\Enums\Permissions\System\ProjectPermissions;
use App\Enums\Permissions\System\RolePermissions;
use App\Enums\Permissions\System\UserPermissions;

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
     * @return list<OverviewPermissions|TaskPermissions|MemberPermissions|DocumentationPermissions|MetricPermissions|ProjectSettingsRolePermissions|GitlabPermissions|LoomPermissions|AiChatPermissions>
     */
    public static function projectCases(): array
    {
        return [
            ...OverviewPermissions::cases(),
            ...TaskPermissions::cases(),
            ...MemberPermissions::cases(),
            ...DocumentationPermissions::cases(),
            ...MetricPermissions::cases(),
            ...ProjectSettingsRolePermissions::cases(),
            ...GitlabPermissions::cases(),
            ...LoomPermissions::cases(),
            ...AiChatPermissions::cases(),
        ];
    }

    /**
     * @return list<RolePermissions|ProjectPermissions|UserPermissions|OverviewPermissions|TaskPermissions|MemberPermissions|DocumentationPermissions|MetricPermissions|ProjectSettingsRolePermissions|GitlabPermissions|LoomPermissions|AiChatPermissions>
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
        return array_map(fn ($permission): string => $permission->value, self::cases());
    }

    /**
     * @return list<string>
     */
    public static function systemValues(): array
    {
        return array_map(fn ($permission): string => $permission->value, self::systemCases());
    }

    /**
     * @return list<string>
     */
    public static function projectValues(): array
    {
        return array_map(fn ($permission): string => $permission->value, self::projectCases());
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
     * @param  list<object>  $permissions
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
