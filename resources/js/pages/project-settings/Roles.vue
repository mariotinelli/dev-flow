<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { reactive } from 'vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { create, edit, index } from '@/routes/project-roles';
import type {
    PaginatedProjectRoles,
    ProjectRoleDeletedStatusOption,
    ProjectRoleFilterValues,
    ProjectRoleSourceProject,
} from '@/types';
import ProjectRoleCopyDialog from '../project-roles/partials/ProjectRoleCopyDialog.vue';
import ProjectRoleFilters from '../project-roles/partials/ProjectRoleFilters.vue';
import ProjectRoleSituationAction from '../project-roles/partials/ProjectRoleSituationAction.vue';
import ProjectPagination from '../projects/partials/ProjectPagination.vue';

const props = defineProps<{
    projectRoles: PaginatedProjectRoles;
    can: {
        create: boolean;
    };
    filters: ProjectRoleFilterValues;
    deletedStatuses: ProjectRoleDeletedStatusOption[];
    sourceProjects: ProjectRoleSourceProject[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Configurações do Projeto',
                href: index(),
            },
            {
                title: 'Papéis',
                href: index(),
            },
        ],
    },
});

const filterForm = reactive<ProjectRoleFilterValues>({ ...props.filters });

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
    <Head title="Papéis do projeto" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <Heading
                variant="small"
                title="Papéis"
                description="Gerencie papéis e permissões disponíveis para membros do projeto."
            />

            <div v-if="can.create" class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <ProjectRoleCopyDialog :source-projects="sourceProjects" />

                <Button as-child>
                    <Link :href="create()"><Plus class="size-4" /> Novo papel</Link>
                </Button>
            </div>
        </div>

        <ProjectRoleFilters
            v-model:filters="filterForm"
            :deleted-statuses="deletedStatuses"
            @submit="submitFilters"
            @clear="clearFilters"
        />

        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
        >
            <Table>
                <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                    <TableRow>
                        <TableHead class="px-4 py-3">Nome</TableHead>
                        <TableHead class="px-4 py-3">Permissões</TableHead>
                        <TableHead class="px-4 py-3">Situação</TableHead>
                        <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-if="projectRoles.data.length === 0">
                        <TableCell colspan="4" class="px-4 py-10 text-center text-muted-foreground">
                            Nenhum papel encontrado para o projeto selecionado.
                        </TableCell>
                    </TableRow>
                    <template v-else>
                        <TableRow v-for="projectRole in projectRoles.data" :key="projectRole.id">
                            <TableCell class="px-4 py-4 font-medium">
                                {{ projectRole.name }}
                            </TableCell>
                            <TableCell class="px-4 py-4 text-muted-foreground">
                                {{ projectRole.permissions_count }} permissões
                            </TableCell>
                            <TableCell class="px-4 py-4">
                                <Badge :variant="projectRole.is_active ? 'secondary' : 'outline'">
                                    {{ projectRole.is_active ? 'Ativo' : 'Inativo' }}
                                </Badge>
                            </TableCell>
                            <TableCell class="px-4 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        v-if="projectRole.is_active && projectRole.can.update"
                                        variant="outline"
                                        size="sm"
                                        as-child
                                    >
                                        <Link :href="edit(projectRole.id)">Editar</Link>
                                    </Button>

                                    <ProjectRoleSituationAction :project-role="projectRole" />

                                    <span
                                        v-if="
                                            !projectRole.can.update &&
                                            !projectRole.can.delete &&
                                            !projectRole.can.restore
                                        "
                                        class="text-sm text-muted-foreground"
                                    >
                                        Sem ações
                                    </span>
                                </div>
                            </TableCell>
                        </TableRow>
                    </template>
                </TableBody>
            </Table>
        </div>

        <ProjectPagination :meta="projectRoles.meta" item-label="papéis" />
    </div>
</template>
