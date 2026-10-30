<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import SampleRibbon from './SampleRibbon.vue';
import StatusBadge from './StatusBadge.vue';

/**
 * Card for programmes, articles, sessions and organisations, in the landing's Exhibition card style (rounded photo,
 * tinted body, round arrow). `layout="row"` gives the directory row (logo left, details, arrow right).
 */
withDefaults(
    defineProps<{
        title: string;
        href?: string;
        eyebrow?: string;
        meta?: (string | null)[];
        text?: string | null;
        tags?: string[];
        image?: string | null;
        imageRatio?: string;
        logo?: string | null;
        status?: { value: string; label: string };
        sample?: boolean;
        layout?: 'stack' | 'row';
        headingLevel?: 'h2' | 'h3';
        tone?: string;
    }>(),
    { meta: () => [], tags: () => [], image: undefined, imageRatio: 'aspect-[16/10]', layout: 'stack', headingLevel: 'h3', tone: 'tone-teal' },
);
</script>

<template>
    <article class="in-card is-washed h-full overflow-hidden" :class="[tone, href && 'is-link', layout === 'row' && 'gap-5 p-5 sm:flex-row! sm:items-center']">
        <span v-if="image !== undefined && layout === 'stack'" class="in-media" :class="imageRatio">
            <img v-if="image" :src="image" alt="" loading="lazy" />
        </span>
        <div v-if="layout === 'row'" class="grid size-20 shrink-0 place-items-center overflow-hidden rounded-2xl border border-night/8 bg-snow p-2">
            <img v-if="logo" :src="logo" alt="" class="max-h-full w-auto" loading="lazy" />
            <span v-else class="text-2xl font-extrabold uppercase" :style="{ color: 'var(--tint)' }" aria-hidden="true">{{ title.slice(0, 2) }}</span>
        </div>
        <div class="flex min-w-0 flex-1 flex-col" :class="layout === 'stack' && 'in-pad'">
            <div v-if="eyebrow || status || sample" class="flex flex-wrap items-center gap-2">
                <span v-if="eyebrow" class="in-pill">{{ eyebrow }}</span>
                <StatusBadge v-if="status" :status="status.value" :label="status.label" />
                <SampleRibbon v-if="sample" />
            </div>
            <component :is="headingLevel" class="in-h3 mt-3 text-balance">
                <Link v-if="href" :href="href" class="after:absolute after:inset-0">{{ title }}</Link>
                <template v-else>{{ title }}</template>
            </component>
            <ul v-if="meta.filter(Boolean).length" class="in-meta mt-2 flex flex-wrap gap-x-3 gap-y-1">
                <li v-for="m in meta.filter(Boolean)" :key="m!">{{ m }}</li>
            </ul>
            <p v-if="text" class="mt-3 line-clamp-3 text-ink-2">{{ text }}</p>
            <ul v-if="tags.length" class="mt-4 flex flex-wrap gap-1.5">
                <li v-for="tag in tags" :key="tag" class="in-pill">{{ tag }}</li>
            </ul>
            <slot />
            <span v-if="href && layout === 'stack'" class="in-arrow mt-auto" :class="'mt-6'" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
        </div>
        <span v-if="href && layout === 'row'" class="in-arrow max-sm:hidden" aria-hidden="true"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg></span>
    </article>
</template>
