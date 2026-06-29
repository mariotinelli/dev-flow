<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { index as projectRolesIndex } from '@/routes/project-settings/roles';
import { loom } from '@/routes/project-settings';

defineOptions({
    layout: {
            breadcrumbs: [
                {
                    title: 'Configurações do Projeto',
                    href: projectRolesIndex(),
                },
                {
                    title: 'Loom',
                    href: loom(),
                },
            ],
    },
});
</script>

<template>
    <Head title="Configurações do Loom" />

    <div class="space-y-6">
        <Heading variant="small" title="Loom" description="Configure a integração do projeto com o Loom." />

        <div class="rounded-xl border border-dashed border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
            <p class="text-sm text-muted-foreground">Integração com Loom será configurada aqui.</p>
        </div>
    </div>
</template>
