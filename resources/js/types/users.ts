import type { User } from '@/types';

export type SelectOption = {
    value: number;
    label: string;
};

export type StatusOption = {
    value: 'active' | 'inactive';
    label: string;
};

export type UserFilterValues = {
    search: string;
    job_title: string;
    contract_type: string;
    seniority: string;
    status: string;
};

export type PaginationLinkData = {
    url: string | null;
    label: string;
    active: boolean;
};

export type PaginationMeta = {
    from: number | null;
    to: number | null;
    total: number;
    current_page: number;
    last_page: number;
    links: PaginationLinkData[];
};

export type PaginatedUsers = {
    data: User[];
    meta: PaginationMeta;
};
