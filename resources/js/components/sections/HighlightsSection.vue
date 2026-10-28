<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { Heading, Highlight } from '@/types/content';

// Section 6, the ice-blue colour break. Desktop: pinned horizontal scroll. Mobile: native swipe with snap.
defineProps<{ highlights: Highlight[]; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const track = ref<HTMLElement>();
useReveal(root);

useGsap(root, () => {
    gsap.matchMedia().add('(min-width: 768px) and (prefers-reduced-motion: no-preference)', () => {
        const el = track.value!;
        const distance = () => el.scrollWidth - innerWidth + 64;
        gsap.to(el, {
            x: () => -distance(),
            ease: 'none',
            scrollTrigger: { trigger: root.value, start: 'top top', end: () => `+=${distance()}`, pin: true, scrub: 1, invalidateOnRefresh: true },
        });
    });
});
</script>

<template>
    <section ref="root" data-section="6" aria-labelledby="hl-title" class="relative z-[1] overflow-hidden bg-mist text-night">
        <div class="flex min-h-[100svh] flex-col justify-center py-16 md:pt-[calc(var(--header-h)+24px)]">
            <div class="container-site">
                <p class="t-label text-green">{{ t('sections.highlights') }}</p>
                <SplitHeading id="hl-title" :text="heading.title" :emphasis="heading.emphasis" class="t-h2 mt-3 max-w-4xl" emphasis-class="text-green" style="--outline: var(--green)" />
            </div>
            <ol ref="track" class="hl-track mt-10 flex snap-x snap-mandatory gap-4 overflow-x-auto px-4 pb-4 md:w-max md:overflow-visible md:px-8">
                <li v-for="(h, i) in highlights" :key="h.title" class="hl-panel flex w-[78vw] shrink-0 snap-start flex-col card card-hover p-5 md:w-[min(26rem,32vw)]" :class="i % 2 ? 'md:rotate-2' : 'md:-rotate-2'">
                    <span class="font-display text-7xl leading-none font-black text-green" aria-hidden="true">{{ String(i + 1).padStart(2, '0') }}</span>
                    <div class="duotone mt-4 aspect-[4/3] w-full" aria-hidden="true"><img v-if="h.image" :src="h.image" alt="" loading="lazy" class="h-full w-full object-cover" /></div>
                    <h3 class="mt-5 font-display text-3xl font-black uppercase">{{ h.title }}</h3>
                    <p class="mt-2 text-ink-2">{{ h.text }}</p>
                </li>
            </ol>
        </div>
    </section>
</template>

<style scoped>
.hl-track {
    scrollbar-width: none;
}
.hl-track::-webkit-scrollbar {
    display: none;
}
</style>
