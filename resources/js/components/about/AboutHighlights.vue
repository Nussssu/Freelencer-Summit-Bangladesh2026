<script setup lang="ts">
import { ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import { gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { Highlight } from '@/types/content';
import type { SectionHeading } from '@/types/inner';
import AboutHead from './AboutHead.vue';

// Bento of the nine Dhaka highlights: fixed row heights (no layout shift while lazy images load), mixed tile sizes,
// captions that rise on hover (always shown on touch), soft scrubbed parallax inside each frame.
defineProps<{ heading: SectionHeading; highlights: Highlight[] }>();
const root = ref<HTMLElement>();
useReveal(root);

// 4-column desktop layout; the spans tile exactly nine cells (dense flow fills the gaps in order).
const SPANS = ['lg:col-span-2 lg:row-span-2', 'lg:col-span-2', '', '', 'lg:row-span-2', 'lg:col-span-2', 'lg:row-span-2', '', ''];

useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.utils.toArray<HTMLElement>('[data-par] img').forEach((img) =>
        gsap.fromTo(img, { yPercent: -4, scale: 1.12 }, { yPercent: 4, scale: 1.12, ease: 'none', scrollTrigger: { trigger: img.closest('li'), start: 'top bottom', end: 'bottom top', scrub: true } }),
    );
});
</script>

<template>
    <section ref="root" class="section bg-snow" aria-labelledby="ab-highlights">
        <div class="container-site">
            <AboutHead id="ab-highlights" :heading="heading" class="max-w-3xl" />
            <ul class="mt-14 grid auto-rows-[13rem] grid-cols-1 gap-3 sm:grid-cols-2 md:auto-rows-[15rem] lg:grid-flow-dense lg:grid-cols-4 lg:gap-4">
                <li v-for="(h, i) in highlights" :key="h.title" class="tile group relative overflow-hidden bg-night" :class="[SPANS[i], i === 0 && 'sm:col-span-2']" data-reveal="clip">
                    <Photo data-par :src="h.image" class="absolute! inset-0" />
                    <div class="absolute inset-0 bg-gradient-to-t from-night/85 via-night/25 to-transparent" aria-hidden="true" />
                    <div class="caption absolute inset-x-0 bottom-0 p-5 text-paper md:p-6">
                        <h3 class="font-display leading-none font-black uppercase" :class="i === 0 ? 'text-4xl md:text-5xl' : 'text-2xl md:text-3xl'">{{ h.title }}</h3>
                        <p class="detail mt-2 max-w-[38ch] text-sm leading-relaxed text-paper/85 md:text-base">{{ h.text }}</p>
                    </div>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
/* Caption rises on hover; the description is always present for screen readers and shown outright on touch */
@media (hover: hover) {
    .caption {
        transform: translateY(var(--rise, 2.75rem));
        transition: transform 0.6s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .detail {
        opacity: 0;
        transition: opacity 0.4s ease-out;
    }
    .tile:hover .caption {
        transform: none;
    }
    .tile:hover .detail {
        opacity: 1;
    }
}
@media (prefers-reduced-motion: reduce) {
    .caption {
        transform: none;
    }
    .detail {
        opacity: 1;
    }
}
</style>
