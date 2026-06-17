<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import StoreController from '@/actions/App/Http/Controllers/Developers/StoreController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { index } from '@/routes/developers';
import type { SelectOption } from '@/types';

defineProps<{
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
                title: 'Novo',
                href: '#',
            },
        ],
    },
});
</script>

<template>
    <div class="flex h-full flex-1 flex-col gap-6">
        <Head title="Novo desenvolvedor" />

        <Heading
            title="Novo desenvolvedor"
            description="Crie o acesso e um link será enviado para o desenvolvedor definir sua senha."
        />

        <Form
            v-bind="StoreController.form()"
            class="w-full space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            enctype="multipart/form-data"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6 md:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="name">Nome</Label>
                    <Input id="name" name="name" required autocomplete="name" />
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
                    />
                    <InputError :message="errors.email" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="avatar">Avatar</Label>
                <Input id="avatar" type="file" name="avatar" accept="image/*" />
                <InputError :message="errors.avatar" />
            </div>

            <div class="grid gap-6 md:grid-cols-3">
                <div class="grid gap-2 md:col-span-1">
                    <Label for="role">Cargo/Função</Label>
                    <Input
                        id="role"
                        name="role"
                        required
                        placeholder="Desenvolvedor backend"
                    />
                    <InputError :message="errors.role" />
                </div>

                <div class="grid gap-2">
                    <Label for="contract_type">Contrato</Label>
                    <Select name="contract_type" required>
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
                    <Select name="seniority" required>
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
                <Button type="submit" :disabled="processing"
                    >Salvar e enviar link</Button
                >
            </div>
        </Form>
    </div>
</template>
