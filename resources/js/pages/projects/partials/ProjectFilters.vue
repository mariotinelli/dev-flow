<script setup lang="ts">
import { Search, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type {
    ProjectDeletedStatusOption,
    ProjectFilterValues,
    ProjectStatusOption,
    ProjectVisibilityOption,
} from '@/types';

defineProps<{
    statuses: ProjectStatusOption[];
    visibilities: ProjectVisibilityOption[];
    deletedStatuses: ProjectDeletedStatusOption[];
}>();

const filters = defineModel<ProjectFilterValues>('filters', {
    required: true,
});

const emit = defineEmits<{
    submit: [];
    clear: [];
}>();
</script>

<template>
    <form
        novalidate
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        @submit.prevent="emit('submit')"
    >
        <div class="flex flex-col gap-1">
            <h2 class="text-base font-semibold">Filtros</h2>
            <p class="text-sm text-muted-foreground">Refine por nome, identificador, status e visibilidade.</p>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(18rem,1fr)_auto] xl:items-end">
            <div class="grid gap-2">
                <Label for="project-search">Pesquisar</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input
                        id="project-search"
                        v-model="filters.search"
                        class="pl-9"
                        placeholder="Nome, identificador ou slug"
                    />
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2 sm:flex sm:justify-end">
                <Button type="submit">Filtrar</Button>
                <Button type="button" variant="outline" class="gap-2" @click="emit('clear')">
                    <X class="size-4" />
                    Limpar
                </Button>
            </div>
        </div>

        <div class="grid gap-4 md:grid-cols-3">
            <div class="grid gap-2">
                <Label for="status-filter">Status</Label>
                <Select v-model="filters.status">
                    <SelectTrigger id="status-filter" class="w-full"><SelectValue placeholder="Todos" /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem v-for="status in statuses" :key="status.value" :value="String(status.value)">
                            {{ status.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-2">
                <Label for="visibility-filter">Visibilidade</Label>
                <Select v-model="filters.visibility">
                    <SelectTrigger id="visibility-filter" class="w-full"
                        ><SelectValue placeholder="Todas"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas</SelectItem>
                        <SelectItem
                            v-for="visibility in visibilities"
                            :key="visibility.value"
                            :value="String(visibility.value)"
                        >
                            {{ visibility.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <div class="grid gap-2">
                <Label for="deleted-status-filter">Situação</Label>
                <Select v-model="filters.deleted_status">
                    <SelectTrigger id="deleted-status-filter" class="w-full"
                        ><SelectValue placeholder="Todas"
                    /></SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas</SelectItem>
                        <SelectItem v-for="status in deletedStatuses" :key="status.value" :value="status.value">
                            {{ status.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>
        </div>
    </form>
</template>
