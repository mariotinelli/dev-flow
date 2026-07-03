<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import StoreController from '@/actions/App/Http/Controllers/ProjectDocumentations/StoreController';
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

defineProps<{
    categories: Array<{ value: number; label: string }>;
    types: Array<{ value: number; label: string }>;
    visibilities: Array<{ value: number; label: string }>;
}>();

const isOpen = ref(false);

const LinkType = 3;
const FileType = 1;
const ImageType = 2;

const form = useForm({
    title: '',
    description: '',
    type: LinkType,
    category: 11,
    visibility: 1,
    url: '',
    file: null as File | null,
});

const isLinkType = computed(() => form.type === LinkType);
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

function submit(): void {
    form.post(StoreController.url(), {
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

        <DialogContent>
            <form class="space-y-6" enctype="multipart/form-data" @submit.prevent="submit">
                <DialogHeader>
                    <DialogTitle>Nova documentação</DialogTitle>
                    <DialogDescription>Cadastre um material relacionado ao projeto selecionado.</DialogDescription>
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
                        <Input id="project-documentation-url" v-model="form.url" name="url" type="url" placeholder="https://exemplo.com/documento" />
                        <InputError :message="form.errors.url" />
                    </div>

                    <div v-if="isFileType" class="grid gap-2">
                        <Label for="project-documentation-file" required>Arquivo</Label>
                        <Input
                            id="project-documentation-file"
                            name="file"
                            type="file"
                            @input="(e: Event) => { const target = e.target as HTMLInputElement; if (target.files) form.file = target.files[0]; }"
                        />
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

                <DialogFooter>
                    <Button type="button" variant="outline" @click="isOpen = false">Cancelar</Button>
                    <Button type="submit" :disabled="form.processing">Cadastrar</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
