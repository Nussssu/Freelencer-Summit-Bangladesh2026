<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Countdown from '@/components/ui/Countdown.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import EditionStamp from '@/components/ui/EditionStamp.vue';
import Marquee from '@/components/ui/Marquee.vue';
import MessageSlider from '@/components/ui/MessageSlider.vue';
import { EASE, STAGGER, gsap, reducedMotion, useGsap } from '@/composables/motion';
import { useMagnetic } from '@/composables/useMagnetic';
import { useShared } from '@/composables/useShared';
import { useStageVisibility } from '@/composables/useStageVisibility';
import { stageMode } from '@/stage/state';
import type { Hero } from '@/types/content';

// Section 1. Canvas (DeltaStage) shows through on desktop; video + poster fallback otherwise (GITEX video hero, G4).
const props = defineProps<{ hero: Hero }>();
const { summit, site } = useShared();
const { t, tm, rt } = useI18n();
const root = ref<HTMLElement>();
const primary = ref<HTMLElement>();
const lines = computed(() => (tm('brand.titleLines') as unknown[]).map((l) => rt(l as never)));

// Client-only choice of clip; reduced motion keeps the still poster.
const mobile = ref(false);
const videoSrc = computed(() => (stageMode.value === 'off' && !reducedMotion() ? (mobile.value ? props.hero.video.srcMobile : props.hero.video.src) : null));
onMounted(() => (mobile.value = innerWidth < 768));

useStageVisibility(root, 'hero');
useMagnetic(primary);
useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.from('[data-hero-line]', { yPercent: 105, duration: 1.1, ease: EASE, stagger: STAGGER * 2, delay: 0.15 });
    gsap.from('[data-hero-fade]', { opacity: 0, y: 24, duration: 0.9, ease: EASE, stagger: STAGGER, delay: 0.45 });
});
</script>

<template>
    <section id="hero" ref="root" data-section="1" aria-labelledby="hero-title" class="grain relative flex min-h-[100svh] flex-col overflow-hidden text-paper pt-24 md:pt-[136px] xl:pt-[164px]">
        <!-- Fallback media: poster always (no layout shift), video only when the WebGL stage is off -->
        <div class="hero-fallback stage-ink absolute inset-0 -z-20 transition-opacity duration-700" :class="stageMode === 'on' && 'opacity-0'" aria-hidden="true">
            <div class="absolute inset-0 bg-cover bg-center bg-[image:var(--poster-m)] md:bg-[image:var(--poster)]" :style="{ '--poster': `url(${hero.video.poster})`, '--poster-m': `url(${hero.video.posterMobile})` }" />
            <video v-if="videoSrc" class="absolute inset-0 h-full w-full object-cover" :poster="mobile ? hero.video.posterMobile : hero.video.poster" :src="videoSrc" autoplay muted loop playsinline />
        </div>
        <!-- Event photo (Unsplash License, photo 6vAjp0pscX0 by Alexandre Pellaes): slow drift + light sweep; swap for summit photography later -->
        <div class="hero-photo absolute inset-0 -z-20 overflow-hidden" aria-hidden="true">
            <picture>
                <source media="(min-width: 768px)" srcset="/media/hero-event.webp" />
                <img src="/media/hero-event-m.webp" alt="" width="2000" height="1333" fetchpriority="high" decoding="async" class="h-full w-full object-cover" />
            </picture>
        </div>
        <!-- Edge shade only (GITEX G4): the photo stays clear, the headline side and ticker edge get contrast -->
        <div class="hero-scrim pointer-events-none absolute inset-0 -z-10" aria-hidden="true" />

        <div class="container-site relative flex flex-1 flex-col">
            <p class="t-label text-bacco-light" data-hero-fade>{{ hero.eyebrow }}</p>

            <div class="mt-4 flex items-start justify-between gap-6">
                <h1 id="hero-title" class="t-h1 hero-h1" :aria-label="`${lines.join(' ')} ${summit.edition}`">
                    <span v-for="(line, i) in lines" :key="i" class="block overflow-hidden pb-[0.04em]" aria-hidden="true">
                        <span data-hero-line class="block" :class="i === 1 && 't-outline'">{{ line }}</span>
                    </span>
                </h1>
                <EditionStamp :edition="summit.edition" class="hidden shrink-0 text-bacco-light md:block" data-hero-fade />
            </div>

            <div class="mt-6 grid grid-cols-1 gap-8 pb-8 lg:grid-cols-12 lg:items-end">
                <div class="min-w-0 lg:col-span-7">
                    <p class="font-display text-2xl font-black text-red uppercase md:text-3xl" data-hero-fade>
                        {{ summit.tagline }}
                        <span v-if="!summit.taglineFinal" class="t-label ml-2 align-middle text-muted normal-case">({{ t('common.provisional') }})</span>
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2" data-hero-fade>
                        <span ref="primary" class="inline-flex"><CtaLink :href="site.ctas.primary[0].href">{{ t('cta.register') }}</CtaLink></span>
                        <CtaLink v-for="c in site.ctas.primary.slice(1)" :key="c.id" :href="c.href" :tone="c.tone">{{ c.label }}</CtaLink>
                    </div>
                    <Countdown :target="summit.startsAt" class="mt-6 max-w-xl" data-hero-fade />
                </div>
                <div class="min-w-0 lg:col-span-5" data-hero-fade>
                    <MessageSlider :messages="hero.messages" />
                </div>
            </div>
        </div>

        <!-- Moving ticker pinned to the hero edge (GITEX G5): bold label + items split by gold bars -->
        <div class="hero-ticker relative flex items-center border-y border-bacco bg-bacco">
            <p class="shrink-0 py-3 pr-6 pl-4 font-display text-xl font-black tracking-wide uppercase md:pl-8">{{ t('sections.tickerLabel') }}</p>
            <Marquee :items="hero.marquee" :duration="36" separator="|" class="min-w-0 flex-1 py-3 text-base font-medium" />
        </div>
    </section>
</template>

<style scoped>
.hero-h1 {
    font-size: clamp(3.75rem, 8vw, 9rem);
    --outline: var(--bacco-light);
}
.hero-scrim {
    background:
        linear-gradient(to right, rgb(5 14 10 / 0.7), rgb(5 14 10 / 0.35) 50%, rgb(5 14 10 / 0) 80%),
        linear-gradient(to top, rgb(5 14 10 / 0.55), rgb(5 14 10 / 0) 35%);
}
@media (max-width: 1023px) {
    .hero-scrim {
        background: rgb(5 14 10 / 0.6);
    }
}
.hero-ticker :deep(.marquee > span > span:last-child) {
    color: var(--gold);
    opacity: 1;
    font-weight: 700;
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
:global(html[lang='bn']) .hero-h1 {
    font-size: clamp(2.75rem, 7vw, 7rem);
}
</style>
