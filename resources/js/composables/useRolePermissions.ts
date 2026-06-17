import { computed, ref } from 'vue';
import type { PermissionGroups } from '@/types';

export function useRolePermissions(permissionGroups: PermissionGroups, initialPermissions: string[] = []) {
    const selectedPermissions = ref<string[]>([...initialPermissions]);

    const allPermissionNames = computed(() =>
        Object.values(permissionGroups).flatMap((permissions) => permissions.map((permission) => permission.name)),
    );
    const allPermissionsSelected = computed(() => selectedPermissions.value.length === allPermissionNames.value.length);

    function hasPermission(permissionName: string): boolean {
        return selectedPermissions.value.includes(permissionName);
    }

    function setPermission(permissionName: string, checked: boolean): void {
        selectedPermissions.value = checked
            ? Array.from(new Set([...selectedPermissions.value, permissionName]))
            : selectedPermissions.value.filter((selectedPermission) => selectedPermission !== permissionName);
    }

    function groupPermissionNames(group: string): string[] {
        return permissionGroups[group]?.map((permission) => permission.name) ?? [];
    }

    function isGroupSelected(group: string): boolean {
        const permissionNames = groupPermissionNames(group);

        return (
            permissionNames.length > 0 &&
            permissionNames.every((permissionName) => selectedPermissions.value.includes(permissionName))
        );
    }

    function toggleGroup(group: string): void {
        const permissionNames = groupPermissionNames(group);

        selectedPermissions.value = isGroupSelected(group)
            ? selectedPermissions.value.filter((permissionName) => !permissionNames.includes(permissionName))
            : Array.from(new Set([...selectedPermissions.value, ...permissionNames]));
    }

    function toggleAllPermissions(): void {
        selectedPermissions.value = allPermissionsSelected.value ? [] : [...allPermissionNames.value];
    }

    return {
        selectedPermissions,
        allPermissionsSelected,
        hasPermission,
        setPermission,
        isGroupSelected,
        toggleGroup,
        toggleAllPermissions,
    };
}
