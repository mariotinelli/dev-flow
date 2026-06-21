<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus } from '@lucide/vue';
import { ref } from 'vue';
import DestroyController from '@/actions/App/Http/Controllers/Roles/DestroyController';
import Heading from '@/components/Heading.vue';
import {
    AlertDialog,
    AlertDialogAction,
    AlertDialogCancel,
    AlertDialogContent,
    AlertDialogDescription,
    AlertDialogFooter,
    AlertDialogHeader,
    AlertDialogTitle,
    AlertDialogTrigger,
} from '@/components/ui/alert-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip';
import { create, edit, index } from '@/routes/roles';
import type { Role } from '@/types';

defineProps<{
    roles: Role[];
    can: {
        create: boolean;
    };
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Perfis',
                href: index(),
            },
        ],
    },
});

const roleToDelete = ref<Role | null>(null);

function destroyRole(): void {
    if (!roleToDelete.value) {
        return;
    }

    router.delete(DestroyController.url(roleToDelete.value.id), {
        preserveScroll: true,
        onFinish: () => {
            roleToDelete.value = null;
        },
    });
}
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6">
        <Head title="Perfis" />

        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <Heading title="Perfis" description="Gerencie perfis e permissões que podem ser atribuídos aos usuários." />

            <Button v-if="can.create" as-child>
                <Link :href="create()">
                    <Plus class="size-4" />
                    Novo perfil
                </Link>
            </Button>
        </div>

        <div
            class="overflow-hidden rounded-xl border border-sidebar-border/70 bg-card shadow-xs dark:border-sidebar-border"
        >
            <Table>
                <TableHeader class="bg-muted/50 text-xs tracking-wide text-muted-foreground uppercase">
                    <TableRow>
                        <TableHead class="px-4 py-3">Nome</TableHead>
                        <TableHead class="px-4 py-3">Permissões</TableHead>
                        <TableHead class="px-4 py-3">Uso</TableHead>
                        <TableHead class="px-4 py-3">Status</TableHead>
                        <TableHead class="px-4 py-3 text-right">Ações</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="role in roles" :key="role.id">
                        <TableCell class="px-4 py-4 font-medium">
                            {{ role.name }}
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground">
                            {{ role.permissions_count }} permissões
                        </TableCell>
                        <TableCell class="px-4 py-4 text-muted-foreground"> {{ role.users_count }} usuários </TableCell>
                        <TableCell class="px-4 py-4">
                            <Badge :variant="role.is_protected || role.is_in_use ? 'secondary' : 'outline'">
                                {{ role.is_protected ? 'Protegida' : role.is_in_use ? 'Em uso' : 'Editável' }}
                            </Badge>
                        </TableCell>
                        <TableCell class="px-4 py-4">
                            <div class="flex items-center justify-end gap-2">
                                <Button v-if="role.can.update" variant="outline" size="sm" as-child>
                                    <Link :href="edit(role.id)">Editar</Link>
                                </Button>

                                <AlertDialog v-if="role.can.delete">
                                    <AlertDialogTrigger as-child>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            size="sm"
                                            @click="roleToDelete = role"
                                        >
                                            Excluir
                                        </Button>
                                    </AlertDialogTrigger>
                                    <AlertDialogContent>
                                        <AlertDialogHeader>
                                            <AlertDialogTitle>Excluir perfil?</AlertDialogTitle>
                                            <AlertDialogDescription>
                                                Tem certeza que deseja excluir o perfil {{ role.name }}?
                                            </AlertDialogDescription>
                                        </AlertDialogHeader>
                                        <AlertDialogFooter>
                                            <AlertDialogCancel @click="roleToDelete = null">Cancelar</AlertDialogCancel>
                                            <AlertDialogAction variant="destructive" @click="destroyRole">
                                                Confirmar exclusão
                                            </AlertDialogAction>
                                        </AlertDialogFooter>
                                    </AlertDialogContent>
                                </AlertDialog>

                                <TooltipProvider v-else-if="role.is_in_use && !role.is_protected" :delay-duration="0">
                                    <Tooltip>
                                        <TooltipTrigger as-child>
                                            <span>
                                                <Button type="button" variant="destructive" size="sm" disabled>
                                                    Excluir
                                                </Button>
                                            </span>
                                        </TooltipTrigger>
                                        <TooltipContent>
                                            <p>Não pode excluir enquanto estiver em uso.</p>
                                        </TooltipContent>
                                    </Tooltip>
                                </TooltipProvider>

                                <span
                                    v-else-if="!role.can.update && !role.can.delete"
                                    class="text-sm text-muted-foreground"
                                >
                                    Sem ações
                                </span>
                            </div>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>
    </div>
</template>
