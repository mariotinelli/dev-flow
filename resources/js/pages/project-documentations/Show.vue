<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { Download } from '@lucide/vue';
import { ref } from 'vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import { download } from '@/routes/project/documentations';

const props = defineProps<{
    projectDocumentation: {
        id: number;
        title: string;
        description: string | null;
        type: number;
        type_label: string;
        category_label: string;
        url: string | null;
        download_url: string | null;
    };
}>();

const imageLoaded = ref(false);
const imageError = ref(false);

const ProjectDocumentationType = {
    File: 1,
    Image: 2,
    Link: 3,
} as const;
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6">
        <Head :title="projectDocumentation.title" />

        <Heading
            :title="projectDocumentation.title"
            :description="projectDocumentation.description ?? 'Material relacionado ao projeto selecionado.'"
        />

        <div
            v-if="projectDocumentation.type === ProjectDocumentationType.Image && projectDocumentation.download_url"
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
        >
            <div v-if="!imageLoaded && !imageError" class="aspect-video animate-pulse bg-muted" />

            <img
                v-show="imageLoaded"
                :src="projectDocumentation.download_url"
                :alt="projectDocumentation.title"
                class="w-full object-contain"
                :class="{ hidden: !imageLoaded }"
                @load="imageLoaded = true"
                @error="imageError = true"
            />

            <div
                v-if="imageError"
                class="flex aspect-video items-center justify-center bg-muted/50 text-sm text-muted-foreground"
            >
                Não foi possível carregar a imagem.
            </div>
        </div>

        <div class="rounded-xl border border-sidebar-border/70 bg-card p-6 shadow-xs dark:border-sidebar-border">
            <dl class="grid gap-4 text-sm sm:grid-cols-2">
                <div>
                    <dt class="font-medium text-muted-foreground">Tipo</dt>
                    <dd>{{ projectDocumentation.type_label }}</dd>
                </div>
                <div>
                    <dt class="font-medium text-muted-foreground">Categoria</dt>
                    <dd>{{ projectDocumentation.category_label }}</dd>
                </div>

                <div v-if="projectDocumentation.type === ProjectDocumentationType.File && projectDocumentation.download_url" class="sm:col-span-2">
                    <dt class="font-medium text-muted-foreground">Arquivo</dt>
                    <dd>
                        <Link :href="download({ projectDocumentation: projectDocumentation.id })">
                            <Button variant="outline" size="sm">
                                <Download class="size-4" />
                                Baixar arquivo
                            </Button>
                        </Link>
                    </dd>
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
