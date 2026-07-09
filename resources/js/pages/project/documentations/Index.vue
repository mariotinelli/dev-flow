<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ArrowUpRight, Download, LayoutGrid, List, Pencil, Plus } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { download, index } from '@/routes/project/documentations';
import type { PaginatedProjectDocumentations, ProjectDocumentationFilterValues } from '@/types';
import ProjectPagination from '@/pages/system/projects/partials/ProjectPagination.vue';
import ProjectDocumentationDeleteAction from './partials/ProjectDocumentationDeleteAction.vue';
import ProjectDocumentationFilters from './partials/ProjectDocumentationFilters.vue';
import ProjectDocumentationFormDialog from './partials/ProjectDocumentationFormDialog.vue';
import ProjectDocumentationGrid from './partials/ProjectDocumentationGrid.vue';

const props = defineProps<{
    projectDocumentations: PaginatedProjectDocumentations;
    can: {
        create: boolean;
    };
    filters: ProjectDocumentationFilterValues;
    categories: Array<{ value: number; label: string }>;
    types: Array<{ value: number; label: string }>;
    visibilities: Array<{ value: number; label: string }>;
    isAdmin: boolean;
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projeto',
                href: index(),
            },
            {
                title: 'Documentações',
                href: index(),
            },
        ],
    },
});

const viewMode = ref<'grid' | 'list'>('grid');
const filterForm = reactive<ProjectDocumentationFilterValues>({ ...props.filters });

function submitFilters(): void {
    const query = Object.fromEntries(Object.entries(filterForm).filter(([, value]) => value !== '' && value !== '0' && value !== 'all'));

    router.get(index.url(), query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function openLink(url: string): void {
    window.open(url, '_blank', 'noreferrer');
}

function downloadFile(id: number): void {
    window.location.href = download({ projectDocumentation: id }).url;
}

function clearFilters(): void {
    filterForm.search = '';
    filterForm.type = '0';
    filterForm.category = '0';
    filterForm.visibility = '0';

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
    <Head title="Documentações do projeto" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                variant="small"
                title="Documentações"
                description="Gerencie materiais relacionados ao projeto selecionado."
            />

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

                <ProjectDocumentationFormDialog v-if="can.create" :categories="categories" :types="types" :visibilities="visibilities">
                    <template #trigger>
                        <Button><Plus class="size-4" /> Nova documentação</Button>
                    </template>
                </ProjectDocumentationFormDialog>
            </div>
        </div>

        <ProjectDocumentationFilters
            v-model:filters="filterForm"
            :types="types"
            :categories="categories"
            :visibilities="visibilities"
            :is-admin="isAdmin"
            @submit="submitFilters"
            @clear="clearFilters"
        />

        <div
            v-if="projectDocumentations.data.length === 0"
            class="rounded-xl border border-sidebar-border/70 bg-card px-4 py-10 text-center text-muted-foreground shadow-xs dark:border-sidebar-border"
        >
            Nenhuma documentação encontrada para o projeto selecionado.
        </div>

        <ProjectDocumentationGrid
            v-else-if="viewMode === 'grid'"
            :project-documentations="projectDocumentations.data"
            :categories="categories"
            :types="types"
            :visibilities="visibilities"
            :is-admin="isAdmin"
        />

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
        >
            <Table>
                <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                    <TableRow>
                        <TableHead class="px-4 py-3">Título</TableHead>
                        <TableHead class="px-4 py-3">Tipo</TableHead>
                        <TableHead class="px-4 py-3">Categoria</TableHead>
                        <TableHead v-if="isAdmin" class="px-4 py-3">Visibilidade</TableHead>
                        <TableHead class="px-4 py-3">Autor</TableHead>
                        <TableHead class="px-4 py-3">Criado em</TableHead>
                        <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="projectDocumentation in projectDocumentations.data" :key="projectDocumentation.id">
                        <TableCell class="px-4 py-4 font-medium">
                            <a
                                v-if="projectDocumentation.type === 3"
                                class="hover:underline"
                                :href="projectDocumentation.url ?? undefined"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {{ projectDocumentation.title }}
                            </a>
                            <span v-else class="cursor-pointer text-foreground hover:underline" @click="downloadFile(projectDocumentation.id)">
                                {{ projectDocumentation.title }}
                            </span>
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectDocumentation.type_label }}
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            {{ projectDocumentation.category_label }}
                        </TableCell>
                        <TableCell v-if="isAdmin" class="px-4 py-4">
                            {{ projectDocumentation.visibility_label }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectDocumentation.author.name }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectDocumentation.created_at }}
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            <div class="flex items-center justify-end gap-1">
                            <Button
                                v-if="projectDocumentation.type === 3"
                                variant="outline"
                                size="sm"
                                title="Abrir link"
                                @click="openLink(projectDocumentation.url ?? '')"
                            >
                                <ArrowUpRight class="size-4" />
                                Abrir
                            </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    size="icon"
                                    title="Baixar arquivo"
                                    @click="downloadFile(projectDocumentation.id)"
                                >
                                    <Download class="size-4" />
                                </Button>
                                <ProjectDocumentationFormDialog
                                    v-if="projectDocumentation.can.update"
                                    :project-documentation="projectDocumentation"
                                    :categories="categories"
                                    :types="types"
                                    :visibilities="visibilities"
                                >
                                    <template #trigger>
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            data-testid="edit-button"
                                            title="Editar"
                                        >
                                            <Pencil class="size-4" />
                                        </Button>
                                    </template>
                                </ProjectDocumentationFormDialog>
                                <ProjectDocumentationDeleteAction :project-documentation="projectDocumentation" icon-only />
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <ProjectPagination :meta="projectDocumentations.meta" item-label="documentações" />
    </div>
</template>
