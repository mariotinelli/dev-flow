import type { PaginationMeta, SelectOption } from '@/types';

export type ProjectMemberFilterValues = {
    search: string;
};

export type ProjectMember = {
    id: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
    project_role: {
        id: number;
        name: string;
    };
    entered_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type EditableProjectMember = {
    id: number;
    user_id: number;
    project_role_id: number;
};

export type PaginatedProjectMembers = {
    data: ProjectMember[];
    meta: PaginationMeta;
};

export type ProjectMemberSelectOption = SelectOption;
