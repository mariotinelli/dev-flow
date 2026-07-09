<script setup lang="ts">
import { CalendarDays, UserRound } from '@lucide/vue';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import type { ProjectMember, ProjectMemberSelectOption } from '@/types';
import ProjectMemberDeleteAction from './ProjectMemberDeleteAction.vue';
import ProjectMemberFormDialog from './ProjectMemberFormDialog.vue';

defineProps<{
    projectMember: ProjectMember;
    users: ProjectMemberSelectOption[];
    projectRoles: ProjectMemberSelectOption[];
    formatDate: (value: string | null) => string;
}>();
</script>

<template>
    <article
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="flex min-w-0 items-center gap-3">
            <Avatar class="size-12">
                <AvatarFallback>{{ getInitials(projectMember.user.name) }}</AvatarFallback>
            </Avatar>

            <div class="min-w-0">
                <h2 class="truncate font-semibold">
                    {{ projectMember.user.name }}
                </h2>
                <p class="truncate text-sm text-muted-foreground">
                    {{ projectMember.user.email }}
                </p>
            </div>
        </div>

        <div class="grid gap-3 text-sm text-muted-foreground">
            <div class="flex items-center gap-2">
                <UserRound class="size-4" />
                <span>Papel: {{ projectMember.project_role.name }}</span>
            </div>
            <div class="flex items-center gap-2">
                <CalendarDays class="size-4" />
                <span>Membro desde: {{ formatDate(projectMember.entered_at) }}</span>
            </div>
        </div>

        <div class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <ProjectMemberFormDialog
                v-if="projectMember.can.update"
                mode="edit"
                :project-member="projectMember"
                :users="users"
                :project-roles="projectRoles"
            >
                <template #trigger>
                    <Button variant="outline" size="sm">Editar</Button>
                </template>
            </ProjectMemberFormDialog>

            <ProjectMemberDeleteAction :project-member="projectMember" />

            <span v-if="!projectMember.can.update && !projectMember.can.delete" class="text-sm text-muted-foreground">
                Sem ações
            </span>
        </div>
    </article>
</template>
