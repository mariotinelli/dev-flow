<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import {
    BarChart3,
    BookOpen,
    Bot,
    FileText,
    FolderKanban,
    LayoutGrid,
    ListChecks,
    ListTodo,
    Settings,
    ShieldCheck,
    Users,
    Zap,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import { index as roles } from '@/routes/roles';
import { index as users } from '@/routes/users';
import type { Auth, NavGroup } from '@/types';

const page = usePage<{ auth: Auth }>();

const projects = [
    {
        value: 'devflow',
        label: 'DevFlow',
        description: 'Sprint 12',
        badge: 'DF',
        badgeClass: 'bg-emerald-500/15 text-emerald-700 ring-emerald-500/20 dark:text-emerald-300',
    },
    {
        value: 'portal-cliente',
        label: 'Portal Cliente',
        description: 'Sprint 8',
        badge: 'PC',
        badgeClass: 'bg-sky-500/15 text-sky-700 ring-sky-500/20 dark:text-sky-300',
    },
    {
        value: 'app-interno',
        label: 'App Interno',
        description: 'Sprint 5',
        badge: 'AI',
        badgeClass: 'bg-violet-500/15 text-violet-700 ring-violet-500/20 dark:text-violet-300',
    },
];

const selectedProject = ref(projects[0].value);

const currentProject = computed(
    () => projects.find((project) => project.value === selectedProject.value) ?? projects[0],
);

const mainNavGroups: NavGroup[] = [
    {
        title: 'Visão Geral',
        items: [
            {
                title: 'Painel',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: 'Tarefas',
                href: dashboard(),
                icon: ListTodo,
            },
        ],
    },
    {
        title: 'Trabalho',
        items: [
            {
                title: 'Sprint',
                href: dashboard(),
                icon: Zap,
            },
            {
                title: 'Backlog',
                href: dashboard(),
                icon: ListChecks,
            },
        ],
    },
    {
        title: 'Inteligência',
        items: [
            {
                title: 'Chat IA',
                href: dashboard(),
                icon: Bot,
            },
        ],
    },
    {
        title: 'Membros & Docs',
        items: [
            {
                title: 'Documentação',
                href: dashboard(),
                icon: FileText,
            },
        ],
    },
    {
        title: 'Análises',
        items: [
            {
                title: 'Métricas',
                href: dashboard(),
                icon: BarChart3,
            },
            {
                title: 'Relatórios',
                href: dashboard(),
                icon: BookOpen,
            },
        ],
    },
    {
        title: 'Sistema',
        items: [
            {
                title: 'Projetos',
                href: dashboard(),
                icon: FolderKanban,
            },
            {
                title: 'Usuários',
                href: users(),
                icon: Users,
                permission: 'users.view',
            },
            {
                title: 'Perfis',
                href: roles(),
                icon: ShieldCheck,
                permission: 'roles.view',
            },
            {
                title: 'Configurações',
                href: dashboard(),
                icon: Settings,
            },
        ],
    },
];

const visibleMainNavGroups = computed<NavGroup[]>(() =>
    mainNavGroups
        .map((group) => ({
            ...group,
            items: group.items.filter((item) => !item.permission || page.props.auth.permissions[item.permission]),
        }))
        .filter((group) => group.items.length > 0),
);
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <div class="border-y border-sidebar-border/70 px-3 py-4 group-data-[collapsible=icon]:hidden">
            <div class="mb-2 flex items-center justify-between px-1">
                <span class="text-xs font-medium text-sidebar-foreground/70">Projeto Atual</span>
                <span
                    class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-300"
                >
                    Ativo
                </span>
            </div>
            <Select v-model="selectedProject">
                <SelectTrigger
                    class="group h-auto! w-full justify-start gap-3 overflow-hidden rounded-2xl border-sidebar-border/80 bg-linear-to-br from-sidebar-accent/90 to-sidebar-accent/35 px-2 py-2! text-sidebar-foreground shadow-none ring-1 ring-sidebar-border/40 transition hover:border-sidebar-ring/40 hover:bg-sidebar-accent hover:ring-sidebar-ring/25"
                >
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl text-xs font-semibold ring-1"
                        :class="currentProject.badgeClass"
                    >
                        {{ currentProject.badge }}
                    </span>
                    <span class="grid min-w-0 flex-1 text-left">
                        <span class="truncate text-sm leading-tight font-semibold">{{ currentProject.label }}</span>
                        <span class="truncate text-xs text-sidebar-foreground/60">{{
                            currentProject.description
                        }}</span>
                    </span>
                    <SelectValue class="sr-only" placeholder="Selecione um projeto" />
                </SelectTrigger>
                <SelectContent class="w-64 rounded-xl p-1.5">
                    <SelectItem v-for="project in projects" :key="project.value" :value="project.value">
                        <span class="flex items-center gap-3 py-1">
                            <span
                                class="flex size-8 items-center justify-center rounded-lg text-xs font-semibold ring-1"
                                :class="project.badgeClass"
                            >
                                {{ project.badge }}
                            </span>
                            <span class="grid min-w-0">
                                <span class="truncate font-medium">{{ project.label }}</span>
                                <span class="truncate text-xs text-muted-foreground">{{ project.description }}</span>
                            </span>
                        </span>
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <SidebarContent>
            <NavMain :groups="visibleMainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
