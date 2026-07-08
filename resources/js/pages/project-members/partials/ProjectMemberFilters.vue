<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import type { ProjectMemberFilterValues } from '@/types';

const filters = defineModel<ProjectMemberFilterValues>('filters', { required: true });

defineEmits<{
    submit: [];
    clear: [];
}>();
</script>

<template>
    <form
        class="grid gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 md:grid-cols-[1fr_auto] md:items-end dark:border-sidebar-border"
        @submit.prevent="$emit('submit')"
    >
        <div class="grid gap-2">
            <Label for="search">Buscar</Label>
            <Input id="search" v-model="filters.search" placeholder="Nome, e-mail ou papel" />
        </div>

        <div class="flex gap-2">
            <Button type="submit">Filtrar</Button>
            <Button type="button" variant="outline" @click="$emit('clear')">Limpar</Button>
        </div>
    </form>
</template>
