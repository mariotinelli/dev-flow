<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    BarChart3,
    BookOpen,
    Bot,
    FileText,
    FolderGit2,
    FolderKanban,
    LayoutGrid,
    ListChecks,
    ListTodo,
    Settings,
    Users,
    Zap,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
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
import type { NavGroup, NavItem } from '@/types';

const mainNavGroups: NavGroup[] = [
    {
        title: 'Visão Geral',
        items: [
            {
                title: 'Dashboard',
                href: dashboard(),
                icon: LayoutGrid,
            },
            {
                title: 'Projetos',
                href: dashboard(),
                icon: FolderKanban,
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
            {
                title: 'Desenvolvedores',
                href: dashboard(),
                icon: Users,
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
                title: 'Configurações',
                href: dashboard(),
                icon: Settings,
            },
        ],
    },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
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

        <SidebarContent>
            <NavMain :groups="mainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
