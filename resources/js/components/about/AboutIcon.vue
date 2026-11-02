<script setup lang="ts">
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';

// Glyph set plus the line icons only the About mock-up uses (eye, laptop, network), same stroke style.
defineProps<{ name: string }>();
</script>

<template>
    <svg v-if="name === 'eye'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <path d="M2.5 12S6 5.5 12 5.5 21.5 12 21.5 12 18 18.5 12 18.5 2.5 12 2.5 12Z" /><circle cx="12" cy="12" r="3" />
    </svg>
    <svg v-else-if="name === 'laptop'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <rect x="4.5" y="5" width="15" height="10.5" rx="1.5" /><path d="M2.5 18.5h19M10 18.5h4" />
    </svg>
    <svg v-else-if="name === 'network'" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
        <circle cx="12" cy="5.5" r="2.5" /><circle cx="5.5" cy="18" r="2.5" /><circle cx="18.5" cy="18" r="2.5" /><path d="M12 8v4.5M12 12.5 7.3 16.2M12 12.5l4.7 3.7" />
    </svg>
    <Glyph v-else :name="name as GlyphName" />
</template>
