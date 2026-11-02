<script setup lang="ts">
import { ref } from 'vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { EASE, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage } from '@/types/inner';

// Short title band: keynote photo under a teal glow that falls off to night at the edges, only the page title
// centred with the emphasis in gold. The hero actions are not shown here; the ghost one closes the page.
// data-header keeps the solid header from the top.
defineProps<{ page: InnerPage }>();
const root = ref<HTMLElement>();
useReveal(root);

useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-hero-img]', { scale: 1.08 }, { scale: 1, duration: 2.4, ease: EASE });
});
</script>

<template>
    <section ref="root" class="relative isolate overflow-hidden bg-night text-paper" data-header="light" aria-labelledby="about-title">
        <div v-if="page.hero.image" class="absolute inset-0 -z-10" aria-hidden="true">
            <img data-hero-img :src="page.hero.image" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover object-[50%_40%]" />
        </div>
        <div class="hero-glow absolute inset-0 -z-10" aria-hidden="true" />

        <!-- shortcut: xl offset hard-codes the 2-row header (153px), same as PageHero; drop it once --header-h is fixed in app.css. -->
        <div class="container-site flex min-h-[22rem] flex-col items-center justify-center pt-[calc(var(--header-h)+3.5rem)] pb-16 text-center md:min-h-[28rem] md:pb-20 xl:pt-[calc(153px+3.5rem)]">
            <SplitHeading
                id="about-title"
                as="h1"
                :text="page.hero.title"
                :emphasis="page.hero.emphasis"
                emphasis-class="text-gold"
                class="font-display text-[clamp(3.5rem,9vw,7.5rem)] leading-[0.9] font-black tracking-[-0.01em] uppercase"
            />
        </div>
    </section>
</template>

<style scoped>
/* The site's teal as a glow left of centre, deepening to night at the edges (the reference's radial falloff) */
.hero-glow {
    background:
        radial-gradient(70% 95% at 40% 50%, rgb(8 127 140 / 0.8), rgb(6 69 76 / 0.86) 45%, rgb(7 20 17 / 0.95) 100%);
}
</style>
