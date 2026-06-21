<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import ActivateController from '@/actions/App/Http/Controllers/Users/ActivateController';
import DestroyController from '@/actions/App/Http/Controllers/Users/DestroyController';
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
import type { User } from '@/types';

const props = defineProps<{
    user: User;
}>();

const isOpen = ref(false);

const canChangeStatus = computed(() => (props.user.is_active ? props.user.can.delete : props.user.can.restore));

function updateStatus(): void {
    isOpen.value = false;

    if (props.user.is_active) {
        router.delete(DestroyController.url(props.user.id), {
            preserveScroll: true,
        });

        return;
    }

    router.post(
        ActivateController.url(props.user.id),
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
            <Button type="button" :variant="user.is_active ? 'destructive' : 'success'" size="sm">
                {{ user.is_active ? 'Inativar' : 'Ativar' }}
            </Button>
        </AlertDialogTrigger>

        <AlertDialogContent>
            <div class="space-y-6">
                <AlertDialogHeader>
                    <AlertDialogTitle>
                        {{ user.is_active ? 'Inativar usuário?' : 'Ativar usuário?' }}
                    </AlertDialogTitle>
                    <AlertDialogDescription>
                        <template v-if="user.is_active">
                            O acesso de {{ user.name }} será inativado e ele não poderá mais autenticar no sistema.
                        </template>
                        <template v-else>
                            O acesso de {{ user.name }} será reativado e ele poderá autenticar novamente no sistema.
                        </template>
                    </AlertDialogDescription>
                </AlertDialogHeader>

                <AlertDialogFooter>
                    <AlertDialogCancel> Cancelar </AlertDialogCancel>
                    <AlertDialogAction :variant="user.is_active ? 'destructive' : 'success'" @click="updateStatus">
                        {{ user.is_active ? 'Confirmar inativação' : 'Confirmar ativação' }}
                    </AlertDialogAction>
                </AlertDialogFooter>
            </div>
        </AlertDialogContent>
    </AlertDialog>
</template>
