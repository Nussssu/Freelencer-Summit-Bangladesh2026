<script setup lang="ts">
import type { Winner } from '@/types/inner';

// Winner spotlight from the client mock-up: photo, a gold "Winner Spotlight" tag on a gold rule, then a deep teal panel.
// The awards page leads with the category and its line; `archive` (the winners page) leads with the winner's name and
// gives the category underneath.
withDefaults(defineProps<{ winner: Winner; archive?: boolean }>(), { archive: false });
</script>

<template>
    <article class="flex h-full flex-col overflow-hidden rounded-3xl bg-[#06535b] shadow-[0_24px_50px_-30px_rgb(8_58_64/0.7)]">
        <div class="relative aspect-[382/252] overflow-hidden">
            <img :src="winner.photo" alt="" loading="lazy" class="h-full w-full object-cover" />
        </div>
        <div class="relative flex-1 bg-gradient-to-b from-[#0b7076] to-[#054a50] px-6 pt-9 pb-7">
            <span class="absolute inset-x-0 top-0 h-1 bg-gradient-to-r from-[#f6cf6a] via-[#e3a92e] to-[#f6cf6a]" aria-hidden="true" />
            <span class="absolute -top-3.5 left-6 rounded-md bg-gradient-to-b from-[#f8d77a] to-[#dca636] px-4 py-1 text-sm font-bold text-night shadow-[0_6px_14px_-8px_rgb(0_0_0/0.6)]">Winner Spotlight</span>
            <template v-if="archive">
                <h3 class="text-2xl font-bold text-white">{{ winner.name }}</h3>
                <p class="mt-1.5 font-medium text-[#f6cf6a]">{{ winner.category }}</p>
            </template>
            <template v-else>
                <h3 class="text-2xl font-bold text-white">{{ winner.category }}</h3>
                <p v-if="winner.text" class="mt-2 leading-snug text-white/85">{{ winner.text }}</p>
            </template>
        </div>
    </article>
</template>
