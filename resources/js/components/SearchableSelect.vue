<script setup lang="ts">
import { ChevronsUpDown } from '@lucide/vue';
import { computed, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { Command, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList } from '@/components/ui/command';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';

type SearchableSelectOption = {
    value: number | string;
    label: string;
};

const props = withDefaults(
    defineProps<{
        id?: string;
        name?: string;
        options: SearchableSelectOption[];
        placeholder?: string;
        searchPlaceholder?: string;
        emptyMessage?: string;
        disabled?: boolean;
    }>(),
    {
        placeholder: 'Selecione',
        searchPlaceholder: 'Pesquisar...',
        emptyMessage: 'Nenhum resultado encontrado.',
        disabled: false,
    },
);

const model = defineModel<string>({ required: true });
const isOpen = ref(false);

const selectedLabel = computed(() => props.options.find((option) => String(option.value) === model.value)?.label);

function selectOption(value: number | string): void {
    model.value = String(value);
    isOpen.value = false;
}
</script>

<template>
    <input v-if="name" type="hidden" :name="name" :value="model" />

    <Popover v-model:open="isOpen">
        <PopoverTrigger as-child>
            <Button
                :id="id"
                type="button"
                variant="outline"
                role="combobox"
                :aria-expanded="isOpen"
                :disabled="disabled"
                class="w-full justify-between"
            >
                <span class="truncate">
                    {{ selectedLabel ?? placeholder }}
                </span>

                <ChevronsUpDown class="size-4 shrink-0 opacity-50" />
            </Button>
        </PopoverTrigger>

        <PopoverContent class="w-(--reka-popover-trigger-width) p-0">
            <Command>
                <CommandInput :placeholder="searchPlaceholder" />

                <CommandList>
                    <CommandEmpty>{{ emptyMessage }}</CommandEmpty>

                    <CommandGroup>
                        <CommandItem
                            v-for="option in options"
                            :key="option.value"
                            :value="String(option.value)"
                            @select="selectOption(option.value)"
                        >
                            {{ option.label }}
                        </CommandItem>
                    </CommandGroup>
                </CommandList>
            </Command>
        </PopoverContent>
    </Popover>
</template>
