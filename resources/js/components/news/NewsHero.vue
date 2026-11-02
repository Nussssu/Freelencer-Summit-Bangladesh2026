<script setup lang="ts">
import { ref } from 'vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { EASE, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';

// Short title band for the news, National Journey, agenda, awards and speakers pages (the reference's "LATEST NEWS" hero), in the About hero's treatment: a real
// event photo under a teal glow that falls off to night at the edges, the title centred with the emphasis in gold.
// data-header keeps the solid header from the top. `showcase` (a photo whose subject must stay visible: the awards trophy,
// the speakers stage) starts the photo below the header, frames it on `focus` and uses a lighter glow.
withDefaults(defineProps<{ title: string; emphasis?: string; image?: string; id?: string; showcase?: boolean; focus?: string }>(), {
    emphasis: '',
    image: '/media/photos/highlight-networking.jpg',
    id: 'news-title',
    showcase: false,
    focus: '50% 35%',
});
const root = ref<HTMLElement>();
useReveal(root);

useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-hero-img]', { scale: 1.08 }, { scale: 1, duration: 2.4, ease: EASE });
});
</script>

<template>
    <section ref="root" class="relative isolate overflow-hidden bg-night text-paper" data-header="light" :aria-labelledby="id">
        <!-- shortcut: showcase offsets hard-code the measured header heights (65 / 113 / 153px); --header-h is 112px throughout. -->
        <div class="absolute inset-0 -z-10 overflow-hidden" :class="showcase && 'top-[65px] md:top-[113px] xl:top-[153px]'" aria-hidden="true">
            <img data-hero-img :src="image" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover" :style="{ objectPosition: focus }" />
        </div>
        <div class="absolute inset-0 -z-10" :class="showcase ? 'hero-glow-soft' : 'hero-glow'" aria-hidden="true" />

        <!-- shortcut: xl offset hard-codes the 2-row header (153px), same as PageHero; drop it once --header-h is fixed in app.css. -->
        <div
            class="container-site flex min-h-[22rem] flex-col items-center justify-center pt-[calc(var(--header-h)+3.5rem)] pb-16 text-center md:min-h-[28rem] md:pb-20 xl:pt-[calc(153px+3.5rem)]"
            :class="showcase && 'md:min-h-[32rem]'"
        >
            <SplitHeading
                :id="id"
                as="h1"
                :text="title"
                :emphasis="emphasis"
                emphasis-class="text-gold"
                class="font-display text-[clamp(3.5rem,9vw,7.5rem)] leading-[0.9] font-black tracking-[-0.01em] uppercase"
                :class="showcase && 'drop-shadow-[0_2px_20px_rgb(7_20_17/0.5)]'"
            />
        </div>
    </section>
</template>

<style scoped>
/* The site's teal as a glow left of centre, deepening to night at the edges (same as the About hero) */
.hero-glow {
    background: radial-gradient(70% 95% at 40% 50%, rgb(8 127 140 / 0.8), rgb(6 69 76 / 0.86) 45%, rgb(7 20 17 / 0.95) 100%);
}
/* Showcase: the same teal falloff, lighter, so the photo's subject reads through */
.hero-glow-soft {
    background: radial-gradient(75% 110% at 35% 50%, rgb(8 127 140 / 0.62), rgb(6 69 76 / 0.5) 50%, rgb(7 20 17 / 0.45) 100%);
}
</style>
