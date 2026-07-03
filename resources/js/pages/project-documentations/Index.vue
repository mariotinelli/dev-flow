<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { LayoutGrid, List, Plus } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index, show } from '@/routes/project/documentations';
import type { PaginatedProjectDocumentations } from '@/types';
import ProjectPagination from '../projects/partials/ProjectPagination.vue';
import ProjectDocumentationFormDialog from './partials/ProjectDocumentationFormDialog.vue';
import ProjectDocumentationGrid from './partials/ProjectDocumentationGrid.vue';

const props = defineProps<{
    projectDocumentations: PaginatedProjectDocumentations;
    can: {
        create: boolean;
    };
    filters: {
        search: string;
    };
    categories: Array<{ value: number; label: string }>;
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
const filterForm = reactive<{ search: string }>({ ...props.filters });

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

                <ProjectDocumentationFormDialog v-if="can.create" :categories="categories">
                    <template #trigger>
                        <Button><Plus class="size-4" /> Nova documentação</Button>
                    </template>
                </ProjectDocumentationFormDialog>
            </div>
        </div>

        <div
            v-if="projectDocumentations.data.length === 0"
            class="rounded-xl border border-sidebar-border/70 bg-card px-4 py-10 text-center text-muted-foreground shadow-xs dark:border-sidebar-border"
        >
            Nenhuma documentação encontrada para o projeto selecionado.
        </div>

        <ProjectDocumentationGrid
            v-else-if="viewMode === 'grid'"
            :project-documentations="projectDocumentations.data"
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
                        <TableHead class="px-4 py-3">Autor</TableHead>
                        <TableHead class="px-4 py-3">Criado em</TableHead>
                        <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="projectDocumentation in projectDocumentations.data" :key="projectDocumentation.id">
                        <TableCell class="px-4 py-4 font-medium">
                            <Link
                                class="hover:underline"
                                :href="show({ projectDocumentation: projectDocumentation.id })"
                            >
                                {{ projectDocumentation.title }}
                            </Link>
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectDocumentation.type_label }}
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            {{ projectDocumentation.category_label }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectDocumentation.author.name }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectDocumentation.created_at }}
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <span
                                    v-if="!projectDocumentation.can.update && !projectDocumentation.can.delete"
                                    class="text-sm text-muted-foreground"
                                >
                                    Sem ações
                                </span>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <ProjectPagination :meta="projectDocumentations.meta" item-label="documentações" />
    </div>
</template>
