<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <div
        data-slot="sidebar-content"
        data-sidebar="content"
        :class="
            cn(
                'flex min-h-0 flex-1 scrollbar-thin scrollbar-thumb-sidebar-border/70 scrollbar-track-transparent flex-col gap-2 overflow-x-hidden overflow-y-auto group-data-[collapsible=icon]:overflow-hidden hover:scrollbar-thumb-sidebar-border [&::-webkit-scrollbar]:w-1.5 [&::-webkit-scrollbar-thumb]:rounded-full [&::-webkit-scrollbar-thumb]:bg-sidebar-border/70 hover:[&::-webkit-scrollbar-thumb]:bg-sidebar-border [&::-webkit-scrollbar-track]:bg-transparent',
                props.class,
            )
        "
    >
        <slot />
    </div>
</template>
