<script setup lang="ts">
import { ArrowUpRight, CalendarDays, Download, FileText, Pencil, UserRound } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { download } from '@/routes/project/documentations';
import type { ProjectDocumentation } from '@/types';
import ProjectDocumentationDeleteAction from './ProjectDocumentationDeleteAction.vue';
import ProjectDocumentationFormDialog from './ProjectDocumentationFormDialog.vue';

const props = defineProps<{
    projectDocumentation: ProjectDocumentation;
    categories: Array<{ value: number; label: string }>;
    types: Array<{ value: number; label: string }>;
    visibilities: Array<{ value: number; label: string }>;
    isAdmin: boolean;
}>();

function openLink(): void {
    if (props.projectDocumentation.url) {
        window.open(props.projectDocumentation.url, '_blank', 'noreferrer');
    }
}

function downloadFile(id: number): void {
    window.location.href = download({ projectDocumentation: id }).url;
}
</script>

<template>
    <article
        class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="flex min-w-0 items-start justify-between gap-3">
            <div class="min-w-0">
                <a
                    v-if="projectDocumentation.type === 3"
                    class="font-semibold hover:underline"
                    :href="projectDocumentation.url ?? undefined"
                    target="_blank"
                    rel="noreferrer"
                >
                    {{ projectDocumentation.title }}
                </a>
                <span
                    v-else
                    class="cursor-pointer font-semibold hover:underline"
                    @click="downloadFile(projectDocumentation.id)"
                >
                    {{ projectDocumentation.title }}
                </span>
                <p v-if="projectDocumentation.description" class="mt-1 truncate text-sm text-muted-foreground">
                    {{ projectDocumentation.description }}
                </p>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <Badge variant="secondary">
                <FileText class="mr-1 size-3" />
                {{ projectDocumentation.type_label }}
            </Badge>
            <Badge variant="outline">
                {{ projectDocumentation.category_label }}
            </Badge>
            <Badge v-if="isAdmin" variant="default">
                {{ projectDocumentation.visibility_label }}
            </Badge>
        </div>

        <div class="grid gap-2 text-sm text-muted-foreground">
            <div class="flex items-center gap-2">
                <UserRound class="size-4" />
                <span>{{ projectDocumentation.author.name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <CalendarDays class="size-4" />
                <span>{{ projectDocumentation.created_at }}</span>
            </div>
        </div>

        <div class="mt-auto flex items-center justify-end gap-1 border-t pt-4">
            <Button
                v-if="projectDocumentation.type === 3"
                variant="outline"
                size="icon"
                data-testid="open-link-button"
                title="Abrir link"
                @click="openLink"
            >
                <ArrowUpRight class="size-4" />
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
                    <Button variant="outline" size="icon" data-testid="edit-button" title="Editar">
                        <Pencil class="size-4" />
                    </Button>
                </template>
            </ProjectDocumentationFormDialog>

            <ProjectDocumentationDeleteAction :project-documentation="projectDocumentation" icon-only />
        </div>
    </article>
</template>
