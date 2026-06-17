<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { LayoutGrid, List, Plus } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/users';
import type { UserFilterValues, PaginatedUsers, SelectOption, StatusOption } from '@/types';
import UserEmptyState from './partials/UserEmptyState.vue';
import UserFilters from './partials/UserFilters.vue';
import UserGrid from './partials/UserGrid.vue';
import UserPagination from './partials/UserPagination.vue';
import UserTable from './partials/UserTable.vue';

const props = defineProps<{
    users: PaginatedUsers;
    can: {
        create: boolean;
    };
    filters: UserFilterValues;
    jobTitles: SelectOption[];
    contractTypes: SelectOption[];
    seniorities: SelectOption[];
    statuses: StatusOption[];
}>();

enum BaseStatus {
    All = 'all',
    Active = 'active',
    Inactive = 'inactive',
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Usuários',
                href: index(),
            },
        ],
    },
});

const viewMode = ref<'grid' | 'list'>('grid');
const filterForm = reactive<UserFilterValues>({ ...props.filters });

function submitFilters(): void {
    const query = Object.fromEntries(
        Object.entries(filterForm).filter(
            ([key, value]) => value !== '' && value !== (key === 'status' ? BaseStatus.All : 'all'),
        ),
    );

    router.get(index.url(), query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function clearFilters(): void {
    filterForm.search = '';
    filterForm.job_title = 'all';
    filterForm.contract_type = 'all';
    filterForm.seniority = 'all';
    filterForm.status = BaseStatus.All;

    router.get(
        index.url(),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            replace: true,
        },
    );
}
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6">
        <Head title="Usuários" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading title="Usuários" description="Gerencie os usuários que podem acessar o sistema." />

            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <div
                    class="inline-flex rounded-lg border border-input bg-background p-1"
                    aria-label="Alternar visualização"
                >
                    <Button
                        type="button"
                        size="sm"
                        :variant="viewMode === 'grid' ? 'secondary' : 'ghost'"
                        :aria-pressed="viewMode === 'grid'"
                        class="gap-2"
                        @click="viewMode = 'grid'"
                    >
                        <LayoutGrid class="size-4" />
                        Grade
                    </Button>
                    <Button
                        type="button"
                        size="sm"
                        :variant="viewMode === 'list' ? 'secondary' : 'ghost'"
                        :aria-pressed="viewMode === 'list'"
                        class="gap-2"
                        @click="viewMode = 'list'"
                    >
                        <List class="size-4" />
                        Lista
                    </Button>
                </div>

                <Button v-if="can.create" as-child>
                    <Link :href="create()">
                        <Plus class="size-4" />
                        Novo usuário
                    </Link>
                </Button>
            </div>
        </div>

        <UserFilters
            v-model:filters="filterForm"
            :job-titles="jobTitles"
            :contract-types="contractTypes"
            :seniorities="seniorities"
            :statuses="statuses"
            @submit="submitFilters"
            @clear="clearFilters"
        />

        <UserEmptyState v-if="users.data.length === 0" />
        <UserGrid v-else-if="viewMode === 'grid'" :users="users.data" />
        <UserTable v-else :users="users.data" />

        <UserPagination :meta="users.meta" />
    </div>
</template>
