<script setup lang="ts">
import type { PrimitiveProps } from "reka-ui"
import type { HTMLAttributes } from "vue"
import { Primitive } from "reka-ui"
import type { ButtonVariants } from "@/components/ui/button"
import { buttonVariants } from "@/components/ui/button"
import { cn } from "@/lib/utils"

const props = withDefaults(defineProps<PrimitiveProps & {
  class?: HTMLAttributes["class"]
  isActive?: boolean
  size?: ButtonVariants["size"]
}>(), {
  as: "a",
  size: "icon",
})
</script>

<template>
  <Primitive
    data-slot="pagination-link"
    :as="as"
    :as-child="asChild"
    :aria-current="isActive ? 'page' : undefined"
    :class="cn(buttonVariants({ variant: isActive ? 'outline' : 'ghost', size }), props.class)"
  >
    <slot />
  </Primitive>
</template>
