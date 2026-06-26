export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    two_factor_enabled?: boolean;
    avatar_url: string | null;
    job_title: number;
    job_title_label: string;
    role_id: number | null;
    role: string;
    contract_type: number;
    contract_type_label: string;
    seniority: number;
    seniority_label: string;
    is_active: boolean;
    status_label: string;
    created_at: string;
    updated_at: string;
    can: {
        update: boolean;
        delete: boolean;
        restore: boolean;
    };
    [key: string]: unknown;
};

export type Auth = {
    user: User;
    permissions: Record<string, boolean>;
};

/* @chisel-passkeys */
export type Passkey = {
    id: number;
    name: string;
    authenticator: string | null;
    created_at_diff: string;
    last_used_at_diff: string | null;
};
/* @end-chisel-passkeys */

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
