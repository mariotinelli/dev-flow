<script setup lang="ts">
import type { User } from '@/types';
import UserCard from './UserCard.vue';

defineProps<{
    users: User[];
}>();
</script>

<template>
    <div class="grid gap-4 lg:grid-cols-2 xl:grid-cols-3">
        <UserCard v-for="user in users" :key="user.id" :user="user" />
    </div>
</template>
