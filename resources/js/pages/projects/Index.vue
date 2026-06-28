<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { LayoutGrid, List, Plus } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { create, index } from '@/routes/projects';
import type { PaginatedProjects, ProjectDeletedStatusOption, ProjectFilterValues } from '@/types';
import ProjectEmptyState from './partials/ProjectEmptyState.vue';
import ProjectFilters from './partials/ProjectFilters.vue';
import ProjectGrid from './partials/ProjectGrid.vue';
import ProjectPagination from './partials/ProjectPagination.vue';
import ProjectTable from './partials/ProjectTable.vue';

const props = defineProps<{
    projects: PaginatedProjects;
    can: {
        create: boolean;
    };
    filters: ProjectFilterValues;
    deletedStatuses: ProjectDeletedStatusOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projetos',
                href: index(),
            },
        ],
    },
});

const viewMode = ref<'grid' | 'list'>('grid');
const filterForm = reactive<ProjectFilterValues>({ ...props.filters });

function submitFilters(): void {
    const query = Object.fromEntries(Object.entries(filterForm).filter(([, value]) => value !== '' && value !== 'all'));

    router.get(index.url(), query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function clearFilters(): void {
    filterForm.search = '';
    filterForm.deleted_status = 'all';

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
        <Head title="Projetos" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading title="Projetos" description="Gerencie iniciativas e prazos do trabalho." />

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
                    <Link :href="create()"><Plus class="size-4" /> Novo projeto</Link>
                </Button>
            </div>
        </div>

        <ProjectFilters
            v-model:filters="filterForm"
            :deleted-statuses="deletedStatuses"
            @submit="submitFilters"
            @clear="clearFilters"
        />

        <ProjectEmptyState v-if="projects.data.length === 0" />
        <ProjectGrid v-else-if="viewMode === 'grid'" :projects="projects.data" />
        <ProjectTable v-else :projects="projects.data" />

        <ProjectPagination :meta="projects.meta" />
    </div>
</template>
