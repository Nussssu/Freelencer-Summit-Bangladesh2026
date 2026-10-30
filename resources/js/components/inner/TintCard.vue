<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Glyph, { type GlyphName } from './Glyph.vue';

/**
 * The landing's tinted card (Why attend / Get involved / award cards): icon disc, title, text and, when it links,
 * a round arrow. layout "row" puts the disc beside the copy (Why attend); "stack" puts it on top.
 */
withDefaults(
    defineProps<{ title: string; text?: string | null; icon?: GlyphName; tone?: string; href?: string; external?: boolean; label?: string; layout?: 'stack' | 'row'; number?: string; headingLevel?: 'h2' | 'h3' }>(),
    { tone: 'tone-teal', layout: 'stack', headingLevel: 'h3' },
);
</script>

<template>
    <article class="in-card is-washed in-pad h-full" :class="[tone, href && 'is-link', layout === 'row' && 'flex-row! items-center gap-5']">
        <span v-if="number" class="in-num" aria-hidden="true">{{ number }}</span>
        <span v-else-if="icon" class="in-disc" :class="layout === 'stack' ? 'mb-5' : 'max-sm:w-12!'"><Glyph :name="icon" /></span>
        <div class="flex min-w-0 flex-1 flex-col" :class="number && 'mt-4'">
            <component :is="headingLevel" class="in-h3">
                <Link v-if="href && !external" :href="href" class="after:absolute after:inset-0">{{ title }}</Link>
                <a v-else-if="href" :href="href" target="_blank" rel="noopener" class="after:absolute after:inset-0">{{ title }}</a>
                <template v-else>{{ title }}</template>
            </component>
            <p v-if="text" class="mt-2 text-[0.96875rem] leading-relaxed text-ink-2">{{ text }}</p>
            <slot />
            <span v-if="href && layout === 'stack'" class="mt-auto flex items-center gap-3 pt-6">
                <span class="in-arrow" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
                <span v-if="label" class="font-semibold" :style="{ color: 'var(--tint)' }">{{ label }}</span>
            </span>
        </div>
        <span v-if="href && layout === 'row'" class="in-arrow max-sm:hidden" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
    </article>
</template>
