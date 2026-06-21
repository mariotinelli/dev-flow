<script setup lang="ts">
import { ImageUp, X } from "@lucide/vue"
import { ref } from "vue"
import { Button } from "@/components/ui/button"

const preview = ref<string | null>(null)

function handleFile(event: Event) {
  const file = (event.target as HTMLInputElement).files?.[0]

  if (!file) return

  preview.value = URL.createObjectURL(file)
}

function clearImage() {
  preview.value = null
}
</script>

<template>
  <div class="space-y-2">
    <label
      class="group relative flex min-h-40 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed bg-muted/30 p-6 text-center transition hover:bg-muted/50"
    >
      <input
        type="file"
        accept="image/*"
        class="sr-only"
        @change="handleFile"
      />

      <template v-if="!preview">
        <div class="mb-3 rounded-full bg-background p-3 shadow-sm">
          <ImageUp class="size-6 text-muted-foreground" />
        </div>

        <p class="text-sm font-medium">Clique para enviar uma imagem</p>

        <p class="mt-1 text-xs text-muted-foreground">
          PNG, JPG ou WEBP até 2MB
        </p>
      </template>

      <template v-else>
        <div
          class="absolute inset-0 flex items-center justify-center rounded-xl bg-muted p-3"
        >
          <img
            :src="preview"
            alt="Preview da imagem"
            class="max-h-full max-w-full rounded-lg object-contain"
          />
        </div>

        <div
          class="absolute inset-0 rounded-xl bg-black/35 opacity-0 transition group-hover:opacity-100"
        />

        <div class="relative z-10 opacity-0 transition group-hover:opacity-100">
          <p
            class="rounded-md bg-background px-3 py-1 text-sm font-medium shadow-sm"
          >
            Trocar imagem
          </p>
        </div>
      </template>
    </label>

    <Button
      v-if="preview"
      type="button"
      variant="outline"
      size="sm"
      class="gap-2"
      @click="clearImage"
    >
      <X class="size-4" />
      Remover imagem
    </Button>
  </div>
</template>
