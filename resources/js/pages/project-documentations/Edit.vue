<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import type { ProjectDocumentation } from '@/types';

defineProps<{
    projectDocumentation: ProjectDocumentation;
    categories: Array<{ value: number; label: string }>;
}>();
</script>

<template>
    <Head :title="'Editar: ' + projectDocumentation.title" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <Heading
            variant="small"
            title="Editar documentação"
            :description="projectDocumentation.title"
        />
    </div>
</template>
