<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { gsap } from 'gsap';
import { computed, onBeforeUnmount, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Awards } from '@/types/content';

// Section 9. Own identity (deep green + gold), clearly separate from summit registration (brief §9).
defineProps<{ awards: Awards }>();
const { t } = useI18n();
const { summit, site } = useShared();
const nominate = computed(() => site.value.ctas.primary.find((c) => c.id === 'nominate')!);
const root = ref<HTMLElement>();
const zone = ref<HTMLElement>();
const ring = ref<HTMLElement>();
const fan = ref<HTMLElement>();
useReveal(root);

// Hover: the deck straightens into a vertical list and the ring grows just enough to clear its corners.
const LIST_GAP = 8;
const RING_CLEARANCE = 20;
let timeline: ReturnType<typeof gsap.timeline> | null = null;

function animate(open: boolean) {
    if (!ring.value || !fan.value) return;
    if (window.matchMedia('(max-width: 767px), (prefers-reduced-motion: reduce)').matches) return;

    const cards = fan.value.querySelectorAll('li');
    const cardHeight = cards[0]?.offsetHeight ?? 0;
    const step = cardHeight + LIST_GAP;
    const halfHeight = ((cards.length - 1) / 2) * step + cardHeight / 2;
    const corner = Math.hypot(fan.value.offsetWidth / 2, halfHeight);
    // Text baseline sits on r=82 of the 200 viewBox; CSS width ignores the spin transform.
    const svg = ring.value.querySelector('svg')!;
    const ringInner = (parseFloat(getComputedStyle(svg).width) / 2) * (82 / 100);
    const ringScale = Math.max(1, (corner + RING_CLEARANCE) / ringInner);

    timeline?.kill();
    timeline = gsap.timeline({ defaults: { duration: 0.7, ease: 'power3.out' } });
    timeline.to(ring.value, { scale: open ? ringScale : 1 }, 0);
    cards.forEach((card, index) => {
        const i = index - (cards.length - 1) / 2;
        timeline!.to(card, { x: 0, y: open ? i * step : i * 8, rotation: 0 }, 0);
    });
}

function openStage() {
    animate(true);
}

function closeStage(event?: FocusEvent | PointerEvent) {
    const next = (event as FocusEvent | undefined)?.relatedTarget;
    if (next && zone.value?.contains(next as Node)) return;
    if (event?.type === 'pointerleave' && zone.value?.contains(document.activeElement)) return;
    animate(false);
}

onBeforeUnmount(() => timeline?.kill());
</script>

<template>
    <section ref="root" data-section="9" aria-labelledby="aw-title" class="on-light light-tokens flow section relative z-[1] overflow-hidden">
        <div class="container-site grid grid-cols-1 gap-12 lg:grid-cols-12">
            <div class="min-w-0 lg:col-span-7">
                <p class="t-label text-gold">{{ t('sections.awards') }} {{ summit.edition }}</p>
                <SplitHeading id="aw-title" :text="awards.headline" :emphasis="awards.emphasis" emphasis-class="text-gold" class="t-h2-xl mt-4" />
                <p class="mt-8 max-w-xl text-lg">{{ awards.eligibility }}</p>
                <p class="t-label mt-4">{{ t('sections.deadline') }}: <span class="text-gold">{{ awards.deadline ?? t('common.tba') }}</span></p>

                <!-- Application status as a stitched line (brief §9 statuses) -->
                <div class="mt-10">
                    <h3 class="t-label text-ink-2">{{ t('sections.status') }}</h3>
                    <ol class="relative mt-4 grid grid-cols-4">
                        <span class="stitch absolute inset-x-[12.5%] top-[7px]" data-reveal="stitch" aria-hidden="true" />
                        <li v-for="(st, i) in awards.statuses" :key="st" class="relative flex flex-col items-center gap-3 text-center">
                            <span class="size-4 border-2 border-gold" :class="i === awards.statuses.length - 1 ? 'bg-gold' : 'bg-snow'" aria-hidden="true" />
                            <span class="t-label text-[0.6875rem]">{{ st }}</span>
                        </li>
                    </ol>
                </div>

                <div class="mt-10 flex flex-wrap gap-2">
                    <CtaLink :href="nominate.href">{{ nominate.label }}</CtaLink>
                    <CtaLink href="/awards/categories" tone="outline">{{ t('sections.categories') }}</CtaLink>
                </div>
            </div>

            <div
                ref="zone"
                class="relative flex min-h-96 flex-col items-center justify-center md:min-h-[30rem] lg:col-span-5"
                @pointerenter="openStage"
                @pointerleave="closeStage"
                @focusin="openStage"
                @focusout="closeStage"
            >
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <div ref="ring" class="pointer-events-none absolute inset-0 flex items-center justify-center">
                        <svg viewBox="0 0 200 200" class="spin-slow size-[19rem] opacity-80 md:size-80" aria-hidden="true">
                            <defs><path id="aw-ring" d="M100 100m-82 0a82 82 0 1 1 164 0a82 82 0 1 1-164 0" /></defs>
                            <text font-size="12" fill="var(--gold-ink)" class="font-mono"><textPath href="#aw-ring" textLength="515" lengthAdjust="spacing">{{ awards.badge.toUpperCase() }}</textPath></text>
                        </svg>
                    </div>
                    <ul ref="fan" class="fan relative z-20 w-60" :aria-label="t('sections.categories')">
                        <li v-for="(c, i) in awards.categories" :key="c" :style="{ '--i': i - (awards.categories.length - 1) / 2 }">
                            <Link href="/awards/categories" class="block border border-night/30 bg-paper px-2 py-3 font-display text-lg leading-tight font-black whitespace-nowrap text-night uppercase">{{ c }}</Link>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.fan {
    display: grid;
}
/* Rest deck; GSAP drives the hover list and ring. */
.fan li {
    grid-area: 1 / 1;
    transform: translateY(calc(var(--i) * 8px));
}
@media (max-width: 767px), (prefers-reduced-motion: reduce) {
    .fan {
        gap: 8px;
    }
    .fan li {
        grid-area: auto;
        transform: none;
    }
}
</style>
