<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { ChevronRight } from "@lucide/vue"
import { Primitive } from "reka-ui"
import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const props = withDefaults(defineProps<PrimitiveProps & {
  class?: HTMLAttributes["class"]
}>(), {
  as: "a",
})
</script>

<template>
  <Primitive
    data-slot="pagination-next"
    :as="as"
    :as-child="asChild"
    :class="cn(buttonVariants({ variant: 'ghost', size: 'default' }), 'gap-1 px-2.5 sm:pr-2.5', props.class)"
  >
    <slot>
      <span>Próxima</span>
      <ChevronRight class="size-4" />
    </slot>
  </Primitive>
</template>
