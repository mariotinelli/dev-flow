<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ActivateController from '@/actions/App/Http/Controllers/Developers/ActivateController';
import DestroyController from '@/actions/App/Http/Controllers/Developers/DestroyController';
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
import type { Developer } from '@/types';

const props = defineProps<{
    developer: Developer;
}>();

const isOpen = ref(false);

function updateStatus(): void {
    isOpen.value = false;

    if (props.developer.is_active) {
        router.delete(DestroyController.url(props.developer.id), {
            preserveScroll: true,
        });

        return;
    }

    router.post(
        ActivateController.url(props.developer.id),
        {},
        {
            preserveScroll: true,
        },
    );
}
</script>

<template>
    <AlertDialog v-model:open="isOpen">
        <AlertDialogTrigger as-child>
            <Button type="button" :variant="developer.is_active ? 'destructive' : 'default'" size="sm">
                {{ developer.is_active ? 'Inativar' : 'Ativar' }}
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <div class="space-y-6">
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ developer.is_active ? 'Inativar desenvolvedor?' : 'Ativar desenvolvedor?' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        <template v-if="developer.is_active">
                            O acesso de {{ developer.name }} será inativado e ele não poderá mais autenticar no sistema.
                        </template>
                        <template v-else>
                            O acesso de {{ developer.name }} será reativado e ele poderá autenticar novamente no
                            sistema.
                        </template>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel> Cancelar </AlertDialogCancel>
                    <AlertDialogAction
                        :class="
                            developer.is_active
                                ? 'bg-destructive text-white shadow-xs hover:bg-destructive/90 focus-visible:ring-destructive/20 dark:bg-destructive/60 dark:focus-visible:ring-destructive/40'
                                : ''
                        "
                        @click="updateStatus"
                    >
                        {{ developer.is_active ? 'Confirmar inativação' : 'Confirmar ativação' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
