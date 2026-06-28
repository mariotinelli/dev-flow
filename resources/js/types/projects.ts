import type { PaginationMeta } from '@/types';

export type ProjectDeletedStatusOption = {
    value: 'active' | 'inactive';
    label: string;
};

export type ProjectFilterValues = {
    search: string;
    deleted_status: string;
};

export type Project = {
    id: number;
    name: string;
    key: string;
    slug: string;
    description: string | null;
    color: string | null;
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
