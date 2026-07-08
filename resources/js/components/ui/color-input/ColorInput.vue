<script setup lang="ts">
import { computed, ref } from 'vue';
import { cn } from '@/lib/utils';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

const props = withDefaults(
    defineProps<{
        id: string;
        name: string;
        defaultValue?: string | null;
        required?: boolean;
        class?: string;
    }>(),
    {
        defaultValue: '#2563eb',
        required: false,
    },
);

const colors = [
    '#EF4444',
    '#F97316',
    '#F59E0B',
    '#84CC16',
    '#22C55E',
    '#14B8A6',
    '#06B6D4',
    '#3B82F6',
    '#6366F1',
    '#8B5CF6',
    '#D946EF',
    '#EC4899',
    '#64748B',
    '#0F172A',
];

const selectedColor = ref(normalizeColor(props.defaultValue ?? '#2563eb'));

const normalizedColor = computed(() => selectedColor.value.toUpperCase());
const swatchColor = computed(() => (isHexColor(selectedColor.value) ? selectedColor.value : '#ffffff'));

function selectColor(color: string): void {
    selectedColor.value = normalizeColor(color);
}

function normalizeColor(color: string): string {
    const value = color.trim().toUpperCase();

    return value.startsWith('#') ? value : `#${value}`;
}

function isHexColor(color: string): boolean {
    return /^#[0-9A-F]{6}$/i.test(color);
}

function updateColor(value: string): void {
    selectedColor.value = normalizeColor(value);
}
</script>

<template>
    <Popover>
        <div
            :class="
                cn(
                    'relative flex h-9 w-full items-center rounded-md border border-input bg-transparent shadow-xs',
                    props.class,
                )
            "
        >
            <PopoverTrigger as-child>
                <button
                    type="button"
                    class="ml-1.5 flex size-6 shrink-0 items-center justify-center rounded-md border border-border bg-background shadow-xs ring-offset-background transition hover:scale-105 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                    :aria-label="`Selecionar cor ${normalizedColor}`"
                >
                    <span
                        class="size-4 rounded-sm border border-black/10 shadow-inner"
                        :style="{ backgroundColor: swatchColor }"
                    />
                </button>
            </PopoverTrigger>

            <input
                :id="id"
                :name="name"
                type="text"
                :required="required"
                :value="normalizedColor"
                maxlength="7"
                spellcheck="false"
                autocomplete="off"
                class="h-full min-w-0 flex-1 bg-transparent px-3 py-1 text-sm font-medium tracking-wide text-foreground outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50"
                placeholder="#2563EB"
                @input="updateColor(($event.target as HTMLInputElement).value)"
                @blur="selectedColor = normalizedColor"
            />
        </div>

        <PopoverContent align="start" class="w-72 p-0">
            <div class="grid gap-4 p-4">
                <label
                    class="relative flex h-36 cursor-pointer items-center justify-center overflow-hidden rounded-lg border border-border shadow-inner"
                    :style="{ backgroundColor: swatchColor }"
                >
                    <span class="rounded-md bg-background/90 px-2 py-1 text-xs font-medium shadow-sm backdrop-blur">
                        {{ normalizedColor }}
                    </span>
                    <input
                        type="color"
                        class="absolute inset-0 size-full cursor-pointer opacity-0"
                        :value="swatchColor"
                        @input="selectColor(($event.target as HTMLInputElement).value)"
                    />
                </label>

                <div class="grid gap-2">
                    <p class="text-xs font-medium text-muted-foreground">Sugestões</p>
                    <div class="grid grid-cols-7 gap-2" aria-label="Sugestões de cor">
                        <button
                            v-for="color in colors"
                            :key="color"
                            type="button"
                            class="size-7 rounded-md border border-border shadow-xs ring-offset-background transition hover:scale-110 focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 focus-visible:outline-none"
                            :class="
                                color.toLowerCase() === selectedColor.toLowerCase()
                                    ? 'ring-2 ring-ring ring-offset-2'
                                    : ''
                            "
                            :style="{ backgroundColor: color }"
                            :aria-label="`Selecionar ${color}`"
                            @click="selectColor(color)"
                        />
                    </div>
                </div>
            </div>
        </PopoverContent>
    </Popover>
</template>
