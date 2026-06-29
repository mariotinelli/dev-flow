<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ActivateController from '@/actions/App/Http/Controllers/ProjectRoles/ActivateController';
import DestroyController from '@/actions/App/Http/Controllers/ProjectRoles/DestroyController';
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
import type { ProjectRole } from '@/types';

const props = defineProps<{
    projectRole: ProjectRole;
}>();

const isOpen = ref(false);

const canChangeSituation = computed(() =>
    props.projectRole.is_active ? props.projectRole.can.delete : props.projectRole.can.restore,
);

function updateSituation(): void {
    isOpen.value = false;

    if (props.projectRole.is_active) {
        router.delete(DestroyController.url(props.projectRole.id), {
            preserveScroll: true,
        });

        return;
    }

    router.post(
        ActivateController.url(props.projectRole.id),
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger v-if="canChangeSituation" as-child>
            <Button type="button" :variant="projectRole.is_active ? 'destructive' : 'success'" size="sm">
                {{ projectRole.is_active ? 'Inativar' : 'Ativar' }}
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <div class="space-y-6">
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ projectRole.is_active ? 'Inativar papel do projeto?' : 'Ativar papel do projeto?' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        <template v-if="projectRole.is_active">
                            O papel {{ projectRole.name }} será inativado, mas poderá ser restaurado depois.
                        </template>
                        <template v-else> O papel {{ projectRole.name }} voltará para a lista ativa. </template>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel>Cancelar</AlertDialogCancel>
                    <AlertDialogAction
                        :variant="projectRole.is_active ? 'destructive' : 'success'"
                        @click="updateSituation"
                    >
                        {{ projectRole.is_active ? 'Confirmar inativação' : 'Confirmar ativação' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
