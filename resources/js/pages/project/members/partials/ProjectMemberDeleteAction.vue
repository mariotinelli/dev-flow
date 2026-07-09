<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import DestroyController from '@/actions/App/Http/Controllers/Project/Members/DestroyController';
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
import { Button } from '@/components/ui/button';
import type { ProjectMember } from '@/types';

const props = defineProps<{
    projectMember: ProjectMember;
}>();

const isOpen = ref(false);

function removeMember(): void {
    isOpen.value = false;

    router.delete(DestroyController.url(props.projectMember.id), {
        preserveScroll: true,
    });
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger v-if="projectMember.can.delete" as-child>
            <Button type="button" variant="destructive" size="sm">Remover</Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <div class="space-y-6">
                <AlertDialogHeader>
                    <AlertDialogTitle>Remover membro do projeto?</AlertDialogTitle>
                    <AlertDialogDescription>
                        {{ projectMember.user.name }} será removido apenas deste projeto. O usuário continuará existindo no
                        sistema.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction variant="destructive" @click="removeMember">
                        Confirmar remoção
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
