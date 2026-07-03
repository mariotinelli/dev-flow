<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import StoreController from '@/actions/App/Http/Controllers/ProjectMembers/StoreController';
import UpdateController from '@/actions/App/Http/Controllers/ProjectMembers/UpdateController';
import InputError from '@/components/InputError.vue';
import SearchableSelect from '@/components/SearchableSelect.vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Label } from '@/components/ui/label';
import type { Auth, ProjectMember, ProjectMemberSelectOption } from '@/types';

const props = defineProps<{
    mode: 'create' | 'edit';
    projectMember?: ProjectMember;
    users: ProjectMemberSelectOption[];
    projectRoles: ProjectMemberSelectOption[];
}>();

const isOpen = ref(false);
const page = usePage<{ auth: Auth }>();

const form = useForm({
    user_id: '',
    project_role_id: '',
});

const title = computed(() => (props.mode === 'create' ? 'Novo membro do projeto' : 'Editar membro do projeto'));
const description = computed(() =>
    props.mode === 'create' ? 'Vincule um usuário ao projeto selecionado.' : 'Atualize o usuário ou papel do vínculo.',
);

const userOptions = computed<ProjectMemberSelectOption[]>(() => {
    if (props.mode !== 'edit' || !props.projectMember) {
        return props.users;
    }

    return [
        ...(props.projectMember.user.id !== page.props.auth.user.id
            ? [
                  {
                      value: props.projectMember.user.id,
                      label: `${props.projectMember.user.name} (${props.projectMember.user.email})`,
                  },
              ]
            : []),
        ...props.users.filter((user) => user.value !== props.projectMember?.user.id),
    ];
});

watch(isOpen, (open) => {
    if (!open) {
        form.clearErrors();

        return;
    }

    form.user_id = props.mode === 'edit' && props.projectMember ? String(props.projectMember.user.id) : '';
    form.project_role_id =
        props.mode === 'edit' && props.projectMember ? String(props.projectMember.project_role.id) : '';
    form.clearErrors();
});

function submit(): void {
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    };

    if (props.mode === 'create') {
        form.post(StoreController.url(), options);

        return;
    }

    if (props.projectMember) {
        form.post(UpdateController.url(props.projectMember.id), options);
    }
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>

        <DialogContent>
            <form class="space-y-6" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ title }}</DialogTitle>
                    <DialogDescription>{{ description }}</DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="project-member-user" required>Usuário</Label>
                        <SearchableSelect
                            id="project-member-user"
                            v-model="form.user_id"
                            :options="userOptions"
                            placeholder="Selecione"
                            search-placeholder="Pesquisar usuário..."
                            empty-message="Nenhum usuário encontrado."
                            :disabled="userOptions.length === 0"
                        />
                        <p v-if="userOptions.length === 0" class="text-sm text-muted-foreground">
                            Todos os usuários disponíveis já pertencem a este projeto.
                        </p>
                        <InputError :message="form.errors.user_id" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="project-member-role" required>Papel do projeto</Label>
                        <SearchableSelect
                            id="project-member-role"
                            v-model="form.project_role_id"
                            :options="projectRoles"
                            placeholder="Selecione"
                            search-placeholder="Pesquisar papel..."
                            empty-message="Nenhum papel encontrado."
                            :disabled="projectRoles.length === 0"
                        />
                        <p v-if="projectRoles.length === 0" class="text-sm text-muted-foreground">
                            Cadastre um papel ativo antes de adicionar membros.
                        </p>
                        <InputError :message="form.errors.project_role_id" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="isOpen = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing || userOptions.length === 0 || projectRoles.length === 0">
                        {{ mode === 'create' ? 'Salvar' : 'Salvar alterações' }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
