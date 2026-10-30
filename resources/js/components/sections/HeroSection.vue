<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Countdown from '@/components/ui/Countdown.vue';
import Icon from '@/components/ui/Icon.vue';
import { ScrollTrigger, gsap, introReady, useGsap } from '@/composables/motion';
import { useShared } from '@/composables/useShared';
import type { Hero } from '@/types/content';

// Section 1, in four stages:
// 01 cinematic opening: three full-bleed crops of the photo shrink away (Lunia-style), headline still masked;
// 02 FREELANCER → SUMMIT → BANGLADESH rise out of their masks one after another;
// 03 once it settles: tagline and countdown fade up, date & Dhaka fade in bottom-left, Register slides up bottom-right, scroll cue;
// 04 on scroll the headline scales down slightly and the photo's drift winds down as the trust strip comes in.
// Nominate stays in the header.
defineProps<{ hero: Hero }>();
const { tm, rt, t, locale } = useI18n();
const { summit, site } = useShared();
const root = ref<HTMLElement>();
const lines = computed(() => (tm('brand.titleLines') as unknown[]).map((l) => rt(l as never)));
// "SUMMIT" animates per letter; split by grapheme so Bangla vowel signs stay attached to their consonant.
const graphemes = (s: string) => (typeof Intl.Segmenter === 'function' ? [...new Intl.Segmenter(undefined, { granularity: 'grapheme' }).segment(s)].map((g) => g.segment) : Array.from(s));

const register = computed(() => site.value.ctas.primary.find((c) => c.id === 'register')!);
const date = computed(() =>
    summit.value.dateConfirmed
        ? new Intl.DateTimeFormat(locale.value === 'bn' ? 'bn-BD' : 'en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Dhaka' }).format(new Date(summit.value.startsAt))
        : t('header.lateNov', { edition: summit.value.edition }),
);
const place = computed(() => (summit.value.venue ? `${summit.value.venue}, ${summit.value.city}` : t('header.city')));

// Top layer first. Each is a different close crop of the same photo (focus point + zoom).
const layers = [
    { pos: '50% 50%', zoom: 1.8 },
    { pos: '18% 62%', zoom: 1.7 },
    { pos: '85% 55%', zoom: 1.7 },
];

useGsap(root, (reduced) => {
    if (reduced) return;
    // Stack: the hero holds still for its own height while the next page slides up over it.
    ScrollTrigger.create({ trigger: root.value, start: 'top top', end: 'bottom top', pin: true, pinSpacing: false });
    const tl = gsap
        .timeline({ paused: true })
        // Stage 01. Same curve and overlap as the reference: 1.9s expo.inOut per layer, next one 0.5s later.
        .set('[data-hero-layer]', { visibility: 'visible' })
        .to('[data-hero-layer]', { scale: 0, duration: 1.9, ease: 'expo.inOut', stagger: { each: 0.5, from: 'end' }, force3D: true }, 0)
        .set('[data-hero-layer]', { display: 'none' })
        // Stage 02. Each line rises out of its own mask, in reading order; SUMMIT letter by letter inside its turn.
        .from('[data-hero-line="0"]', { yPercent: 110, duration: 1.1, ease: 'expo.out' }, 0.5)
        .from('[data-hero-letter]', { yPercent: 110, duration: 1, ease: 'expo.out', stagger: 0.06 }, 0.85)
        .from('[data-hero-line="2"]', { yPercent: 110, duration: 1.1, ease: 'expo.out' }, 1.45)
        .fromTo(
            '#hero-title',
            { filter: 'drop-shadow(0 0 0px rgb(141 176 230 / 0))' },
            { filter: 'drop-shadow(0 0 24px rgb(141 176 230 / 0.55))', duration: 0.7, ease: 'sine.inOut', yoyo: true, repeat: 1, clearProps: 'filter' },
            2.3,
        )
        // Stage 03. Supporting content once the headline has landed.
        .from('[data-hero-tagline]', { autoAlpha: 0, y: 18, duration: 0.9, ease: 'expo.out' }, 2.3)
        .from('[data-hero-countdown]', { autoAlpha: 0, y: 18, duration: 0.9, ease: 'expo.out' }, 2.4)
        .from('[data-hero-meta]', { autoAlpha: 0, duration: 1, ease: 'sine.out' }, 2.5)
        .from('[data-hero-cta]', { autoAlpha: 0, yPercent: 60, duration: 0.9, ease: 'expo.out' }, 2.6)
        .from('[data-hero-scroll]', { autoAlpha: 0, duration: 0.8, ease: 'sine.out' }, 2.9);

    // Start once the photo is decoded (so the first frames don't stutter) and any preloader is opening.
    const img = root.value?.querySelector<HTMLImageElement>('[data-hero-layer] img');
    Promise.all([Promise.race([img?.decode() ?? Promise.resolve(), new Promise((r) => setTimeout(r, 800))]).catch(() => {}), introReady]).then(() => tl.play());

    // Stage 04. Scrubbed across the hero's own height: headline eases back, the bottom row clears first,
    // the photo trails behind the scroll and its CSS drift/sweep slow to a stop.
    const photo = root.value?.querySelector<HTMLElement>('[data-hero-photo]');
    const drift = photo?.getAnimations?.({ subtree: true }) ?? [];
    gsap.timeline({
        defaults: { ease: 'none' },
        scrollTrigger: {
            trigger: root.value,
            start: 'top top',
            end: 'bottom top',
            scrub: true,
            onUpdate: ({ progress }) => drift.forEach((a) => (a.playbackRate = Math.max(0, 1 - progress * 1.6))),
        },
    })
        .to('[data-hero-headline]', { scale: 0.86, yPercent: -6 }, 0)
        .to('[data-hero-foot]', { autoAlpha: 0, y: -24, duration: 0.35 }, 0)
        .to('[data-hero-photo]', { yPercent: 18 }, 0)
        // Section reveal: the hero recedes into shadow as the next page covers it.
        .to('[data-hero-dim]', { opacity: 0.6 }, 0);
});
</script>

