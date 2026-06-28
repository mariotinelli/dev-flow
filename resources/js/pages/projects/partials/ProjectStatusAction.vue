<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ActivateController from '@/actions/App/Http/Controllers/Projects/ActivateController';
import DestroyController from '@/actions/App/Http/Controllers/Projects/DestroyController';
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
import type { Project } from '@/types';

const props = defineProps<{
    project: Project;
}>();

const isOpen = ref(false);

const canChangeStatus = computed(() => (props.project.is_active ? props.project.can.delete : props.project.can.restore));

function updateStatus(): void {
    isOpen.value = false;

    if (props.project.is_active) {
        router.delete(DestroyController.url(props.project.id), {
            preserveScroll: true,
        });

        return;
    }

    router.post(
        ActivateController.url(props.project.id),
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger v-if="canChangeStatus" as-child>
            <Button type="button" :variant="project.is_active ? 'destructive' : 'success'" size="sm">
                {{ project.is_active ? 'Inativar' : 'Ativar' }}
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <div class="space-y-6">
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ project.is_active ? 'Inativar projeto?' : 'Ativar projeto?' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        <template v-if="project.is_active">
                            O projeto {{ project.name }} será inativado, mas poderá ser restaurado depois.
                        </template>
                        <template v-else> O projeto {{ project.name }} voltará para a lista ativa. </template>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction :variant="project.is_active ? 'destructive' : 'success'" @click="updateStatus">
                        {{ project.is_active ? 'Confirmar inativação' : 'Confirmar ativação' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
