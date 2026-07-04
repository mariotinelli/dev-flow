import type { PaginationMeta } from '@/types';

export type ProjectDocumentationFilterValues = {
    search: string;
    type: string;
};

export type ProjectDocumentation = {
    id: number;
    title: string;
    description: string | null;
    type: number;
    type_label: string;
    category: number;
    category_label: string;
    visibility: number;
    visibility_label: string;
    url: string | null;
    download_url: string | null;
    author: {
        id: number;
        name: string;
    };
    created_at: string | null;
    can: {
        update: boolean;
        delete: boolean;
    };
};

export type PaginatedProjectDocumentations = {
    data: ProjectDocumentation[];
    meta: PaginationMeta;
};
