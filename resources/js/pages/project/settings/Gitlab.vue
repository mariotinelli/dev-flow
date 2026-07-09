<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import { gitlab } from '@/routes/project/settings';
import { index as projectRolesIndex } from '@/routes/project/settings/roles';

defineOptions({
    layout: {
            breadcrumbs: [
                {
                    title: 'Configurações do Projeto',
                    href: projectRolesIndex(),
                },
                {
                    title: 'Gitlab',
                    href: gitlab(),
                },
            ],
    },
});
</script>

<template>
    <Head title="Configurações do Gitlab" />

    <div class="space-y-6">
        <Heading variant="small" title="Gitlab" description="Configure a integração do projeto com o Gitlab." />

        <div class="rounded-xl border border-dashed border-sidebar-border/70 bg-card p-5 dark:border-sidebar-border">
            <p class="text-sm text-muted-foreground">Integração com Gitlab será configurada aqui.</p>
        </div>
    </div>
</template>
