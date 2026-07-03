<script setup lang="ts">
import type { ProjectDocumentation } from '@/types';
import ProjectDocumentationCard from './ProjectDocumentationCard.vue';

defineProps<{
    projectDocumentations: ProjectDocumentation[];
}>();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <ProjectDocumentationCard
            v-for="projectDocumentation in projectDocumentations"
            :key="projectDocumentation.id"
            :project-documentation="projectDocumentation"
        />
    </div>
</template>