<template>
    <section
        id="hero"
        ref="root"
        data-section="1"
        aria-labelledby="hero-title"
        class="grain relative isolate flex min-h-[100svh] flex-col overflow-hidden bg-night text-paper pt-24 md:pt-[136px] xl:pt-[164px]"
    >
        <!-- Event photo (Unsplash License, photo 6vAjp0pscX0 by Alexandre Pellaes): slow drift + light sweep; swap for summit photography later -->
        <div data-hero-photo class="hero-photo absolute inset-0 -z-20 overflow-hidden bg-night" aria-hidden="true">
            <picture>
                <source media="(min-width: 768px)" srcset="/media/hero-event.webp" />
                <img src="/media/hero-event-m.webp" alt="" width="2000" height="1333" fetchpriority="high" decoding="async" class="h-full w-full object-cover" />
            </picture>
            <div class="hero-scrim absolute inset-0" />
        </div>

        <!-- Headline sits above the intro layers (as in the reference), so its masks are in place from the first frame -->
        <div class="container-site relative z-20 flex flex-1 items-center justify-center pb-10 md:pb-16">
            <div data-hero-headline class="flex flex-col items-center text-center">
                <h1 id="hero-title" class="t-h1 hero-h1" :aria-label="lines.join(' ')">
                    <template v-for="(line, i) in lines" :key="i">
                        <span v-if="i === 1" class="relative z-10 block pb-[0.04em]" aria-hidden="true">
                            <span class="t-outline block">
                                <span v-for="(g, j) in graphemes(line)" :key="j" class="hero-letter-mask inline-block"><span data-hero-letter class="inline-block">{{ g }}</span></span>
                            </span>
                        </span>
                        <span v-else class="block overflow-hidden pb-[0.04em]" aria-hidden="true">
                            <span :data-hero-line="i" class="block">{{ line }}</span>
                        </span>
                    </template>
                </h1>
                <p data-hero-tagline class="mt-6 max-w-xl text-base font-medium text-paper/80 md:mt-8 md:text-xl">{{ summit.tagline }}</p>
            </div>
        </div>

        <!-- Bottom row: date & city left, countdown centre (scroll cue under it), Register right (mobile has the sticky CTA bar, so it keeps clear of it) -->
        <div data-hero-foot class="container-site relative z-20 grid grid-cols-1 items-end gap-6 pb-24 md:grid-cols-2 md:pb-10 lg:grid-cols-3">
            <div data-hero-meta class="flex flex-col gap-2 md:mb-12">
                <span class="t-label flex items-center gap-2 text-paper"><Icon name="calendar" :size="14" class="text-gold" />{{ date }}</span>
                <span class="t-label flex items-center gap-2 text-paper/80">{{ place }}</span>
                <span v-if="!summit.dateConfirmed" class="text-xs text-muted">{{ t('header.tbc') }}</span>
            </div>
            <Countdown data-hero-countdown :target="summit.startsAt" class="w-full max-w-md justify-self-center md:col-span-2 md:row-start-2 lg:col-span-1 lg:col-start-2 lg:row-start-1" />
            <div class="hidden justify-self-end md:mb-12 md:block">
                <CtaLink data-hero-cta :href="register.href" class="!px-6 !py-4 !text-base">{{ t('cta.register') }}</CtaLink>
            </div>
            <div data-hero-scroll class="absolute bottom-0 left-1/2 hidden -translate-x-1/2 md:block" aria-hidden="true">
                <span class="hero-scroll-track relative block h-8 w-px overflow-hidden bg-paper/20"><span class="hero-scroll-dot absolute inset-x-0 top-0 h-4 bg-gold" /></span>
            </div>
        </div>

        <!-- Darkens on scroll (stage 04) as the next page slides over -->
        <div data-hero-dim class="pointer-events-none absolute inset-0 z-30 bg-night opacity-0" aria-hidden="true" />

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
    /* Also capped by viewport height (minus header, tagline and bottom row), so the hero fits one screen on short laptops */
    font-size: clamp(3rem, min(max(8vw, 3.75rem), calc((100svh - 28rem) / 2.8)), 9rem);
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
[data-hero-headline] {
    transform-origin: 50% 40%;
}
/* Even shade so the centred headline reads over any part of the photo, deeper at the foot for the bottom row */
.hero-scrim {
    z-index: 3;
    background:
        radial-gradient(60% 55% at 50% 52%, rgb(5 14 10 / 0.55), rgb(5 14 10 / 0.25) 70%, transparent),
        linear-gradient(to top, rgb(5 14 10 / 0.75), rgb(5 14 10 / 0) 40%);
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
.hero-scroll-dot {
    animation: hero-scroll 2.2s cubic-bezier(0.65, 0, 0.35, 1) infinite;
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
@keyframes hero-scroll {
    from {
        transform: translateY(-100%);
    }
    to {
        transform: translateY(300%);
    }
}
@media (prefers-reduced-motion: reduce) {
    .hero-photo img,
    .hero-photo::after,
    .hero-scroll-dot {
        animation: none;
    }
}
:global(html[lang='bn']) .hero-h1 {
    font-size: clamp(2.5rem, min(max(7vw, 2.75rem), calc((100svh - 28rem) / 3.5)), 7rem);
}
</style>
