<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { edit } from '@/routes/projects';
import type { Project } from '@/types';
import ProjectStatusAction from './ProjectStatusAction.vue';

defineProps<{
    projects: Project[];
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
    >
        <Table class="min-w-4xl">
            <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                <TableRow>
                    <TableHead class="px-4 py-3">Projeto</TableHead>
                    <TableHead class="px-4 py-3">Status</TableHead>
                    <TableHead class="px-4 py-3">Visibilidade</TableHead>
                    <TableHead class="px-4 py-3">Prazo</TableHead>
                    <TableHead class="px-4 py-3">Situação</TableHead>
                    <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="project in projects" :key="project.id">
                    <TableCell class="px-4 py-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <span
                                class="size-3 rounded-full"
                                :style="{ backgroundColor: project.color ?? '#94a3b8' }"
                            />
                            <div class="min-w-0">
                                <p class="truncate font-medium">{{ project.name }}</p>
                                <p class="truncate text-muted-foreground">Identificador: {{ project.key }}</p>
                                <p class="truncate text-xs text-muted-foreground">Slug: {{ project.slug }}</p>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="px-4 py-4"
                        ><Badge>{{ project.status_label }}</Badge></TableCell
                    >
                    <TableCell class="px-4 py-4"
                        ><Badge variant="secondary">{{ project.visibility_label }}</Badge></TableCell
                    >
                    <TableCell class="px-4 py-4 text-muted-foreground">{{ project.due_at ?? 'Sem prazo' }}</TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge :variant="project.is_active ? 'secondary' : 'outline'">
                            {{ project.is_active ? 'Ativo' : 'Inativo' }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <div class="flex items-center justify-end gap-2">
                            <Button v-if="project.is_active && project.can.update" variant="outline" size="sm" as-child>
                                <Link :href="edit(project.id)">Editar</Link>
                            </Button>

                            <ProjectStatusAction :project="project" />
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
