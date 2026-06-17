<script setup lang="ts">
import { Search, X } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { UserFilterValues, SelectOption, StatusOption } from '@/types';

defineProps<{
    jobTitles: SelectOption[];
    contractTypes: SelectOption[];
    seniorities: SelectOption[];
    statuses: StatusOption[];
}>();

const filters = defineModel<UserFilterValues>('filters', {
    required: true,
});

const emit = defineEmits<{
    submit: [];
    clear: [];
}>();
</script>

<template>
    <form
        class="grid gap-4 rounded-xl border border-sidebar-border/70 bg-card p-4 shadow-xs lg:grid-cols-[minmax(16rem,1fr)_repeat(3,minmax(10rem,13rem))_auto] lg:items-end dark:border-sidebar-border"
        @submit.prevent="emit('submit')"
    >
        <div class="grid gap-2">
            <Label for="user-search">Pesquisar</Label>
            <div class="relative">
                <Search
                    class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                />
                <Input
                    id="user-search"
                    v-model="filters.search"
                    class="pl-9"
                    placeholder="Nome, e-mail ou cargo"
                />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="contract-type-filter">Contrato</Label>
            <Select v-model="filters.contract_type">
                <SelectTrigger id="contract-type-filter" class="w-full">
                    <SelectValue placeholder="Todos" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem
                        v-for="contractType in contractTypes"
                        :key="contractType.value"
                        :value="String(contractType.value)"
                    >
                        {{ contractType.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="grid gap-2">
            <Label for="seniority-filter">Senioridade</Label>
            <Select v-model="filters.seniority">
                <SelectTrigger id="seniority-filter" class="w-full">
                    <SelectValue placeholder="Todas" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todas</SelectItem>
                    <SelectItem
                        v-for="seniority in seniorities"
                        :key="seniority.value"
                        :value="String(seniority.value)"
                    >
                        {{ seniority.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="grid gap-2">
            <Label for="job_title-filter">Cargo</Label>
            <Select v-model="filters.job_title">
                <SelectTrigger id="job_title-filter" class="w-full">
                    <SelectValue placeholder="Todas" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todas</SelectItem>
                    <SelectItem
                        v-for="jobTitle in jobTitles"
                        :key="jobTitle.value"
                        :value="String(jobTitle.value)"
                    >
                        {{ jobTitle.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="grid gap-2">
            <Label for="status-filter">Status</Label>
            <Select v-model="filters.status">
                <SelectTrigger id="status-filter" class="w-full">
                    <SelectValue placeholder="Todos" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem value="all">Todos</SelectItem>
                    <SelectItem v-for="status in statuses" :key="status.value" :value="status.value">
                        {{ status.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <div class="flex gap-2">
            <Button type="submit" class="flex-1 lg:flex-none">Filtrar</Button>
            <Button type="button" variant="outline" class="flex-1 gap-2 lg:flex-none" @click="emit('clear')">
                <X class="size-4" />
                Limpar
            </Button>
        </div>
    </form>
</template>
