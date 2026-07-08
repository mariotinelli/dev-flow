<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { FolderPlus } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { create } from '@/routes/projects';
</script>

<template>
    <div
        class="flex min-h-80 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
    >
        <div class="flex size-14 items-center justify-center rounded-full bg-muted">
            <FolderPlus class="size-7 text-muted-foreground" />
        </div>
        <div class="space-y-1">
            <h2 class="text-lg font-semibold">Nenhum projeto encontrado</h2>
            <p class="max-w-md text-sm text-muted-foreground">
                Crie um projeto ou ajuste os filtros para encontrar iniciativas existentes.
            </p>
        </div>
        <Button as-child>
            <Link :href="create()">Novo projeto</Link>
        </Button>
    </div>
</template>
