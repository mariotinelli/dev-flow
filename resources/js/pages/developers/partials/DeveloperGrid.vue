<script setup lang="ts">
import type { Developer } from '@/types';
import DeveloperCard from './DeveloperCard.vue';

defineProps<{
    developers: Developer[];
}>();
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        <DeveloperCard
            v-for="developer in developers"
            :key="developer.id"
            :developer="developer"
        />
    </div>
</template>
