<script setup lang="ts">
import { ImageUp, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<{
    existingImageUrl?: string | null;
}>();

const emit = defineEmits<{
    removeExisting: [];
}>();

const model = defineModel<File | null>({ default: null });

const showExisting = ref(!!props.existingImageUrl);
const preview = ref<string | null>(null);

watch(() => props.existingImageUrl, (val) => {
    if (!model.value) {
        showExisting.value = !!val;
        preview.value = null;
    }
});

function handleFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) return;

    model.value = file;
    showExisting.value = false;
    preview.value = URL.createObjectURL(file);
}

function clearImage() {
    if (preview.value) {
        model.value = null;
        preview.value = null;

        return;
    }

    if (showExisting.value) {
        showExisting.value = false;
        emit('removeExisting');
    }
}
</script>

<template>
    <div class="flex h-full flex-col gap-2">
        <label
            class="group relative flex min-h-40 flex-1 cursor-pointer flex-col items-center justify-center rounded-xl border border-dashed bg-muted/30 p-6 text-center transition hover:bg-muted/50"
        >
            <input v-bind="$attrs" type="file" class="sr-only" @change="handleFile" />

            <template v-if="!preview && !showExisting">
                <div class="mb-3 rounded-full bg-background p-3 shadow-sm">
                    <ImageUp class="size-6 text-muted-foreground" />
                </div>

                <p class="text-sm font-medium">Clique para enviar uma imagem</p>

                <p class="mt-1 text-xs text-muted-foreground">PNG, JPG ou WEBP até 2MB</p>
            </template>

            <template v-else-if="showExisting && !preview">
                <div class="absolute inset-0 flex items-center justify-center rounded-xl bg-muted p-3">
                    <img
                        :src="existingImageUrl"
                        alt="Imagem existente"
                        class="max-h-full max-w-full rounded-lg object-contain"
                    />
                </div>

                <div class="absolute inset-0 rounded-xl bg-black/35 opacity-0 transition group-hover:opacity-100" />

                <div class="relative z-10 opacity-0 transition group-hover:opacity-100">
                    <p class="rounded-md bg-background px-3 py-1 text-sm font-medium shadow-sm">Trocar imagem</p>
                </div>
            </template>

            <template v-else>
                <div class="absolute inset-0 flex items-center justify-center rounded-xl bg-muted p-3">
                    <img
                        :src="preview"
                        alt="Preview da imagem"
                        class="max-h-full max-w-full rounded-lg object-contain"
                    />
                </div>

                <div class="absolute inset-0 rounded-xl bg-black/35 opacity-0 transition group-hover:opacity-100" />

                <div class="relative z-10 opacity-0 transition group-hover:opacity-100">
                    <p class="rounded-md bg-background px-3 py-1 text-sm font-medium shadow-sm">Trocar imagem</p>
                </div>
            </template>
        </label>

        <Button v-if="preview || showExisting" type="button" variant="outline" size="sm" class="gap-2" @click="clearImage">
            <X class="size-4" />
            Remover imagem
        </Button>
    </div>
</template>
