<script setup lang="ts">
import type { Project } from '@/types';
import ProjectCard from './ProjectCard.vue';

defineProps<{
    projects: Project[];
}>();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <ProjectCard v-for="project in projects" :key="project.id" :project="project" />
    </div>
</template>
