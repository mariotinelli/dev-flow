<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { ProjectDocumentationFilterValues } from '@/types';

const filters = defineModel<ProjectDocumentationFilterValues>('filters', { required: true });

const props = defineProps<{
    types: Array<{ value: number; label: string }>;
    categories: Array<{ value: number; label: string }>;
    visibilities: Array<{ value: number; label: string }>;
    isAdmin: boolean;
}>();

defineEmits<{
    submit: [];
    clear: [];
}>();
</script>

<template>
    <form
        class="flex flex-col gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 md:flex-row md:items-end md:flex-wrap dark:border-sidebar-border"
        @submit.prevent="$emit('submit')"
    >
        <div class="grid min-w-0 flex-1 gap-2">
            <Label for="search">Buscar</Label>
            <Input id="search" v-model="filters.search" placeholder="Título" />
        </div>

        <div class="grid gap-2">
            <Label for="type">Tipo</Label>
            <Select v-model="filters.type">
                <SelectTrigger id="type" class="w-44">
                    <SelectValue placeholder="Todos os tipos" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="0">Todos os tipos</SelectItem>
                        <SelectItem v-for="type in types" :key="type.value" :value="String(type.value)">
                            {{ type.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="grid gap-2">
            <Label for="category">Categoria</Label>
            <Select v-model="filters.category">
                <SelectTrigger id="category" class="w-44">
                    <SelectValue placeholder="Todas as categorias" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="0">Todas as categorias</SelectItem>
                        <SelectItem v-for="category in categories" :key="category.value" :value="String(category.value)">
                            {{ category.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div v-if="isAdmin" class="grid gap-2">
            <Label for="visibility">Visibilidade</Label>
            <Select v-model="filters.visibility">
                <SelectTrigger id="visibility" class="w-44">
                    <SelectValue placeholder="Todas as visibilidades" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="0">Todas as visibilidades</SelectItem>
                        <SelectItem v-for="visibility in visibilities" :key="visibility.value" :value="String(visibility.value)">
                            {{ visibility.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="flex gap-2 max-md:w-full">
            <Button type="submit" class="flex-1 md:flex-none">Filtrar</Button>
            <Button type="button" variant="outline" class="flex-1 md:flex-none" @click="$emit('clear')">Limpar</Button>
        </div>
    </form>
</template>
