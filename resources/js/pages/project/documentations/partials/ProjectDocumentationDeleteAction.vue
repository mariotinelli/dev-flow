<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { ref } from 'vue';
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
import { destroy } from '@/routes/project/documentations';
import type { ProjectDocumentation } from '@/types';

const props = defineProps<{
    projectDocumentation: ProjectDocumentation;
    iconOnly?: boolean;
}>();

const isOpen = ref(false);

function removeDocumentation(): void {
    isOpen.value = false;

    router.delete(
        destroy.url({ projectDocumentation: props.projectDocumentation.id }),
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger v-if="projectDocumentation.can.delete" as-child>
            <Button
                type="button"
                variant="outline"
                tone="destructive"
                :size="iconOnly ? 'icon' : 'sm'"
                data-testid="delete-button"
                title="Excluir"
            >
                <Trash2 class="size-4" />
                <span v-if="!iconOnly">Excluir</span>
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <div class="space-y-6">
                <AlertDialogHeader>
                    <AlertDialogTitle>Excluir documentação?</AlertDialogTitle>
                    <AlertDialogDescription>
                        Esta ação removerá a documentação "{{ projectDocumentation.title }}" permanentemente.
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction variant="destructive" @click="removeDocumentation">
                        Confirmar exclusão
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
