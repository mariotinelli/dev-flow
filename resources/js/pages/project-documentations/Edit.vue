<script setup lang="ts">
import { Head, router, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import UpdateController from '@/actions/App/Http/Controllers/ProjectDocumentations/UpdateController';
import { index } from '@/routes/project/documentations';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectItemText,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { ProjectDocumentation } from '@/types';

const props = defineProps<{
    projectDocumentation: ProjectDocumentation;
    categories: Array<{ value: number; label: string }>;
    types: Array<{ value: number; label: string }>;
    visibilities: Array<{ value: number; label: string }>;
}>();

const LinkType = 3;
const FileType = 1;
const ImageType = 2;

const form = useForm({
    title: props.projectDocumentation.title,
    description: props.projectDocumentation.description ?? '',
    type: props.projectDocumentation.type,
    category: props.projectDocumentation.category,
    visibility: 1,
    url: props.projectDocumentation.url ?? '',
    file: null as File | null,
});

const isLinkType = computed(() => form.type === LinkType);
const isFileType = computed(() => form.type === FileType || form.type === ImageType);

function submit(): void {
    form.post(UpdateController.url({ projectDocumentation: props.projectDocumentation.id }), {
        preserveScroll: true,
    });
}
</script>

<template>
    <Head :title="'Editar: ' + projectDocumentation.title" />

    <div class="flex h-full flex-1 flex-col gap-6">
        <Heading
            variant="small"
            title="Editar documentação"
            :description="projectDocumentation.title"
        />

        <form class="max-w-lg space-y-6" enctype="multipart/form-data" @submit.prevent="submit">
            <div class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="project-documentation-title" required>Título</Label>
                    <Input
                        id="project-documentation-title"
                        v-model="form.title"
                        name="title"
                    />
                    <InputError :message="form.errors.title" />
                </div>

                <div class="grid gap-2">
                    <Label for="project-documentation-description">Descrição</Label>
                    <Textarea
                        id="project-documentation-description"
                        v-model="form.description"
                        name="description"
                    />
                    <InputError :message="form.errors.description" />
                </div>

                <div class="grid gap-2">
                    <Label for="project-documentation-type" required>Tipo</Label>
                    <Select v-model="form.type">
                        <SelectTrigger id="project-documentation-type">
                            <SelectValue placeholder="Selecione" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="type in types" :key="type.value" :value="type.value">
                                <SelectItemText>{{ type.label }}</SelectItemText>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.type" />
                </div>

                <div v-if="isLinkType" class="grid gap-2">
                    <Label for="project-documentation-url" required>URL</Label>
                    <Input id="project-documentation-url" v-model="form.url" name="url" type="url" />
                    <InputError :message="form.errors.url" />
                </div>

                <div v-if="isFileType" class="grid gap-2">
                    <Label for="project-documentation-file">Arquivo</Label>
                    <Input
                        id="project-documentation-file"
                        name="file"
                        type="file"
                        @input="(e: Event) => { const target = e.target as HTMLInputElement; if (target.files) form.file = target.files[0]; }"
                    />
                    <p class="text-sm text-muted-foreground">
                        Deixe vazio para manter o arquivo atual.
                    </p>
                    <InputError :message="form.errors.file" />
                </div>

                <div class="grid gap-2">
                    <Label for="project-documentation-category" required>Categoria</Label>
                    <Select v-model="form.category">
                        <SelectTrigger id="project-documentation-category">
                            <SelectValue placeholder="Selecione" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="category in categories" :key="category.value" :value="category.value">
                                <SelectItemText>{{ category.label }}</SelectItemText>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.category" />
                </div>

                <div class="grid gap-2">
                    <Label for="project-documentation-visibility" required>Visibilidade</Label>
                    <Select v-model="form.visibility">
                        <SelectTrigger id="project-documentation-visibility">
                            <SelectValue placeholder="Selecione" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem v-for="visibility in visibilities" :key="visibility.value" :value="visibility.value">
                                <SelectItemText>{{ visibility.label }}</SelectItemText>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.visibility" />
                </div>
            </div>

            <div class="flex gap-2">
                <Button type="submit" :disabled="form.processing">Salvar</Button>
                <Button type="button" variant="outline" @click="router.visit(index.url())">Cancelar</Button>
            </div>
        </form>
    </div>
</template>
