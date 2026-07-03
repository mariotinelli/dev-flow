<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
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
import { index as projectMembersIndex } from '@/routes/project/members';
import { index as projectRolesIndex } from '@/routes/project-settings/roles';
import { select } from '@/routes/projects';
import { index as projectsIndex } from '@/routes/projects';
import { index as projectDocumentationsIndex } from '@/routes/project/documentations';
import { index as roles } from '@/routes/roles';
import { index as users } from '@/routes/users';
import type { Auth, NavGroup } from '@/types';

const page = usePage<{ auth: Auth }>();

const projects = computed(() => page.props.auth.projects);
const selectedProject = ref(page.props.auth.current_project ? String(page.props.auth.current_project.id) : '');

const currentProject = computed(() =>
    projects.value.find((project) => String(project.id) === selectedProject.value) ?? page.props.auth.current_project,
);

function projectBadge(project: NonNullable<typeof currentProject.value>): string {
    return project.key.split('-')[0] || project.name.slice(0, 2).toUpperCase();
}

function selectProject(projectId: string): void {
    selectedProject.value = projectId;

    router.post(
        select.url(Number(projectId)),
        {},
        {
            preserveScroll: true,
        },
    );
}

const mainNavGroups: NavGroup[] = [
    {
        title: 'Visão Geral',
        items: [
            {
                title: 'Painel',
                href: dashboard(),
                icon: LayoutGrid,
                permission: 'project.overview.view',
                permissionScope: 'project',
            },
            {
                title: 'Tarefas',
                href: dashboard(),
                icon: ListTodo,
                permission: 'project.tasks.view',
                permissionScope: 'project',
            },
        ],
    },
    {
        title: 'Trabalho',
        items: [
            {
                title: 'Ciclo',
                href: dashboard(),
                icon: Zap,
                permission: 'project.overview.view',
                permissionScope: 'project',
            },
            {
                title: 'Pendências',
                href: dashboard(),
                icon: ListChecks,
                permission: 'project.tasks.view',
                permissionScope: 'project',
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
        title: 'Projeto',
        items: [
            {
                title: 'Membros',
                href: projectMembersIndex(),
                icon: Users,
                permission: 'project.members.view',
                permissionScope: 'project',
            },
            {
                title: 'Documentação',
                href: projectDocumentationsIndex(),
                icon: FileText,
                permission: 'project.documents.view',
                permissionScope: 'project',
            },
            {
                title: 'Configurações',
                href: projectRolesIndex(),
                icon: Settings,
                permission: 'project.settings.manage',
                permissionScope: 'project',
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
                permission: 'project.metrics.view',
                permissionScope: 'project',
            },
            {
                title: 'Relatórios',
                href: dashboard(),
                icon: BookOpen,
                permission: 'project.metrics.view',
                permissionScope: 'project',
            },
        ],
    },
    {
        title: 'Sistema',
        items: [
            {
                title: 'Projetos',
                href: projectsIndex(),
                icon: FolderKanban,
                permission: 'projects.view',
                permissionScope: 'system',
            },
            {
                title: 'Usuários',
                href: users(),
                icon: Users,
                permission: 'users.view',
                permissionScope: 'system',
            },
            {
                title: 'Perfis',
                href: roles(),
                icon: ShieldCheck,
                permission: 'roles.view',
                permissionScope: 'system',
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
            items: group.items.filter((item) => {
                if (!item.permission) {
                    return true;
                }

                if (item.permissionScope === 'project' && !page.props.auth.current_project) {
                    return false;
                }

                return item.permissionScope === 'project'
                    ? page.props.auth.project_permissions[item.permission]
                    : page.props.auth.permissions[item.permission];
            }),
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

        <div v-if="currentProject" class="border-y border-sidebar-border/70 px-3 py-4 group-data-[collapsible=icon]:hidden">
            <div class="mb-2 flex items-center justify-between px-1">
                <span class="text-xs font-medium text-sidebar-foreground/70">Projeto Atual</span>
                <span
                    class="rounded-full bg-emerald-500/10 px-2 py-0.5 text-[10px] font-medium text-emerald-700 dark:text-emerald-300"
                >
                    Ativo
                </span>
            </div>
            <Select :model-value="selectedProject" @update:model-value="(value) => selectProject(String(value))">
                <SelectTrigger
                    class="group h-auto! w-full justify-start gap-3 overflow-hidden rounded-2xl border-sidebar-border/80 bg-linear-to-br from-sidebar-accent/90 to-sidebar-accent/35 px-2 py-2! text-sidebar-foreground shadow-none ring-1 ring-sidebar-border/40 transition hover:border-sidebar-ring/40 hover:bg-sidebar-accent hover:ring-sidebar-ring/25"
                >
                    <span
                        class="flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-500/15 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300"
                    >
                        {{ projectBadge(currentProject) }}
                    </span>
                    <span class="grid min-w-0 flex-1 text-left">
                        <span class="truncate text-sm leading-tight font-semibold">{{ currentProject.name }}</span>
                        <span class="truncate text-xs text-sidebar-foreground/60">{{ currentProject.key }}</span>
                    </span>
                    <SelectValue class="sr-only" placeholder="Selecione um projeto" />
                </SelectTrigger>
                <SelectContent class="w-64 rounded-xl p-1.5">
                    <SelectItem v-for="project in projects" :key="project.id" :value="String(project.id)">
                        <span class="flex items-center gap-3 py-1">
                            <span
                                class="flex size-8 items-center justify-center rounded-lg bg-emerald-500/15 text-xs font-semibold text-emerald-700 ring-1 ring-emerald-500/20 dark:text-emerald-300"
                            >
                                {{ projectBadge(project) }}
                            </span>
                            <span class="grid min-w-0">
                                <span class="truncate font-medium">{{ project.name }}</span>
                                <span class="truncate text-xs text-muted-foreground">{{ project.key }}</span>
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
