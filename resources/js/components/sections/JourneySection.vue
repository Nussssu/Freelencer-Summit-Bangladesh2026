<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { ScrollTrigger, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useStageVisibility } from '@/composables/useStageVisibility';
import { toBanglaDigits } from '@/i18n';
import { stage, stageMode } from '@/stage/state';
import type { Journey } from '@/types/content';
import JourneyCounters from './journey/JourneyCounters.vue';
import JourneyDirectory from './journey/JourneyDirectory.vue';
import JourneyMap from './journey/JourneyMap.vue';

// Section 4, the signature section. Desktop: pinned ~4.5 screens, scrubbed, the shared canvas morphs flow → map.
// Mobile / reduced motion / SSR: a vertical stacked timeline with division cards (all content in the DOM).
defineProps<{ journey: Journey }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const pin = ref<HTMLElement>();
const active = ref(0);
const pinned = ref(false);

useStageVisibility(pin, 'journey');
useReveal(root);

const stepIndex = (s: number) => (s < 1.5 ? 0 : s < 2.5 ? 1 : s < 3.4 ? 2 : 3);

useGsap(root, () => {
    const mm = gsap.matchMedia();
    mm.add('(min-width: 768px) and (prefers-reduced-motion: no-preference)', () => {
        pinned.value = true;
        // Map morph begins while the section scrolls in…
        ScrollTrigger.create({
            trigger: pin.value,
            start: 'top bottom',
            end: 'top top',
            onUpdate: (st) => (stage.step = st.progress * 0.5),
        });
        // …then the pinned, scrubbed journey: step 0.5 → 4 over 3.5 extra screen-heights.
        ScrollTrigger.create({
            trigger: pin.value,
            start: 'top top',
            end: '+=350%',
            pin: true,
            scrub: 1,
            onUpdate: (st) => {
                stage.step = 0.5 + st.progress * 3.5;
                const i = stepIndex(stage.step);
                if (i !== active.value) active.value = i; // reactive only on the 4 step changes
                pin.value?.style.setProperty('--progress', st.progress.toFixed(3));
            },
        });
        return () => {
            pinned.value = false;
            stage.step = 0;
        };
    });
});
</script>

<template>
    <section id="journey" ref="root" data-section="4" aria-labelledby="journey-title" class="relative">
        <div ref="pin" class="journey-pin light-tokens relative" :class="pinned && 'is-pinned'">
            <div class="container-site grid grid-cols-1 gap-10 py-20 md:min-h-[100svh] md:grid-cols-12 md:items-center md:py-0 md:pt-[112px] xl:pt-[148px]">
                <div class="min-w-0 md:col-span-6 lg:col-span-5">
                    <p class="t-label text-mint">{{ t('sections.journey') }}</p>
                    <SplitHeading id="journey-title" :text="journey.headline" :emphasis="journey.headlineEmphasis" emphasis-class="text-gold" class="t-h2 mt-3 journey-title" />
                    <p class="mt-4 max-w-md text-ink-2">{{ t('sections.journeyLead') }}</p>

                    <!-- Stitch progress line with four ticks -->
                    <div class="journey-progress relative mt-8 hidden h-6 md:block" aria-hidden="true">
                        <div class="stitch absolute inset-x-0 top-2 opacity-30" />
                        <div class="stitch journey-progress-fill absolute inset-x-0 top-2" />
                        <span v-for="i in 4" :key="i" class="absolute top-0 size-3 -translate-x-1/2 border border-gold-ink bg-paper transition-colors duration-300" :class="active >= i - 1 && '!bg-gold'" :style="{ left: `${((i - 1) / 3) * 100}%` }" />
                    </div>

                    <ol class="journey-steps mt-10 space-y-12 md:mt-8">
                        <li v-for="(s, i) in journey.steps" :key="s.tag" class="journey-step" :class="{ 'is-active': active === i }" :aria-current="pinned && active === i ? 'step' : undefined">
                            <div class="flex items-end gap-4">
                                <span class="font-display text-[clamp(4rem,8vw,7rem)] leading-[0.8] font-black t-outline" style="--outline: var(--gold-ink)">{{ s.count }}</span>
                                <span class="font-bangla text-[clamp(2rem,4vw,3.5rem)] leading-none font-bold text-gold" lang="bn">{{ toBanglaDigits(s.count) }}</span>
                            </div>
                            <p class="t-label mt-4 inline-block border border-mint/40 px-2 py-1 text-mint">{{ s.tag }}</p>
                            <h3 class="mt-3 font-display text-3xl font-black uppercase">{{ s.title }}</h3>
                            <p class="mt-2 max-w-md text-ink-2">{{ s.text }}</p>
                            <Link :href="s.href" class="link-u t-label mt-3 inline-flex min-h-11 items-center text-gold">{{ s.tag }} →</Link>

                            <!-- Division cards: stacked layout only -->
                            <ul v-if="i === 2" class="journey-cards mt-6 grid grid-cols-2 gap-2">
                                <li v-for="d in journey.divisions" :key="d.name" class="border border-line-dark p-3">
                                    <p class="font-display text-xl font-black uppercase">{{ d.name }}</p>
                                    <p class="t-label mt-1 text-muted">{{ t('sections.cols.date') }}: {{ t('common.tba') }}</p>
                                </li>
                            </ul>
                        </li>
                    </ol>
                </div>

                <!-- Static map: always on mobile, on desktop only when WebGL is unavailable -->
                <div class="journey-map md:col-span-6 md:col-start-7 lg:col-span-6 lg:col-start-7" :class="stageMode === 'on' && 'md:invisible'">
                    <JourneyMap :step="pinned ? active : 3" :beacon-label="journey.beaconLabel" class="mx-auto max-w-md md:max-w-none" />
                </div>
            </div>
        </div>

        <div class="on-light relative bg-mist">
            <JourneyCounters :counters="journey.counters" />
            <JourneyDirectory :directory="journey.directory" :steps="journey.steps" />
        </div>
    </section>
</template>

<style scoped>
.journey-title {
    font-size: clamp(2.25rem, 3.4vw, 3.5rem);
}
/* Pinned desktop mode: one step at a time, stacked in place. */
.is-pinned .journey-steps {
    position: relative;
    display: grid;
}
.is-pinned .journey-step {
    grid-area: 1 / 1;
    margin: 0;
    opacity: 0;
    transform: translateY(24px);
    transition:
        opacity 0.5s var(--ease-out),
        transform 0.9s var(--ease-out);
    pointer-events: none;
}
.is-pinned .journey-step.is-active {
    opacity: 1;
    transform: none;
    pointer-events: auto;
}
.is-pinned .journey-cards {
    display: none;
}
.journey-progress-fill {
    clip-path: inset(0 calc((1 - var(--progress, 0)) * 100%) 0 0);
}
@media (max-width: 767px) {
    .journey-map {
        order: -1;
    }
}
</style>
