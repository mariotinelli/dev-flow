export type Developer = {
    id: number;
    name: string;
    email: string;
    avatar_url: string | null;
    role: string;
    contract_type: number;
    contract_type_label: string;
    seniority: number;
    seniority_label: string;
    is_active: boolean;
    status_label: string;
    can: {
        update: boolean;
        delete: boolean;
        restore: boolean;
    };
};

export type SelectOption = {
    value: number;
    label: string;
};

export type StatusOption = {
    value: 'active' | 'inactive';
    label: string;
};

export type DeveloperFilterValues = {
    search: string;
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

export type PaginatedDevelopers = {
    data: Developer[];
    meta: PaginationMeta;
};
