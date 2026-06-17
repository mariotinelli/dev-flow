<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import UpdateController from '@/actions/App/Http/Controllers/Roles/UpdateController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Collapsible, CollapsibleContent, CollapsibleTrigger } from '@/components/ui/collapsible';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useRolePermissions } from '@/composables/useRolePermissions';
import { index } from '@/routes/roles';
import type { EditableRole, PermissionGroups } from '@/types';

const props = defineProps<{
    role: EditableRole;
    permissionGroups: PermissionGroups;
}>();

const {
    selectedPermissions,
    allPermissionsSelected,
    hasPermission,
    setPermission,
    isGroupSelected,
    toggleGroup,
    toggleAllPermissions,
} = useRolePermissions(props.permissionGroups, props.role.permissions);

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Perfis',
                href: index(),
            },
            {
                title: 'Editar',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6">
        <Head :title="`Editar ${role.name}`" />

        <Heading title="Editar perfil" description="Atualize o nome e as permissões atribuídas a este perfil." />

        <Form
            v-bind="UpdateController.form(role.id)"
            :transform="(data) => ({ ...data, permissions: selectedPermissions })"
            class="w-full space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-2">
                <Label for="name">Nome</Label>
                <Input id="name" name="name" required :default-value="role.name" />
                <InputError :message="errors.name" />
            </div>

            <div class="space-y-4">
                <div>
                    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                        <div>
                            <h2 class="font-medium">Permissões</h2>
                            <p class="text-sm text-muted-foreground">
                                Marque as permissões que este perfil deve conceder.
                            </p>
                        </div>
                        <Button type="button" variant="outline" size="sm" @click="toggleAllPermissions">
                            {{ allPermissionsSelected ? 'Limpar permissões' : 'Marcar todas as permissões' }}
                        </Button>
                    </div>
                    <InputError :message="errors.permissions" />
                </div>

                <div class="grid gap-4">
                    <Collapsible
                        v-for="(permissions, group) in permissionGroups"
                        :key="group"
                        class="rounded-lg border border-sidebar-border/70 p-4 dark:border-sidebar-border"
                    >
                        <template #default="{ open }">
                            <div :class="['flex items-center justify-between gap-3', open ? 'mb-3' : '']">
                                <CollapsibleTrigger as-child>
                                    <Button type="button" variant="ghost" size="sm" class="-ml-2 gap-2 px-2">
                                        <ChevronsUpDown class="size-4" />
                                        <span class="font-medium">{{ group }}</span>
                                    </Button>
                                </CollapsibleTrigger>
                                <Button type="button" variant="outline" size="sm" @click="toggleGroup(String(group))">
                                    {{ isGroupSelected(String(group)) ? 'Limpar grupo' : 'Marcar grupo' }}
                                </Button>
                            </div>
                            <CollapsibleContent class="grid gap-3">
                                <label
                                    v-for="permission in permissions"
                                    :key="permission.name"
                                    class="flex items-center gap-3 text-sm"
                                >
                                    <Checkbox
                                        :model-value="hasPermission(permission.name)"
                                        @update:model-value="setPermission(permission.name, $event === true)"
                                    />
                                    <span>{{ permission.label }}</span>
                                </label>
                            </CollapsibleContent>
                        </template>
                    </Collapsible>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t pt-6">
                <Button variant="outline" as-child>
                    <Link :href="index()">Cancelar</Link>
                </Button>
                <Button type="submit" :disabled="processing">Salvar alterações</Button>
            </div>
        </Form>
    </div>
</template>
