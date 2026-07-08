<script setup lang="ts">
import { FileText, X } from '@lucide/vue';
import { ref, watch } from 'vue';
import { Button } from '@/components/ui/button';

defineOptions({
    inheritAttrs: false,
});

const props = defineProps<{
    existingFileName?: string | null;
}>();

const emit = defineEmits<{
    removeExisting: [];
}>();

const model = defineModel<File | null>({ default: null });

const showExisting = ref(!!props.existingFileName);
const fileName = ref<string | null>(null);
const fileSize = ref<string | null>(null);

watch(() => props.existingFileName, (val) => {
    if (!model.value) {
        showExisting.value = !!val;
    }
});

function formatSize(bytes: number): string {
    const units = ['B', 'KB', 'MB', 'GB'];
    let size = bytes;
    let unitIndex = 0;

    while (size >= 1024 && unitIndex < units.length - 1) {
        size /= 1024;
        unitIndex++;
    }

    return `${size.toFixed(unitIndex === 0 ? 0 : 1)} ${units[unitIndex] ?? ''}`;
}

function handleFile(event: Event) {
    const file = (event.target as HTMLInputElement).files?.[0];

    if (!file) return;

    model.value = file;
    showExisting.value = false;
    fileName.value = file.name;
    fileSize.value = formatSize(file.size);
}

function clearFile() {
    if (fileName.value) {
        model.value = null;
        fileName.value = null;
        fileSize.value = null;

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

            <template v-if="!fileName && !showExisting">
                <div class="mb-3 rounded-full bg-background p-3 shadow-sm">
                    <FileText class="size-6 text-muted-foreground" />
                </div>

                <p class="text-sm font-medium">Clique para enviar um arquivo</p>

                <p class="mt-1 text-xs text-muted-foreground">PDF, DOC, XLS, TXT, ZIP, CSV e mais</p>
            </template>

            <template v-else-if="showExisting && !fileName">
                <div class="absolute inset-0 flex items-center justify-center rounded-xl bg-muted p-3">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <FileText class="size-10 text-muted-foreground" />
                        <div class="min-w-0 max-w-full">
                            <p class="truncate text-sm font-medium">{{ existingFileName }}</p>
                            <p class="text-xs text-muted-foreground">Arquivo existente</p>
                        </div>
                    </div>
                </div>

                <div class="absolute inset-0 rounded-xl bg-black/35 opacity-0 transition group-hover:opacity-100" />

                <div class="relative z-10 opacity-0 transition group-hover:opacity-100">
                    <p class="rounded-md bg-background px-3 py-1 text-sm font-medium shadow-sm">Trocar arquivo</p>
                </div>
            </template>

            <template v-else>
                <div class="absolute inset-0 flex items-center justify-center rounded-xl bg-muted p-3">
                    <div class="flex flex-col items-center gap-2 text-center">
                        <FileText class="size-10 text-muted-foreground" />
                        <div class="min-w-0 max-w-full">
                            <p class="truncate text-sm font-medium">{{ fileName }}</p>
                            <p class="text-xs text-muted-foreground">{{ fileSize }}</p>
                        </div>
                    </div>
                </div>

                <div class="absolute inset-0 rounded-xl bg-black/35 opacity-0 transition group-hover:opacity-100" />

                <div class="relative z-10 opacity-0 transition group-hover:opacity-100">
                    <p class="rounded-md bg-background px-3 py-1 text-sm font-medium shadow-sm">Trocar arquivo</p>
                </div>
            </template>
        </label>

        <Button v-if="fileName || showExisting" type="button" variant="outline" size="sm" class="gap-2" @click="clearFile">
            <X class="size-4" />
            Remover arquivo
        </Button>
    </div>
</template>
