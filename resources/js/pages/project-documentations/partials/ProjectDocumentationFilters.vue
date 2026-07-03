<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectGroup, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { ProjectDocumentationFilterValues } from '@/types';

const filters = defineModel<ProjectDocumentationFilterValues>('filters', { required: true });

defineProps<{
    types: Array<{ value: number; label: string }>;
}>();

defineEmits<{
    submit: [];
    clear: [];
}>();
</script>

<template>
    <form
        class="grid gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 md:grid-cols-[1fr_auto_auto] md:items-end dark:border-sidebar-border"
        @submit.prevent="$emit('submit')"
    >
        <div class="grid gap-2">
            <Label for="search">Buscar</Label>
            <Input id="search" v-model="filters.search" placeholder="Título da documentação" />
        </div>

        <div class="grid gap-2">
            <Label for="type">Tipo</Label>
            <Select v-model="filters.type">
                <SelectTrigger id="type" class="w-44">
                    <SelectValue placeholder="Todos os tipos" />
                </SelectTrigger>
                <SelectContent>
                    <SelectGroup>
                        <SelectItem value="">Todos os tipos</SelectItem>
                        <SelectItem v-for="type in types" :key="type.value" :value="String(type.value)">
                            {{ type.label }}
                        </SelectItem>
                    </SelectGroup>
                </SelectContent>
            </Select>
        </div>

        <div class="flex gap-2">
            <Button type="submit">Filtrar</Button>
            <Button type="button" variant="outline" @click="$emit('clear')">Limpar</Button>
        </div>
    </form>
</template>
