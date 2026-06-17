<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { getInitials } from '@/composables/useInitials';
import { edit } from '@/routes/users';
import type { User } from '@/types';
import UserStatusAction from './UserStatusAction.vue';

defineProps<{
    users: User[];
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
    >
        <Table class="min-w-3xl">
            <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                <TableRow>
                    <TableHead class="px-4 py-3">Nome</TableHead>
                    <TableHead class="px-4 py-3">Cargo</TableHead>
                    <TableHead class="px-4 py-3">Contrato</TableHead>
                    <TableHead class="px-4 py-3">Senioridade</TableHead>
                    <TableHead class="px-4 py-3">Status</TableHead>
                    <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="user in users" :key="user.id">
                    <TableCell class="px-4 py-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <Avatar class="size-10">
                                <AvatarImage
                                    v-if="user.avatar_url"
                                    :src="user.avatar_url"
                                    :alt="user.name"
                                />
                                <AvatarFallback>
                                    {{ getInitials(user.name) }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ user.name }}
                                </p>
                                <p class="truncate text-muted-foreground">
                                    {{ user.email }}
                                </p>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="px-4 py-4 text-muted-foreground">
                        {{ user.job_title_label }}
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge variant="secondary">
                            {{ user.contract_type_label }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge variant="outline">
                            {{ user.seniority_label }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge :variant="user.is_active ? 'secondary' : 'outline'">
                            {{ user.status_label }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <div v-if="user.is_active" class="flex items-center justify-end gap-2">
                            <Button v-if="user.can.update" variant="outline" size="sm" as-child>
                                <Link :href="edit(user.id)">Editar</Link>
                            </Button>

                            <UserStatusAction :user="user" />
                        </div>
                        <div v-else class="flex items-center justify-end">
                            <UserStatusAction :user="user" />
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
