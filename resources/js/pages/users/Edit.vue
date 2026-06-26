<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ChevronsUpDown } from '@lucide/vue';
import { ref } from 'vue';
import UpdateController from '@/actions/App/Http/Controllers/Users/UpdateController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getInitials } from '@/composables/useInitials';
import { index } from '@/routes/users';
import type { SelectOption, User } from '@/types';

const props = defineProps<{
    user: User;
    jobTitles: SelectOption[];
    contractTypes: SelectOption[];
    seniorities: SelectOption[];
    roles: SelectOption[];
}>();

const roleOpen = ref(false);
const selectedRoleId = ref(props.user.role_id ? String(props.user.role_id) : '');

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Usuários',
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
        <Head :title="`Editar ${user.name}`" />

        <Heading title="Editar usuário" description="Atualize os dados de acesso e perfil técnico do usuário." />

        <Form
            novalidate
            v-bind="UpdateController.form(user.id)"
            class="w-full space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            enctype="multipart/form-data"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6 lg:grid-cols-[18rem_1fr]">
                <div class="grid h-full grid-rows-[auto_auto_1fr_auto_auto] gap-2">
                    <div class="flex min-w-0 items-center gap-3">
                        <Avatar class="size-12 shrink-0">
                            <AvatarImage v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name" />
                            <AvatarFallback>{{ getInitials(user.name) }}</AvatarFallback>
                        </Avatar>
                        <div class="min-w-0">
                            <p class="truncate font-medium">{{ user.name }}</p>
                            <p class="truncate text-sm text-muted-foreground">
                                {{ user.email }}
                            </p>
                        </div>
                    </div>

                    <Label for="avatar">Avatar</Label>
                    <ImageUpload id="avatar" name="avatar" accept="image/*" />
                    <p class="text-xs text-muted-foreground">Envie apenas se quiser substituir.</p>
                    <InputError :message="errors.avatar" />
                </div>

                <div class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name" required>Nome</Label>
                        <Input id="name" name="name" required autocomplete="name" :default-value="user.name" />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="email" required>E-mail</Label>
                        <Input
                            id="email"
                            type="email"
                            name="email"
                            required
                            autocomplete="email"
                            :default-value="user.email"
                        />
                        <InputError :message="errors.email" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="job_title" required>Cargo</Label>
                        <Select name="job_title" required :default-value="user.job_title">
                            <SelectTrigger id="job_title" class="w-full">
                                <SelectValue placeholder="Selecione" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem v-for="jobTitle in jobTitles" :key="jobTitle.value" :value="jobTitle.value">
                                    {{ jobTitle.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.job_title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="contract_type" required>Contrato</Label>
                        <Select name="contract_type" required :default-value="user.contract_type">
                            <SelectTrigger id="contract_type" class="w-full">
                                <SelectValue placeholder="Selecione" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="contractType in contractTypes"
                                    :key="contractType.value"
                                    :value="contractType.value"
                                >
                                    {{ contractType.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.contract_type" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="seniority" required>Senioridade</Label>
                        <Select name="seniority" required :default-value="user.seniority">
                            <SelectTrigger id="seniority" class="w-full">
                                <SelectValue placeholder="Selecione" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="seniority in seniorities"
                                    :key="seniority.value"
                                    :value="seniority.value"
                                >
                                    {{ seniority.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="errors.seniority" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="role_id" required>Perfil</Label>
                        <input type="hidden" name="role_id" :value="selectedRoleId" />

                        <Popover v-model:open="roleOpen">
                            <PopoverTrigger as-child>
                                <Button
                                    id="role_id"
                                    type="button"
                                    variant="outline"
                                    role="combobox"
                                    :aria-expanded="roleOpen"
                                    class="w-full justify-between"
                                >
                                    <span class="truncate">
                                        {{
                                            selectedRoleId
                                                ? roles.find((role) => String(role.value) === selectedRoleId)?.label
                                                : 'Selecione'
                                        }}
                                    </span>

                                    <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
                                </Button>
                            </PopoverTrigger>

                            <PopoverContent class="w-(--reka-popover-trigger-width) p-0">
                                <Command>
                                    <CommandInput placeholder="Pesquisar perfil..." />

                                    <CommandList>
                                        <CommandEmpty>Nenhum perfil encontrado.</CommandEmpty>

                                        <CommandGroup>
                                            <CommandItem
                                                v-for="role in roles"
                                                :key="role.value"
                                                :value="String(role.value)"
                                                @select="
                                                    selectedRoleId = String(role.value);
                                                    roleOpen = false;
                                                "
                                            >
                                                {{ role.label }}
                                            </CommandItem>
                                        </CommandGroup>
                                    </CommandList>
                                </Command>
                            </PopoverContent>
                        </Popover>
                        <InputError :message="errors.role_id" />
                    </div>
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
