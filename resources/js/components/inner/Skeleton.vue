<script setup lang="ts">
// Loading placeholders for partial reloads; static under reduced motion (global CSS stops the pulse).
withDefaults(defineProps<{ count?: number; shape?: 'card' | 'row' }>(), { count: 6, shape: 'card' });
</script>

<template>
    <div v-for="i in count" :key="i" class="card animate-pulse" aria-hidden="true">
        <template v-if="shape === 'card'">
            <div class="aspect-[4/3] bg-night/5" />
            <div class="space-y-3 p-5"><div class="h-3 w-1/3 bg-night/10" /><div class="h-5 w-4/5 bg-night/10" /><div class="h-3 w-1/2 bg-night/10" /></div>
        </template>
        <div v-else class="flex items-center gap-5 p-5"><div class="size-20 bg-night/5" /><div class="flex-1 space-y-3"><div class="h-5 w-1/2 bg-night/10" /><div class="h-3 w-1/3 bg-night/10" /></div></div>
    </div>
</template>
