<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import StoreController from '@/actions/App/Http/Controllers/Project/Documentations/StoreController';
import UpdateController from '@/actions/App/Http/Controllers/Project/Documentations/UpdateController';
import FileUpload from '@/components/ui/file-upload/FileUpload.vue';
import ImageUpload from '@/components/ui/image-upload/ImageUpload.vue';
import InputError from '@/components/InputError.vue';
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
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectItemText, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import type { ProjectDocumentation } from '@/types';

const props = defineProps<{
    categories: Array<{ value: number; label: string }>;
    types: Array<{ value: number; label: string }>;
    visibilities: Array<{ value: number; label: string }>;
    projectDocumentation?: ProjectDocumentation;
}>();

const isOpen = ref(false);

const LinkType = 3;
const FileType = 1;
const ImageType = 2;

const isEditing = computed(() => Boolean(props.projectDocumentation));

const existingFileName = computed(() =>
    isEditing.value && props.projectDocumentation?.file_original_name
        ? props.projectDocumentation.file_original_name
        : null,
);

const existingImageUrl = computed(() =>
    isEditing.value && props.projectDocumentation?.preview_url
        ? props.projectDocumentation.preview_url
        : null,
);

function defaults(projectDocumentation?: ProjectDocumentation): {
    title: string;
    description: string;
    type: number;
    category: number;
    visibility: number;
    url: string;
    file: File | null;
    remove_file: boolean;
} {
    return {
        title: projectDocumentation?.title ?? '',
        description: projectDocumentation?.description ?? '',
        type: projectDocumentation?.type ?? LinkType,
        category: projectDocumentation?.category ?? props.categories[0]?.value ?? 11,
        visibility: projectDocumentation?.visibility ?? props.visibilities[0]?.value ?? 1,
        url: projectDocumentation?.url ?? '',
        file: null,
        remove_file: false,
    };
}

const form = useForm(defaults(props.projectDocumentation));

const isLinkType = computed(() => form.type === LinkType);
const isImageType = computed(() => form.type === ImageType);
const isPlainFileType = computed(() => form.type === FileType);
const isFileType = computed(() => form.type === FileType || form.type === ImageType);

watch(() => form.type, (newType, oldType) => {
    if (newType !== oldType) {
        if (newType === LinkType) {
            form.file = null;
        } else if (isFileType.value) {
            form.url = '';
        }
    }
});

watch(
    () => props.projectDocumentation,
    (projectDocumentation) => {
        form.defaults(defaults(projectDocumentation));
        form.reset();
    },
);

watch(isOpen, (opened) => {
    if (!opened) {
        form.defaults(defaults(props.projectDocumentation));
        form.reset();

        return;
    }

    form.defaults(defaults(props.projectDocumentation));
    form.reset();
    form.clearErrors();
});

function submit(): void {
    const url = isEditing.value
        ? UpdateController.url({ projectDocumentation: props.projectDocumentation!.id })
        : StoreController.url();

    form.post(url, {
        preserveScroll: true,
        onSuccess: () => {
            isOpen.value = false;
            form.reset();
        },
    });
}
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>

        <DialogContent class="sm:max-w-3xl">
            <form class="space-y-6" enctype="multipart/form-data" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>{{ isEditing ? 'Editar documentação' : 'Nova documentação' }}</DialogTitle>
                    <DialogDescription>
                        {{ isEditing ? 'Atualize os dados do material selecionado.' : 'Cadastre um material relacionado ao projeto selecionado.' }}
                    </DialogDescription>
                </DialogHeader>

                <div class="grid gap-4">
                    <div class="grid gap-2">
                        <Label for="project-documentation-title" required>Título</Label>
                        <Input
                            id="project-documentation-title"
                            v-model="form.title"
                            name="title"
                            placeholder="Ex: Guia de onboarding da API"
                        />
                        <InputError :message="form.errors.title" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="project-documentation-description">Descrição</Label>
                        <Textarea
                            id="project-documentation-description"
                            v-model="form.description"
                            name="description"
                            placeholder="Descreva brevemente o material..."
                        />
                        <InputError :message="form.errors.description" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
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

                    <div v-if="isLinkType" class="grid gap-2">
                        <Label for="project-documentation-url" required>URL</Label>
                        <Input id="project-documentation-url" v-model="form.url" name="url" type="url" placeholder="https://exemplo.com/documento" />
                        <InputError :message="form.errors.url" />
                    </div>

                    <div v-if="isImageType" class="grid gap-2">
                        <Label for="project-documentation-image" required>Imagem</Label>
                        <ImageUpload
                            id="project-documentation-image"
                            name="file"
                            accept="image/*"
                            :existing-image-url="existingImageUrl"
                            v-model="form.file"
                            @remove-existing="form.remove_file = true"
                        />
                        <InputError :message="form.errors.file" />
                    </div>

                    <div v-if="isPlainFileType" class="grid gap-2">
                        <Label for="project-documentation-file" required>Arquivo</Label>
                        <FileUpload
                            id="project-documentation-file"
                            name="file"
                            :existing-file-name="existingFileName"
                            v-model="form.file"
                            @remove-existing="form.remove_file = true"
                        />
                        <InputError :message="form.errors.file" />
                    </div>
                </div>

                <DialogFooter>
                    <Button type="button" variant="outline" @click="isOpen = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">{{ isEditing ? 'Salvar' : 'Cadastrar' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
