<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import Icon from '@/components/ui/Icon.vue';

// One news card in the reference press-release shape: photo on top, an off-white body rising over it with a raised
// left tab and an S-curve down to the right, gold tag (date or topic), uppercase title, optional short description
// and the teal "View more" pill. The whole card is the link. Width comes from the parent (grid cell or marquee slot);
// attributes such as aria-hidden / tabindex fall through to the link.
withDefaults(defineProps<{ title: string; href: string; tag: string; image: string; text?: string | null; lines?: 2 | 3 }>(), { text: null, lines: 2 });
</script>

<template>
    <Link
        :href="href"
        class="card group relative flex flex-col overflow-hidden rounded-[2rem] shadow-[0_28px_56px_-32px_rgb(7_20_17/0.5)] transition-[transform,box-shadow] duration-500 ease-[cubic-bezier(0.16,1,0.3,1)] hover:-translate-y-1.5 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-green"
    >
        <div class="relative aspect-[16/10] overflow-hidden bg-mist">
            <img :src="image" alt="" loading="lazy" decoding="async" class="h-full w-full object-cover transition-transform duration-700 ease-[cubic-bezier(0.16,1,0.3,1)] group-hover:scale-105" />
        </div>
        <!-- Body rises over the photo: a raised left tab with a rounded corner, an S-curve down to the right -->
        <svg class="relative -mt-14 block h-14 w-full" viewBox="0 0 400 56" preserveAspectRatio="none" aria-hidden="true">
            <path d="M0 56V26Q0 0 26 0H232C262 0 266 34 300 34H400V56Z" class="fill-[var(--card-body)]" />
        </svg>
        <div class="body relative -mt-11 flex flex-1 flex-col px-6 pb-7 text-left md:px-7">
            <span class="self-start rounded-full bg-gold px-4 py-1.5 text-sm font-bold text-night tabular-nums">{{ tag }}</span>
            <h3 class="mt-5 text-base leading-snug font-medium tracking-[0.02em] text-night uppercase md:text-lg" :class="lines === 3 ? 'line-clamp-3' : 'line-clamp-2'">{{ title }}</h3>
            <p v-if="text" class="mt-2 line-clamp-2 text-sm leading-relaxed text-ink-2">{{ text }}</p>
            <span class="mt-auto inline-flex min-h-11 items-center gap-3 self-start rounded-full bg-green px-6 text-sm font-bold tracking-[0.02em] text-snow uppercase transition-colors duration-300 group-hover:bg-[#066a75]" :class="text ? 'mt-6' : 'mt-5'">
                View more <Icon name="arrow" :size="16" class="transition-transform duration-300 group-hover:translate-x-1" />
            </span>
        </div>
    </Link>
</template>

<style scoped>
/* Card body: a soft off-white, shared by the curved tab (SVG) and the panel under it */
.card {
    --card-body: #f5f6f6;
}
.body {
    background: var(--card-body);
}
</style>
