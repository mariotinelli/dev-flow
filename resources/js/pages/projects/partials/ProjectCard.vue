<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { CalendarDays, FolderKanban } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { edit } from '@/routes/projects';
import type { Project } from '@/types';
import ProjectSituationAction from './ProjectSituationAction.vue';

defineProps<{
    project: Project;
}>();
</script>

<template>
    <article
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <div
                    class="flex size-12 shrink-0 items-center justify-center rounded-xl border text-sm font-semibold"
                    :style="{ backgroundColor: project.color ?? undefined }"
                >
                    <FolderKanban class="size-5" :class="project.color ? 'text-white' : 'text-muted-foreground'" />
                </div>

                <div class="min-w-0">
                    <h2 class="truncate font-semibold">{{ project.name }}</h2>
                    <p class="truncate text-sm text-muted-foreground">Identificador: {{ project.key }}</p>
                </div>
            </div>

            <Badge :variant="project.is_active ? 'secondary' : 'outline'">
                {{ project.is_active ? 'Ativo' : 'Inativo' }}
            </Badge>
        </div>

        <div class="grid gap-2 text-sm text-muted-foreground sm:grid-cols-2">
            <div class="flex items-center gap-2">
                <CalendarDays class="size-4" />
                <span>Início: {{ project.starts_at ?? 'Sem data' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <CalendarDays class="size-4" />
                <span>Prazo: {{ project.due_at ?? 'Sem prazo' }}</span>
            </div>
        </div>

        <div class="rounded-lg border bg-muted/30 p-3 text-sm text-muted-foreground">
            {{
                project.is_active
                    ? 'Projeto disponível para atualizações.'
                    : 'Projeto arquivado e disponível para restauração.'
            }}
        </div>

        <div class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <Button v-if="project.is_active && project.can.update" variant="outline" size="sm" as-child>
                <Link :href="edit(project.id)">Editar</Link>
            </Button>

            <ProjectSituationAction :project="project" />
        </div>
    </article>
</template>
