<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ChevronLeft, ChevronRight } from '@lucide/vue';
import { computed } from 'vue';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationItem,
    PaginationLink,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { isPaginationEllipsis, paginationLabel } from '@/lib/pagination';
import type { PaginationMeta } from '@/types';

const props = defineProps<{
    meta: PaginationMeta;
}>();

const previousPageLink = computed(() => props.meta.links[0]);
const nextPageLink = computed(() => props.meta.links[props.meta.links.length - 1]);
const pageLinks = computed(() => props.meta.links.slice(1, -1));
</script>

<template>
    <div v-if="meta.total > 0" class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <p class="text-sm text-muted-foreground">
            Exibindo {{ meta.from }}-{{ meta.to }} de {{ meta.total }}
            usuários
        </p>

        <Pagination v-if="meta.last_page > 1" class="sm:mx-0 sm:w-auto">
            <PaginationContent>
                <PaginationItem>
                    <PaginationPrevious v-if="previousPageLink?.url" as-child>
                        <Link :href="previousPageLink.url" preserve-scroll>
                            <ChevronLeft class="size-4" />
                            <span>Anterior</span>
                        </Link>
                    </PaginationPrevious>
                    <PaginationPrevious v-else as="span" class="pointer-events-none opacity-50" />
                </PaginationItem>

                <PaginationItem v-for="link in pageLinks" :key="`${link.label}-${link.url}`">
                    <PaginationEllipsis v-if="isPaginationEllipsis(link.label)" />
                    <PaginationLink v-else-if="link.url" as-child :is-active="link.active">
                        <Link :href="link.url" preserve-scroll>
                            {{ paginationLabel(link.label) }}
                        </Link>
                    </PaginationLink>
                    <PaginationLink v-else as="span" class="pointer-events-none opacity-50">
                        {{ paginationLabel(link.label) }}
                    </PaginationLink>
                </PaginationItem>

                <PaginationItem>
                    <PaginationNext v-if="nextPageLink?.url" as-child>
                        <Link :href="nextPageLink.url" preserve-scroll>
                            <span>Próxima</span>
                            <ChevronRight class="size-4" />
                        </Link>
                    </PaginationNext>
                    <PaginationNext v-else as="span" class="pointer-events-none opacity-50" />
                </PaginationItem>
            </PaginationContent>
        </Pagination>
    </div>
</template>
