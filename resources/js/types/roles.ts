export type RolePermission = {
    name: string;
    label: string;
};

export type PermissionGroups = Record<string, RolePermission[]>;

export type Role = {
    id: number;
    name: string;
    permissions_count: number;
    users_count: number;
    is_protected: boolean;
    is_in_use: boolean;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type EditableRole = {
    id: number;
    name: string;
    permissions: string[];
};
