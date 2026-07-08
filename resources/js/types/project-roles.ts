import type { PaginationMeta, ProjectDeletedStatusOption } from '@/types';

export type ProjectRoleFilterValues = {
    search: string;
    deleted_status: string;
};

export type ProjectRole = {
    id: number;
    name: string;
    permissions_count: number;
    is_active: boolean;
    deleted_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
        restore: boolean;
    };
};

export type EditableProjectRole = {
    id: number;
    name: string;
    permissions: string[];
};

export type PaginatedProjectRoles = {
    data: ProjectRole[];
    meta: PaginationMeta;
};

export type ProjectRoleSourceProject = {
    id: number;
    name: string;
    key: string;
    roles_count: number;
};

export type ProjectRoleDeletedStatusOption = ProjectDeletedStatusOption;
