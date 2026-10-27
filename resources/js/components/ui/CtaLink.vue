<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { utm, withUtm } from '@/composables/useUtm';
import type { CtaTone } from '@/types/content';

const props = withDefaults(defineProps<{ href: string; tone?: CtaTone; label?: string }>(), { tone: 'gold' });
const toneClass = { gold: '', outline: 'btn-outline', red: 'btn-red', green: 'btn-green', blue: 'btn-blue', ghost: 'btn-ghost' };
const target = computed(() => withUtm(props.href, utm.value));
</script>

<template>
    <Link :href="target" class="btn" :class="toneClass[tone]">
        <slot>{{ label }}</slot>
        <svg width="14" height="14" viewBox="0 0 14 14" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M3 11 11 3M5 3h6v6" /></svg>
    </Link>
</template>
