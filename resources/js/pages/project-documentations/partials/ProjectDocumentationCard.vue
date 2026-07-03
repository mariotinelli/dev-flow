<script setup lang="ts">
import { CalendarDays, ExternalLink, FileText, UserRound } from '@lucide/vue';
import { Link } from '@inertiajs/vue3';
import { show } from '@/routes/project/documentations';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { ProjectDocumentation } from '@/types';

defineProps<{
    projectDocumentation: ProjectDocumentation;
}>();
</script>

<template>
    <article
        class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="flex min-w-0 items-start justify-between gap-3">
            <div class="min-w-0">
                <Link
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

        <div class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <span v-if="!projectDocumentation.can.update && !projectDocumentation.can.delete" class="text-sm text-muted-foreground">
                Sem ações
            </span>
        </div>
    </article>
</template>
