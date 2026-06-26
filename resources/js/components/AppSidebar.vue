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
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import {
    Sidebar,
    SidebarContent,
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
    { value: 'devflow', label: 'DevFlow', badge: 'DF' },
    { value: 'portal-cliente', label: 'Portal Cliente', badge: 'PC' },
    { value: 'app-interno', label: 'App Interno', badge: 'AI' },
];

const selectedProject = ref(projects[0].value);

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
            <div class="mb-2 px-1 text-xs font-medium text-sidebar-foreground/70">Projeto Atual</div>
            <Select v-model="selectedProject">
                <SelectTrigger
                    class="h-auto w-full justify-start gap-3 rounded-xl border-sidebar-border/80 bg-sidebar-accent/50 px-3 py-2.5 text-sidebar-foreground shadow-none hover:bg-sidebar-accent"
                >
                    <span
                        class="flex size-8 shrink-0 items-center justify-center rounded-lg bg-sidebar-primary text-xs font-semibold text-sidebar-primary-foreground"
                    >
                        {{ projects.find((project) => project.value === selectedProject)?.badge }}
                    </span>
                    <SelectValue placeholder="Selecione um projeto" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem v-for="project in projects" :key="project.value" :value="project.value">
                        <span class="flex items-center gap-2">
                            <span
                                class="flex size-6 items-center justify-center rounded-md bg-muted text-xs font-semibold text-muted-foreground"
                            >
                                {{ project.badge }}
                            </span>
                            {{ project.label }}
                        </span>
                    </SelectItem>
                </SelectContent>
            </Select>
        </div>

        <SidebarContent>
            <NavMain :groups="visibleMainNavGroups" />
        </SidebarContent>
    </Sidebar>
    <slot />
</template>
