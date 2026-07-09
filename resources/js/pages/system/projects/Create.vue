<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import StoreController from '@/actions/App/Http/Controllers/System/Projects/StoreController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import RichEditor from '@/components/RichEditor.vue';
import { Button } from '@/components/ui/button';
import { ColorInput } from '@/components/ui/color-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/system/projects';

const name = ref('');
const key = ref('');
const keyManuallyEdited = ref(false);

function generatedKeyForName(value: string): string {
    const normalizedName = value
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/([a-z])([A-Z])/g, '$1 $2');

    const prefix = normalizedName
        .split(/[^A-Za-z0-9]+/)
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase())
        .join('');

    return `${prefix || 'PRJ'}-001`;
}

function fillKeyFromName(): void {
    if (!keyManuallyEdited.value) {
        key.value = generatedKeyForName(name.value);
    }
}

function updateKey(value: string): void {
    keyManuallyEdited.value = true;
    key.value = value.toUpperCase();
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projetos',
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
        <Head title="Novo projeto" />

        <Heading title="Novo projeto" />

        <Form
            novalidate
            v-bind="StoreController.form()"
            class="w-full space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            v-slot="{ errors, processing }"
        >
            <input type="hidden" name="key_manually_edited" :value="keyManuallyEdited ? '1' : '0'" />

            <div class="grid gap-6 lg:grid-cols-[1fr_18rem]">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name" required>Nome</Label>
                        <Input
                            id="name"
                            v-model="name"
                            name="name"
                            required
                            autocomplete="off"
                            @blur="fillKeyFromName"
                        />
                        <InputError :message="errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="key" required>Identificador</Label>
                        <Input
                            id="key"
                            v-model="key"
                            name="key"
                            required
                            maxlength="10"
                            placeholder="DF-001"
                            autocomplete="off"
                            @update:model-value="(value) => updateKey(String(value))"
                        />
                        <InputError :message="errors.key" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="description">Descrição</Label>
                        <RichEditor id="description" name="description" :rows="8" />
                        <InputError :message="errors.description" />
                    </div>
                </div>

                <div class="grid content-start gap-6">
                    <div class="grid gap-2">
                        <Label for="color" required>Cor</Label>
                        <ColorInput id="color" name="color" required default-value="#2563eb" />
                        <InputError :message="errors.color" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="starts_at">Início</Label>
                        <Input id="starts_at" type="date" name="starts_at" />
                        <InputError :message="errors.starts_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="due_at">Prazo</Label>
                        <Input id="due_at" type="date" name="due_at" />
                        <InputError :message="errors.due_at" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t pt-6">
                <Button variant="outline" as-child><Link :href="index()">Cancelar</Link></Button>
                <Button type="submit" :disabled="processing">Salvar</Button>
            </div>
        </Form>
    </div>
</template>
