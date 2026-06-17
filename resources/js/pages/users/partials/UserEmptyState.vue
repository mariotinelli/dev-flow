<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { UserRound } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { create } from '@/routes/users';
</script>

<template>
    <div
        class="flex min-h-80 flex-col items-center justify-center gap-4 rounded-xl border border-dashed border-sidebar-border/70 p-8 text-center dark:border-sidebar-border"
    >
        <div class="rounded-full bg-muted p-4">
            <UserRound class="size-8 text-muted-foreground" />
        </div>
        <div class="space-y-1">
            <h2 class="text-lg font-semibold">Nenhum usuário cadastrado</h2>
            <p class="text-sm text-muted-foreground">
                Cadastre o primeiro usuário e um link serádo enviado para definição de senha.
            </p>
        </div>
        <Button as-child>
            <Link :href="create()">Novo usuário</Link>
        </Button>
    </div>
</template>
