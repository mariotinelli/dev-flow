<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import { edit } from '@/routes/users';
import type { User } from '@/types';
import UserStatusAction from './UserStatusAction.vue';

defineProps<{
    user: User;
}>();
</script>

<template>
    <article
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <Avatar class="size-12">
                    <AvatarImage v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" />
                    <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                </Avatar>

                <div class="min-w-0">
                    <h2 class="truncate font-semibold">
                        {{ user.name }}
                    </h2>
                    <p class="truncate text-sm text-muted-foreground">
                        {{ user.email }}
                    </p>
                </div>
            </div>

            <Badge :variant="user.is_active ? 'secondary' : 'outline'">
                {{ user.status_label }}
            </Badge>
        </div>

        <div class="space-y-3">
            <div>
                <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Perfil</p>
                <p class="text-sm">{{ user.role }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Badge>
                    {{ user.job_title_label }}
                </Badge>
                <Badge variant="secondary">
                    {{ user.contract_type_label }}
                </Badge>
                <Badge variant="outline">
                    {{ user.seniority_label }}
                </Badge>
            </div>
        </div>

        <div v-if="user.is_active" class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <Button v-if="user.can.update" variant="outline" size="sm" as-child>
                <Link :href="edit(user.id)">Editar</Link>
            </Button>

            <UserStatusAction :user="user" />
        </div>
        <div v-else class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <UserStatusAction :user="user" />
        </div>
    </article>
</template>
