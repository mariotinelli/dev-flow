import type { PaginationMeta, SelectOption } from '@/types';

export type ProjectStatusOption = SelectOption;

export type ProjectVisibilityOption = SelectOption;

export type ProjectDeletedStatusOption = {
    value: 'active' | 'inactive';
    label: string;
};

export type ProjectFilterValues = {
    search: string;
    status: string;
    visibility: string;
    deleted_status: string;
};

export type Project = {
    id: number;
    name: string;
    key: string;
    slug: string;
    description: string | null;
    color: string | null;
    status: number;
    status_label: string;
    visibility: number;
    visibility_label: string;
    starts_at: string | null;
    due_at: string | null;
    is_active: boolean;
    deleted_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
        restore: boolean;
    };
};

export type PaginatedProjects = {
    data: Project[];
    meta: PaginationMeta;
};
