<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';

const props = defineProps<{
    projectDocumentation: {
        title: string;
        description: string | null;
        type_label: string;
        category_label: string;
        url: string | null;
    };
}>();
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6">
        <Head :title="projectDocumentation.title" />

        <Heading
            :title="projectDocumentation.title"
            :description="projectDocumentation.description ?? 'Material relacionado ao projeto selecionado.'"
        />

        <div class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs dark:border-sidebar-border">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-medium text-muted-foreground">Tipo</dt>
                    <dd>{{ props.projectDocumentation.type_label }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-muted-foreground">Categoria</dt>
                    <dd>{{ props.projectDocumentation.category_label }}</dd>
                </div>
                <div v-if="projectDocumentation.url" class="sm:col-span-2">
                    <dt class="font-medium text-muted-foreground">Link</dt>
                    <dd>
                        <a class="text-primary underline-offset-4 hover:underline" :href="projectDocumentation.url" target="_blank" rel="noreferrer">
                            {{ projectDocumentation.url }}
                        </a>
                    </dd>
                </div>
            </dl>
        </div>
    </div>
</template>
