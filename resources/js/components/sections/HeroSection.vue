<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Countdown from '@/components/ui/Countdown.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import EditionStamp from '@/components/ui/EditionStamp.vue';
import Marquee from '@/components/ui/Marquee.vue';
import MessageSlider from '@/components/ui/MessageSlider.vue';
import { EASE, STAGGER, gsap, useGsap } from '@/composables/motion';
import { useMagnetic } from '@/composables/useMagnetic';
import { useShared } from '@/composables/useShared';
import { useStageVisibility } from '@/composables/useStageVisibility';
import { stageMode } from '@/stage/state';
import type { Hero } from '@/types/content';

// Section 1. Canvas (DeltaStage) shows through on desktop; video + poster fallback otherwise (GITEX video hero, G4).
defineProps<{ hero: Hero }>();
const { summit, site } = useShared();
const { t, tm, rt } = useI18n();
const root = ref<HTMLElement>();
const primary = ref<HTMLElement>();
const lines = computed(() => (tm('brand.titleLines') as unknown[]).map((l) => rt(l as never)));

useStageVisibility(root, 'hero');
useMagnetic(primary);
useGsap(root, (reduced) => {
    if (reduced) return;
    gsap.from('[data-hero-line]', { yPercent: 105, duration: 1.1, ease: EASE, stagger: STAGGER * 2, delay: 0.15 });
    gsap.from('[data-hero-fade]', { opacity: 0, y: 24, duration: 0.9, ease: EASE, stagger: STAGGER, delay: 0.45 });
});
</script>

<template>
    <section id="hero" ref="root" data-section="1" aria-labelledby="hero-title" class="grain relative flex min-h-[100svh] flex-col overflow-hidden pt-24 md:pt-[136px]">
        <!-- Fallback media: poster always (no layout shift), video only when the WebGL stage is off -->
        <div class="hero-fallback absolute inset-0 -z-20 transition-opacity duration-700" :class="stageMode === 'on' && 'opacity-0'" aria-hidden="true">
            <div class="absolute inset-0 bg-cover bg-center" :style="{ backgroundImage: `url(${hero.video.poster})` }" />
            <video v-if="stageMode === 'off'" class="absolute inset-0 h-full w-full object-cover" :poster="hero.video.poster" autoplay muted loop playsinline preload="auto">
                <source :src="hero.video.src" type="video/webm" />
            </video>
        </div>
        <div class="absolute inset-0 -z-10 bg-gradient-to-r from-night via-night/80 to-transparent md:via-night/55" aria-hidden="true" />

        <div class="container-site relative flex flex-1 flex-col">
            <p class="t-label text-mint" data-hero-fade>{{ hero.eyebrow }}</p>

            <div class="mt-4 flex items-start justify-between gap-6">
                <h1 id="hero-title" class="t-h1 hero-h1" :aria-label="`${lines.join(' ')} ${summit.edition}`">
                    <span v-for="(line, i) in lines" :key="i" class="line-mask block" aria-hidden="true">
                        <span data-hero-line class="line-inner" :class="i === 1 && 't-outline'">{{ line }}</span>
                    </span>
                </h1>
                <EditionStamp :edition="summit.edition" class="hidden shrink-0 md:block" data-hero-fade />
            </div>

            <div class="mt-6 grid gap-8 pb-8 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-7">
                    <p class="font-display text-2xl font-black text-gold uppercase md:text-3xl" data-hero-fade>
                        {{ summit.tagline }}
                        <span v-if="!summit.taglineFinal" class="t-label ml-2 align-middle text-muted normal-case">({{ t('common.provisional') }})</span>
                    </p>
                    <div class="mt-5 flex flex-wrap gap-2" data-hero-fade>
                        <span ref="primary" class="inline-flex"><CtaLink :href="site.ctas.primary[0].href">{{ t('cta.register') }}</CtaLink></span>
                        <CtaLink v-for="c in site.ctas.primary.slice(1)" :key="c.id" :href="c.href" :tone="c.tone">{{ c.label }}</CtaLink>
                    </div>
                    <Countdown :target="summit.startsAt" class="mt-6 max-w-xl" data-hero-fade />
                </div>
                <div class="lg:col-span-5" data-hero-fade>
                    <MessageSlider :messages="hero.messages" />
                </div>
            </div>
        </div>

        <div class="relative border-y border-line-dark bg-night/70 py-3 font-display text-xl font-black tracking-wide uppercase">
            <Marquee :items="hero.marquee" :duration="36" separator="✦" />
        </div>
    </section>
</template>

<style scoped>
.hero-h1 {
    font-size: clamp(3.75rem, 9vw, 9.5rem);
    --outline: var(--paper);
}
:global(html[lang='bn']) .hero-h1 {
    font-size: clamp(2.75rem, 7vw, 7rem);
}
</style>
