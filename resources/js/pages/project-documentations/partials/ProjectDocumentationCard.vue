<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { CalendarDays, ClipboardCopy, FileText, Pencil, Trash2, UserRound } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { destroy, edit, show } from '@/routes/project/documentations';
import type { ProjectDocumentation } from '@/types';

const props = defineProps<{
    projectDocumentation: ProjectDocumentation;
}>();

function copyLink(): void {
    if (props.projectDocumentation.url) {
        navigator.clipboard.writeText(props.projectDocumentation.url);
    }
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
                <Link
                    v-else
                    class="font-semibold hover:underline"
                    :href="show({ projectDocumentation: projectDocumentation.id })"
                >
                    {{ projectDocumentation.title }}
                </Link>
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
                data-testid="copy-link-button"
                title="Copiar link"
                @click="copyLink"
            >
                <ClipboardCopy class="size-4" />
            </Button>

            <Button
                v-if="projectDocumentation.can.update"
                variant="outline"
                size="icon"
                tone="primary"
                data-testid="edit-button"
                title="Editar"
                @click="router.visit(edit({ projectDocumentation: projectDocumentation.id }))"
            >
                <Pencil class="size-4" />
            </Button>

            <Button
                v-if="projectDocumentation.can.delete"
                variant="outline"
                tone="destructive"
                size="icon"
                data-testid="delete-button"
                title="Excluir"
                @click="router.delete(destroy({ projectDocumentation: projectDocumentation.id }))"
            >
                <Trash2 class="size-4" />
            </Button>

            <span v-if="projectDocumentation.type !== 3 && !projectDocumentation.can.update && !projectDocumentation.can.delete" class="text-sm text-muted-foreground">
                Sem ações
            </span>
        </div>
    </article>
</template>
