<script setup lang="ts">
import { Form, Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import UpdateController from '@/actions/App/Http/Controllers/System/Projects/UpdateController';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import RichEditor from '@/components/RichEditor.vue';
import { Button } from '@/components/ui/button';
import { ColorInput } from '@/components/ui/color-input';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { index } from '@/routes/system/projects';
import type { Project } from '@/types';

const props = defineProps<{
    project: Project;
}>();

const key = ref(props.project.key);

function normalizeKey(value: string): string {
    return value.toUpperCase();
}

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Projetos',
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
        <Head :title="`Editar ${project.name}`" />

        <Heading title="Editar projeto" />

        <Form
            novalidate
            v-bind="UpdateController.form(props.project.id)"
            class="w-full space-y-6 rounded-xl border border-sidebar-border/70 bg-card p-6 dark:border-sidebar-border"
            v-slot="{ errors, processing }"
        >
            <div class="grid gap-6 lg:grid-cols-[1fr_18rem]">
                <div class="grid gap-6 md:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="name" required>Nome</Label>
                        <Input id="name" name="name" required autocomplete="off" :default-value="project.name" />
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
                            autocomplete="off"
                            @update:model-value="(value) => (key = normalizeKey(String(value)))"
                        />
                        <InputError :message="errors.key" />
                    </div>

                    <div class="grid gap-2 md:col-span-2">
                        <Label for="description">Descrição</Label>
                        <RichEditor
                            id="description"
                            name="description"
                            :rows="8"
                            :default-value="project.description"
                        />
                        <InputError :message="errors.description" />
                    </div>
                </div>

                <div class="grid content-start gap-6">
                    <div class="grid gap-2">
                        <Label for="color" required>Cor</Label>
                        <ColorInput id="color" name="color" required :default-value="project.color ?? '#2563eb'" />
                        <InputError :message="errors.color" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="starts_at">Início</Label>
                        <Input id="starts_at" type="date" name="starts_at" :default-value="project.starts_at ?? ''" />
                        <InputError :message="errors.starts_at" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="due_at">Prazo</Label>
                        <Input id="due_at" type="date" name="due_at" :default-value="project.due_at ?? ''" />
                        <InputError :message="errors.due_at" />
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 border-t pt-6">
                <Button variant="outline" as-child><Link :href="index()">Cancelar</Link></Button>
                <Button type="submit" :disabled="processing">Salvar alterações</Button>
            </div>
        </Form>
    </div>
</template>
