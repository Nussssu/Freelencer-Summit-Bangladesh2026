<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';
import Photo from './Photo.vue';
import SampleRibbon from './SampleRibbon.vue';
import StatusBadge from './StatusBadge.vue';

/**
 * Generic card for programs, articles, sessions and organisations.
 * `layout="row"` gives the GITEX-style directory row (logo left, details, action right).
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
    }>(),
    { meta: () => [], tags: () => [], image: undefined, imageRatio: 'aspect-[4/3]', layout: 'stack', headingLevel: 'h3' },
);
</script>

<template>
    <article class="card relative flex h-full" :class="[href && 'card-hover', layout === 'row' ? 'flex-col gap-5 p-5 sm:flex-row sm:items-center' : 'flex-col']">
        <Photo v-if="image !== undefined && layout === 'stack'" :src="image" :class="imageRatio" />
        <div v-if="layout === 'row'" class="grid size-20 shrink-0 place-items-center border border-line-light bg-snow p-2">
            <img v-if="logo" :src="logo" alt="" class="max-h-full w-auto" loading="lazy" />
            <span v-else class="font-display text-2xl font-black text-ink-2" aria-hidden="true">{{ title.slice(0, 2) }}</span>
        </div>
        <div class="flex min-w-0 flex-1 flex-col" :class="layout === 'stack' && 'p-5'">
            <div v-if="eyebrow || status || sample" class="flex flex-wrap items-center gap-2">
                <span v-if="eyebrow" class="t-label text-green">{{ eyebrow }}</span>
                <StatusBadge v-if="status" :status="status.value" :label="status.label" />
                <SampleRibbon v-if="sample" />
            </div>
            <component :is="headingLevel" class="mt-2 text-xl leading-snug font-semibold text-balance">
                <Link v-if="href" :href="href" class="after:absolute after:inset-0">{{ title }}</Link>
                <template v-else>{{ title }}</template>
            </component>
            <ul v-if="meta.filter(Boolean).length" class="t-label mt-2 flex flex-wrap gap-x-3 gap-y-1 text-ink-2">
                <li v-for="m in meta.filter(Boolean)" :key="m!">{{ m }}</li>
            </ul>
            <p v-if="text" class="mt-3 line-clamp-3 text-ink-2">{{ text }}</p>
            <ul v-if="tags.length" class="mt-4 flex flex-wrap gap-1.5">
                <li v-for="tag in tags" :key="tag" class="t-label border border-line-light px-2 py-1 text-[0.6875rem]">{{ tag }}</li>
            </ul>
            <slot />
        </div>
        <Icon v-if="href && layout === 'row'" name="arrow" class="hidden shrink-0 text-green sm:block" />
    </article>
</template>
