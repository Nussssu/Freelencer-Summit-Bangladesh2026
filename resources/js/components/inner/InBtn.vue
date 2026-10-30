<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { utm, withUtm } from '@/composables/useUtm';
import type { CtaTone } from '@/types/content';

// The landing's teal pill CTA with an arrow. gold: the one primary action per view; ghost/outline: secondary.
const props = withDefaults(defineProps<{ href: string; tone?: CtaTone | 'teal'; external?: boolean; download?: boolean }>(), { tone: 'teal' });
const cls = computed(() => ['in-btn', props.tone === 'gold' && 'is-gold', (props.tone === 'ghost' || props.tone === 'outline') && 'is-ghost']);
const target = computed(() => (props.external || props.download || props.href.startsWith('#') ? props.href : withUtm(props.href, utm.value)));
</script>

<template>
    <a v-if="external || download || href.startsWith('#')" :href="target" :class="cls" :download="download || undefined" :target="external ? '_blank' : undefined" :rel="external ? 'noopener' : undefined">
        <slot />
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path :d="download ? 'M12 4v11M7 10.5l5 5 5-5M4.5 20h15' : 'M5 12h14M13 6l6 6-6 6'" /></svg>
    </a>
    <Link v-else :href="target" :class="cls">
        <slot />
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
    </Link>
</template>
