<script setup lang="ts">
import { computed } from 'vue';

// One badge for every status enum; colours follow the landing legend (mint available, gold reserved, red confirmed).
const props = defineProps<{ status: string; label: string }>();
const tone: Record<string, string> = {
    available: 'bg-mint/70 border-transparent',
    reserved: 'bg-gold border-transparent',
    confirmed: 'bg-red border-transparent',
    'registration-open': 'bg-green text-paper border-transparent',
    winner: 'bg-gold border-transparent',
    shortlisted: 'border-green text-green',
    completed: 'border-line-light text-ink-2',
    postponed: 'border-red-ink text-red-ink',
};
const cls = computed(() => tone[props.status] ?? 'border-green text-green');
</script>

<template>
    <span class="t-label inline-flex items-center border px-2 py-1 text-[0.6875rem] text-night" :class="cls">{{ label }}</span>
</template>
