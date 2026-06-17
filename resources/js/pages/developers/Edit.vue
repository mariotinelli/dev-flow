<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import UpdateController from '@/actions/App/Http/Controllers/Developers/UpdateController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { getInitials } from '@/composables/useInitials';
import { index } from '@/routes/developers';
import type { Developer, SelectOption } from '@/types';

defineProps<{
    developer: Developer;
    contractTypes: SelectOption[];
    seniorities: SelectOption[];
}>();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Desenvolvedores',
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
        <Head :title="`Editar ${developer.name}`" />

        <Heading
            title="Editar desenvolvedor"
            description="Atualize os dados de acesso e perfil técnico do desenvolvedor."
        />

        <Form
            v-bind="UpdateController.form(developer.id)"
            class="w-full space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            enctype="multipart/form-data"
            v-slot="{ errors, processing }"
        >
            <div class="flex items-center gap-4">
                <Avatar class="size-16">
                    <AvatarImage v-if="developer.avatar_url" :src="developer.avatar_url" :alt="developer.name" />
                    <AvatarFallback>{{ getInitials(developer.name) }}</AvatarFallback>
                </Avatar>
                <div>
                    <p class="font-medium">{{ developer.name }}</p>
                    <p class="text-sm text-muted-foreground">
                        {{ developer.email }}
                    </p>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="name">Nome</Label>
                    <Input id="name" name="name" required autocomplete="name" :default-value="developer.name" />
                    <InputError :message="errors.name" />
                </div>

                <div class="grid gap-2">
                    <Label for="email">E-mail</Label>
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autocomplete="email"
                        :default-value="developer.email"
                    />
                    <InputError :message="errors.email" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="avatar">Avatar</Label>
                <Input id="avatar" type="file" name="avatar" accept="image/*" />
                <p class="text-xs text-muted-foreground">
                    Envie uma nova imagem apenas se quiser substituir o avatar atual.
                </p>
                <InputError :message="errors.avatar" />
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="grid gap-2 md:col-span-1">
                    <Label for="role">Cargo/Função</Label>
                    <Input id="role" name="role" required :default-value="developer.role" />
                    <InputError :message="errors.role" />
                </div>

                <div class="grid gap-2">
                    <Label for="contract_type">Contrato</Label>
                    <Select name="contract_type" required :default-value="developer.contract_type">
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
                    <Label for="seniority">Senioridade</Label>
                    <Select name="seniority" required :default-value="developer.seniority">
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
