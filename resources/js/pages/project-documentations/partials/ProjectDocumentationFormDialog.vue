<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
import type { PaginationMeta } from '@/types';

const props = defineProps<{
    categories: Array<{ value: number; label: string }>;
}>();

const isOpen = ref(false);

const form = useForm({
    title: '',
    description: '',
    type: 3,
    category: 11,
    url: '',
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
            <form class="space-y-6" @submit.prevent="submit">
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
                        <Label for="project-documentation-url" required>URL</Label>
                        <Input id="project-documentation-url" v-model="form.url" name="url" type="url" placeholder="https://exemplo.com/documento" />
                        <InputError :message="form.errors.url" />
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
