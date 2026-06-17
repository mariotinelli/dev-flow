<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { getInitials } from '@/composables/useInitials';
import { edit } from '@/routes/developers';
import type { Developer } from '@/types';
import DeveloperStatusAction from './DeveloperStatusAction.vue';

defineProps<{
    developers: Developer[];
}>();
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
    >
        <Table class="min-w-3xl">
            <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                <TableRow>
                    <TableHead class="px-4 py-3"> Desenvolvedor </TableHead>
                    <TableHead class="px-4 py-3">Cargo/Função</TableHead>
                    <TableHead class="px-4 py-3">Contrato</TableHead>
                    <TableHead class="px-4 py-3">Senioridade</TableHead>
                    <TableHead class="px-4 py-3">Status</TableHead>
                    <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <TableRow v-for="developer in developers" :key="developer.id">
                    <TableCell class="px-4 py-4">
                        <div class="flex min-w-0 items-center gap-3">
                            <Avatar class="size-10">
                                <AvatarImage
                                    v-if="developer.avatar_url"
                                    :src="developer.avatar_url"
                                    :alt="developer.name"
                                />
                                <AvatarFallback>
                                    {{ getInitials(developer.name) }}
                                </AvatarFallback>
                            </Avatar>

                            <div class="min-w-0">
                                <p class="truncate font-medium">
                                    {{ developer.name }}
                                </p>
                                <p class="truncate text-muted-foreground">
                                    {{ developer.email }}
                                </p>
                            </div>
                        </div>
                    </TableCell>
                    <TableCell class="px-4 py-4 text-muted-foreground">
                        {{ developer.role }}
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge variant="secondary">
                            {{ developer.contract_type_label }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge variant="outline">
                            {{ developer.seniority_label }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <Badge :variant="developer.is_active ? 'secondary' : 'outline'">
                            {{ developer.status_label }}
                        </Badge>
                    </TableCell>
                    <TableCell class="px-4 py-4">
                        <div v-if="developer.is_active" class="flex items-center justify-end gap-2">
                            <Button v-if="developer.can.update" variant="outline" size="sm" as-child>
                                <Link :href="edit(developer.id)">Editar</Link>
                            </Button>

                            <DeveloperStatusAction :developer="developer" />
                        </div>
                        <div v-else class="flex items-center justify-end">
                            <DeveloperStatusAction :developer="developer" />
                        </div>
                    </TableCell>
                </TableRow>
            </TableBody>
        </Table>
    </div>
</template>
