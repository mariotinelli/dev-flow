<script setup lang="ts">
import type { ProjectMember, ProjectMemberSelectOption } from '@/types';
import ProjectMemberCard from './ProjectMemberCard.vue';

defineProps<{
    projectMembers: ProjectMember[];
    users: ProjectMemberSelectOption[];
    projectRoles: ProjectMemberSelectOption[];
    formatDate: (value: string | null) => string;
}>();
</script>

<template>
    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        <ProjectMemberCard
            v-for="projectMember in projectMembers"
            :key="projectMember.id"
            :project-member="projectMember"
            :users="users"
            :project-roles="projectRoles"
            :format-date="formatDate"
        />
    </div>
</template>
