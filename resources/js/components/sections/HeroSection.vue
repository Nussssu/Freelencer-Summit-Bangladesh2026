<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { gsap, useGsap } from '@/composables/motion';
import type { Hero } from '@/types/content';

// Section 1. Headline only, over the event photo. Intro (Lunia-style): three full-bleed crops of the photo
// stacked on top shrink into the centre one after another, uncovering the hero underneath.
defineProps<{ hero: Hero }>();
const { tm, rt } = useI18n();
const root = ref<HTMLElement>();
const lines = computed(() => (tm('brand.titleLines') as unknown[]).map((l) => rt(l as never)));
// "SUMMIT" animates per letter; split by grapheme so Bangla vowel signs stay attached to their consonant.
const graphemes = (s: string) => (typeof Intl.Segmenter === 'function' ? [...new Intl.Segmenter(undefined, { granularity: 'grapheme' }).segment(s)].map((g) => g.segment) : Array.from(s));

// Top layer first. Each is a different close crop of the same photo (focus point + zoom).
const layers = [
    { pos: '50% 50%', zoom: 1.8 },
    { pos: '18% 62%', zoom: 1.7 },
    { pos: '85% 55%', zoom: 1.7 },
];

useGsap(root, (reduced) => {
    if (reduced) return;
    const tl = gsap
        .timeline({ paused: true })
        .set('[data-hero-layer]', { visibility: 'visible' })
        // Same curve and overlap as the reference: 1.9s expo.inOut per layer, next one 0.5s later.
        .to('[data-hero-layer]', { scale: 0, duration: 1.9, ease: 'expo.inOut', stagger: { each: 0.5, from: 'end' }, force3D: true }, 0)
        .set('[data-hero-layer]', { display: 'none' })
        // Headline, as the reference's "LUNIA STUDIO" → "3D DIGITAL EXPERIENCES": "SUMMIT" loads first, large,
        // letter by letter; then it settles to size while the other lines slide up and the full title lands together.
        .from('[data-hero-letter]', { yPercent: 110, duration: 0.5, ease: 'none', stagger: 0.1 }, 0)
        .fromTo('[data-hero-summit]', { scale: 1.6 }, { scale: 1, duration: 1, ease: 'expo.inOut', immediateRender: true }, 1.1)
        .from('[data-hero-line]', { yPercent: 110, duration: 1.8, ease: 'expo.inOut', stagger: 0.3 }, 1.1)
        .fromTo(
            '#hero-title',
            { filter: 'drop-shadow(0 0 0px rgb(141 176 230 / 0))' },
            { filter: 'drop-shadow(0 0 24px rgb(141 176 230 / 0.55))', duration: 0.7, ease: 'sine.inOut', yoyo: true, repeat: 1, clearProps: 'filter' },
            2.7,
        );
    // Start only once the photo is decoded, so the first frames don't stutter.
    const img = root.value?.querySelector<HTMLImageElement>('[data-hero-layer] img');
    Promise.race([img?.decode() ?? Promise.resolve(), new Promise((r) => setTimeout(r, 800))])
        .catch(() => {})
        .then(() => tl.play());
});
</script>

<template>
    <section id="hero" ref="root" data-section="1" aria-labelledby="hero-title" class="grain relative flex min-h-[100svh] flex-col overflow-hidden text-paper pt-24 md:pt-[136px] xl:pt-[164px]">
        <!-- Event photo (Unsplash License, photo 6vAjp0pscX0 by Alexandre Pellaes): slow drift + light sweep; swap for summit photography later -->
        <div class="hero-photo absolute inset-0 -z-20 overflow-hidden bg-night" aria-hidden="true">
            <picture>
                <source media="(min-width: 768px)" srcset="/media/hero-event.webp" />
                <img src="/media/hero-event-m.webp" alt="" width="2000" height="1333" fetchpriority="high" decoding="async" class="h-full w-full object-cover" />
            </picture>
            <div class="hero-scrim absolute inset-0" />
        </div>

        <!-- Headline sits above the intro layers (as in the reference), so it shows from the first frame -->
        <div class="container-site relative z-20 flex flex-1 items-center justify-center pb-16 md:pb-24">
            <h1 id="hero-title" class="t-h1 hero-h1 text-center" :aria-label="lines.join(' ')">
                <template v-for="(line, i) in lines" :key="i">
                    <span v-if="i === 1" data-hero-summit class="relative z-10 block pb-[0.04em]" aria-hidden="true">
                        <span class="t-outline block">
                            <span v-for="(g, j) in graphemes(line)" :key="j" class="hero-letter-mask inline-block"><span data-hero-letter class="inline-block">{{ g }}</span></span>
                        </span>
                    </span>
                    <span v-else class="block overflow-hidden pb-[0.04em]" aria-hidden="true">
                        <span data-hero-line class="block">{{ line }}</span>
                    </span>
                </template>
            </h1>
        </div>

        <!-- Intro layers: hidden until the timeline shows them, so no-JS / reduced motion just sees the hero -->
        <div class="pointer-events-none absolute inset-0 z-10" aria-hidden="true">
            <div v-for="(l, i) in [...layers].reverse()" :key="i" data-hero-layer class="hero-layer absolute inset-0 overflow-hidden">
                <picture>
                    <source media="(min-width: 768px)" srcset="/media/hero-event.webp" />
                    <img src="/media/hero-event-m.webp" alt="" width="2000" height="1333" decoding="async" class="h-full w-full object-cover" :style="{ objectPosition: l.pos, transform: `scale(${l.zoom})`, transformOrigin: l.pos }" />
                </picture>
            </div>
        </div>
    </section>
</template>

<style scoped>
.hero-h1 {
    font-size: clamp(3.75rem, 8vw, 9rem);
    --outline: var(--bacco-light);
}
/* Clip only the bottom edge, so letters rise out of the baseline without trimming the outline stroke */
.hero-letter-mask {
    clip-path: inset(-25% -10% 0 -10%);
}
.hero-layer {
    visibility: hidden;
    transform-origin: 50% 50%;
    will-change: transform;
}
/* Even shade so the centred headline reads over any part of the photo */
.hero-scrim {
    z-index: 3;
    background:
        radial-gradient(60% 55% at 50% 52%, rgb(5 14 10 / 0.55), rgb(5 14 10 / 0.25) 70%, transparent),
        linear-gradient(to top, rgb(5 14 10 / 0.6), rgb(5 14 10 / 0) 35%);
}
.hero-photo img {
    transform-origin: 70% 40%;
    animation: hero-drift 28s ease-in-out infinite alternate;
}
.hero-photo::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(105deg, transparent 38%, rgb(120 180 255 / 0.28) 50%, transparent 62%) 0 0 / 250% 100%;
    z-index: 2;
    mix-blend-mode: screen;
    animation: hero-sweep 9s ease-in-out infinite;
}
@keyframes hero-drift {
    from {
        transform: scale(1.04);
    }
    to {
        transform: scale(1.14) translate3d(-2%, -1.5%, 0);
    }
}
@keyframes hero-sweep {
    from {
        background-position: 120% 0;
    }
    to {
        background-position: -20% 0;
    }
}
@media (prefers-reduced-motion: reduce) {
    .hero-photo img,
    .hero-photo::after {
        animation: none;
    }
}
:global(html[lang='bn']) .hero-h1 {
    font-size: clamp(2.75rem, 7vw, 7rem);
}
</style>
