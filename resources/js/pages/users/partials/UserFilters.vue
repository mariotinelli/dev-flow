<script setup lang="ts">
import { ChevronsUpDown, Search, X } from '@lucide/vue';
import { ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import type { UserFilterValues, SelectOption, StatusOption } from '@/types';

defineProps<{
    roles: SelectOption[];
    jobTitles: SelectOption[];
    contractTypes: SelectOption[];
    seniorities: SelectOption[];
    statuses: StatusOption[];
}>();

const roleOpen = ref(false);

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
        novalidate
        class="flex flex-col gap-5 rounded-xl border border-sidebar-border/70 bg-card p-5 shadow-xs dark:border-sidebar-border"
        @submit.prevent="emit('submit')"
    >
        <div class="flex flex-col gap-1">
            <h2 class="text-base font-semibold">Filtros</h2>
            <p class="text-sm text-muted-foreground">Refine a lista por acesso, status e informações profissionais.</p>
        </div>

        <div class="grid gap-4 xl:grid-cols-[minmax(18rem,1fr)_auto] xl:items-end">
            <div class="grid gap-2">
                <Label for="user-search">Pesquisar</Label>
                <div class="relative">
                    <Search
                        class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground"
                    />
                    <Input id="user-search" v-model="filters.search" class="pl-9" placeholder="Nome, e-mail ou cargo" />
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

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <div class="grid gap-2">
                <Label for="role-filter">Perfil</Label>
                <Popover v-model:open="roleOpen">
                    <PopoverTrigger as-child>
                        <Button
                            id="role-filter"
                            variant="outline"
                            role="combobox"
                            :aria-expanded="roleOpen"
                            class="w-full justify-between"
                        >
                            <span class="truncate">
                                {{
                                    filters.role !== 'all'
                                        ? roles.find((role) => String(role.value) === filters.role)?.label
                                        : 'Todos'
                                }}
                            </span>

                            <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
                        </Button>
                    </PopoverTrigger>

                    <PopoverContent class="w-(--reka-popover-trigger-width) p-0">
                        <Command>
                            <CommandInput placeholder="Pesquisar perfil..." />

                            <CommandList>
                                <CommandEmpty>Nenhum perfil encontrado.</CommandEmpty>

                                <CommandGroup>
                                    <CommandItem
                                        value="all"
                                        @select="
                                            filters.role = 'all';
                                            roleOpen = false;
                                        "
                                    >
                                        Todos
                                    </CommandItem>

                                    <CommandItem
                                        v-for="role in roles"
                                        :key="role.value"
                                        :value="String(role.value)"
                                        @select="
                                            filters.role = String(role.value);
                                            roleOpen = false;
                                        "
                                    >
                                        {{ role.label }}
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
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

            <div class="grid gap-2">
                <Label for="job_title-filter">Cargo</Label>
                <Select v-model="filters.job_title">
                    <SelectTrigger id="job_title-filter" class="w-full">
                        <SelectValue placeholder="Todos" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos</SelectItem>
                        <SelectItem v-for="jobTitle in jobTitles" :key="jobTitle.value" :value="String(jobTitle.value)">
                            {{ jobTitle.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
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
        </div>
    </form>
</template>
