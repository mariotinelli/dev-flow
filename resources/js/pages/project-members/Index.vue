<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { LayoutGrid, List, Plus } from '@lucide/vue';
import { reactive, ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { index } from '@/routes/project/members';
import type { PaginatedProjectMembers, ProjectMemberFilterValues, ProjectMemberSelectOption } from '@/types';
import ProjectPagination from '../projects/partials/ProjectPagination.vue';
import ProjectMemberDeleteAction from './partials/ProjectMemberDeleteAction.vue';
import ProjectMemberFilters from './partials/ProjectMemberFilters.vue';
import ProjectMemberFormDialog from './partials/ProjectMemberFormDialog.vue';
import ProjectMemberGrid from './partials/ProjectMemberGrid.vue';

const props = defineProps<{
    projectMembers: PaginatedProjectMembers;
    can: {
        create: boolean;
    };
    filters: ProjectMemberFilterValues;
    users: ProjectMemberSelectOption[];
    projectRoles: ProjectMemberSelectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projeto',
                href: index(),
            },
            {
                title: 'Membros',
                href: index(),
            },
        ],
    },
});

const viewMode = ref<'grid' | 'list'>('grid');
const filterForm = reactive<ProjectMemberFilterValues>({ ...props.filters });

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

function formatDate(value: string | null): string {
    if (!value) {
        return '-';
    }

    return new Intl.DateTimeFormat('pt-BR', { dateStyle: 'short' }).format(new Date(`${value}T00:00:00`));
}
</script>

<template>
    <Head title="Membros do projeto" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                variant="small"
                title="Membros"
                description="Gerencie usuários vinculados ao projeto selecionado."
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

                <ProjectMemberFormDialog v-if="can.create" mode="create" :users="users" :project-roles="projectRoles">
                    <template #trigger>
                        <Button><Plus class="size-4" /> Novo membro</Button>
                    </template>
                </ProjectMemberFormDialog>
            </div>
        </div>

        <ProjectMemberFilters v-model:filters="filterForm" @submit="submitFilters" @clear="clearFilters" />

        <div
            v-if="projectMembers.data.length === 0"
            class="rounded-xl border border-sidebar-border/70 bg-card px-4 py-10 text-center text-muted-foreground shadow-xs dark:border-sidebar-border"
        >
            Nenhum membro encontrado para o projeto selecionado.
        </div>

        <ProjectMemberGrid
            v-else-if="viewMode === 'grid'"
            :project-members="projectMembers.data"
            :users="users"
            :project-roles="projectRoles"
            :format-date="formatDate"
        />

        <div
            v-else
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
        >
            <Table>
                <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                    <TableRow>
                        <TableHead class="px-4 py-3">Nome</TableHead>
                        <TableHead class="px-4 py-3">E-mail</TableHead>
                        <TableHead class="px-4 py-3">Papel</TableHead>
                        <TableHead class="px-4 py-3">Membro desde</TableHead>
                        <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="projectMember in projectMembers.data" :key="projectMember.id">
                        <TableCell class="px-4 py-4 font-medium">
                            {{ projectMember.user.name }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ projectMember.user.email }}
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            {{ projectMember.project_role.name }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ formatDate(projectMember.entered_at) }}
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <ProjectMemberFormDialog
                                    v-if="projectMember.can.update"
                                    mode="edit"
                                    :project-member="projectMember"
                                    :users="users"
                                    :project-roles="projectRoles"
                                >
                                    <template #trigger>
                                        <Button variant="outline" size="sm">Editar</Button>
                                    </template>
                                </ProjectMemberFormDialog>

                                <ProjectMemberDeleteAction :project-member="projectMember" />

                                <span
                                    v-if="!projectMember.can.update && !projectMember.can.delete"
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

        <ProjectPagination :meta="projectMembers.meta" item-label="membros" />
    </div>
</template>
