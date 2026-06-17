<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { getInitials } from '@/composables/useInitials';
import { edit } from '@/routes/developers';
import type { Developer } from '@/types';
import DeveloperStatusAction from './DeveloperStatusAction.vue';

defineProps<{
    developer: Developer;
}>();
</script>

<template>
    <article
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
    >
        <div class="flex items-start justify-between gap-4">
            <div class="flex min-w-0 items-center gap-3">
                <Avatar class="size-12">
                    <AvatarImage v-if="developer.avatar_url" :src="developer.avatar_url" :alt="developer.name" />
                    <AvatarFallback>{{ getInitials(developer.name) }}</AvatarFallback>
                </Avatar>

                <div class="min-w-0">
                    <h2 class="truncate font-semibold">
                        {{ developer.name }}
                    </h2>
                    <p class="truncate text-sm text-muted-foreground">
                        {{ developer.email }}
                    </p>
                </div>
            </div>

            <Badge :variant="developer.is_active ? 'secondary' : 'outline'">
                {{ developer.status_label }}
            </Badge>
        </div>

        <div class="space-y-3">
            <div>
                <p class="text-xs font-medium tracking-wide text-muted-foreground uppercase">Cargo/Função</p>
                <p class="text-sm">{{ developer.role }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <Badge variant="secondary">
                    {{ developer.contract_type_label }}
                </Badge>
                <Badge variant="outline">
                    {{ developer.seniority_label }}
                </Badge>
            </div>
        </div>

        <div v-if="developer.is_active" class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <Button variant="outline" size="sm" as-child>
                <Link :href="edit(developer.id)">Editar</Link>
            </Button>

            <DeveloperStatusAction :developer="developer" />
        </div>
        <div v-else class="mt-auto flex items-center justify-end gap-2 border-t pt-4">
            <DeveloperStatusAction :developer="developer" />
        </div>
    </article>
</template>
