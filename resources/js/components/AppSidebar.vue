<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
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
    ShieldCheck,
    Users,
    Zap,
} from '@lucide/vue';
import { computed } from 'vue';
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
import { index as developers } from '@/routes/developers';
import { index as roles } from '@/routes/roles';
import type { Auth, NavGroup, NavItem } from '@/types';

const page = usePage<{ auth: Auth }>();

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
                href: developers(),
                icon: Users,
                permission: 'developers.view',
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

const footerNavItems: NavItem[] = [
    {
        title: 'Repositório',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: FolderGit2,
    },
    {
        title: 'Documentação',
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
            <NavMain :groups="visibleMainNavGroups" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
