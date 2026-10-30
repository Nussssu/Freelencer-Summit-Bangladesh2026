<script setup lang="ts">
import { onMounted, ref } from 'vue';

// Confirmation panel for thank-you pages: heading, the reference number in mono, next steps, actions.
defineProps<{ title: string; reference: string; referenceLabel: string; body?: string }>();
const box = ref<HTMLElement>();
onMounted(() => box.value?.focus());
</script>

<template>
    <div ref="box" tabindex="-1" role="status" class="in-form outline-none">
        <span class="grid size-12 place-items-center rounded-full bg-green text-paper" aria-hidden="true">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="m5 12.5 4.5 4.5L19 7.5" /></svg>
        </span>
        <h2 class="in-h2 mt-6">{{ title }}</h2>
        <p class="mt-6 text-ink-2">{{ referenceLabel }}</p>
        <p class="mt-1 font-mono text-2xl font-semibold tracking-wider text-green">{{ reference }}</p>
        <p v-if="body" class="mt-6 max-w-xl text-lg">{{ body }}</p>
        <div v-if="$slots.default" class="mt-8 flex flex-wrap gap-3"><slot /></div>
    </div>
</template>
