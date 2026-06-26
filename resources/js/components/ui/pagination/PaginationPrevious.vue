<script setup lang="ts">
import type { PrimitiveProps } from 'reka-ui';
import type { HTMLAttributes } from 'vue';
import { ChevronLeft } from '@lucide/vue';
import { Primitive } from 'reka-ui';
import { buttonVariants } from '@/components/ui/button';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<
        PrimitiveProps & {
            class?: HTMLAttributes['class'];
        }
    >(),
    {
        as: 'a',
    },
);
</script>

<template>
    <Primitive
        data-slot="pagination-previous"
        :as="as"
        :as-child="asChild"
        :class="cn(buttonVariants({ variant: 'ghost', size: 'default' }), 'gap-1 px-2.5 sm:pl-2.5', props.class)"
    >
        <slot>
            <ChevronLeft class="size-4" />
            <span>Anterior</span>
        </slot>
    </Primitive>
</template>
