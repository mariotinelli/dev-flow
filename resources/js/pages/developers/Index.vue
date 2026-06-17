<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { LayoutGrid, List, Plus } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/developers';
import type { DeveloperFilterValues, PaginatedDevelopers, SelectOption, StatusOption } from '@/types';
import DeveloperEmptyState from './partials/DeveloperEmptyState.vue';
import DeveloperFilters from './partials/DeveloperFilters.vue';
import DeveloperGrid from './partials/DeveloperGrid.vue';
import DeveloperPagination from './partials/DeveloperPagination.vue';
import DeveloperTable from './partials/DeveloperTable.vue';

const props = defineProps<{
    developers: PaginatedDevelopers;
    can: {
        create: boolean;
    };
    filters: DeveloperFilterValues;
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
                title: 'Desenvolvedores',
                href: index(),
            },
        ],
    },
});

const viewMode = ref<'grid' | 'list'>('grid');
const filterForm = reactive<DeveloperFilterValues>({ ...props.filters });

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
        <Head title="Desenvolvedores" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading title="Desenvolvedores" description="Gerencie os membros técnicos que podem acessar o sistema." />

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
                        Novo desenvolvedor
                    </Link>
                </Button>
            </div>
        </div>

        <DeveloperFilters
            v-model:filters="filterForm"
            :contract-types="contractTypes"
            :seniorities="seniorities"
            :statuses="statuses"
            @submit="submitFilters"
            @clear="clearFilters"
        />

        <DeveloperEmptyState v-if="developers.data.length === 0" />
        <DeveloperGrid v-else-if="viewMode === 'grid'" :developers="developers.data" />
        <DeveloperTable v-else :developers="developers.data" />

        <DeveloperPagination :meta="developers.meta" />
    </div>
</template>
