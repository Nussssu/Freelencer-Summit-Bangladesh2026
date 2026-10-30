<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import CtaLink from '@/components/ui/CtaLink.vue';
import Dialog from '@/components/ui/Dialog.vue';
import Icon from '@/components/ui/Icon.vue';
import { ScrollTrigger, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { About } from '@/types/content';

// Section 3. Full-bleed conference photo under a dark left-hand fade: eyebrow, three-line headline with the summit
// name in gold, intro, three icon highlights and the two CTAs. One screen tall on desktop.
const props = defineProps<{ about: About }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const watching = ref(false);
useReveal(root);

// "What is" / "Freelancer Summit" / "Bangladesh 2026?": the emphasis gets its own gold line.
const lines = computed(() => {
    const { headline, emphasis } = props.about;
    const i = headline.indexOf(emphasis);
    if (i < 0) return [{ text: headline, em: false }];
    return [
        { text: headline.slice(0, i).trim(), em: false },
        { text: emphasis, em: true },
        { text: headline.slice(i + emphasis.length).trim(), em: false },
    ].filter((l) => l.text);
});

useGsap(root, (reduced) => {
    if (reduced) return;
    // Cinematic entrance: the photo settles from a slight push-in while the section scrolls in, locked to the scroll
    // (no catch-up smoothing) and finished before the hold, so it stays still while held.
    gsap.fromTo('[data-whatis-img]', { scale: 1.12 }, { scale: 1, ease: 'none', force3D: true, scrollTrigger: { trigger: root.value, start: 'top bottom', end: 'top top', scrub: true } });
    // Desktop (where it fits one screen): hold the full section in view for a moment before it scrolls on normally.
    gsap.matchMedia().add('(min-width: 1024px)', () => {
        ScrollTrigger.create({ trigger: root.value, start: 'top top', end: '+=70%', pin: true });
    });
});
</script>

<template>
    <section ref="root" data-section="3" aria-labelledby="whatis-title" class="whatis on-light section relative z-[1] overflow-hidden bg-snow text-night">
        <div data-whatis-bg class="whatis-bg absolute inset-0 -z-10" aria-hidden="true">
            <img data-whatis-img :src="about.image" alt="" loading="lazy" decoding="async" class="whatis-img h-full w-full object-cover" />
            <div class="whatis-shade absolute inset-0" />
        </div>

        <div class="container-site w-full">
            <div class="max-w-[52rem]">
                <h2 id="whatis-title" class="whatis-h2" data-reveal="lines" :aria-label="about.headline">
                    <span v-for="(l, i) in lines" :key="i" class="line-mask block" aria-hidden="true">
                        <span class="line-inner block" :class="l.em && 'whatis-teal'">{{ l.text }}</span>
                    </span>
                </h2>

                <ul class="whatis-points mt-10 grid gap-5 sm:flex sm:gap-0">
                    <li v-for="p in about.points" :key="p.title" class="flex items-center gap-4 sm:pr-6 sm:[&:not(:first-child)]:border-l sm:[&:not(:first-child)]:border-line-light sm:[&:not(:first-child)]:pl-6" data-reveal="fade">
                        <span class="whatis-icon grid size-14 shrink-0 place-items-center rounded-full text-green">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <template v-if="p.icon === 'people'">
                                    <circle cx="12" cy="8" r="3" /><path d="M6.5 19a5.5 5.5 0 0 1 11 0" /><circle cx="5" cy="10" r="2" /><path d="M1.5 17.5A3.5 3.5 0 0 1 6 15" /><circle cx="19" cy="10" r="2" /><path d="M22.5 17.5A3.5 3.5 0 0 0 18 15" />
                                </template>
                                <template v-else-if="p.icon === 'ai'">
                                    <rect x="6" y="6" width="12" height="12" rx="2" /><path d="M9.5 15V9.5h1.5a1.5 1.5 0 0 1 0 3H9.5M14.5 9v6M9 3v3M15 3v3M9 18v3M15 18v3M3 9h3M3 15h3M18 9h3M18 15h3" />
                                </template>
                                <template v-else>
                                    <circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
                                </template>
                            </svg>
                        </span>
                        <span class="max-w-[9.5rem] text-[0.9375rem] leading-snug font-medium">{{ p.title }}</span>
                    </li>
                </ul>

                <div class="whatis-actions mt-10 flex flex-wrap items-center gap-x-10 gap-y-4" data-reveal="fade">
                    <CtaLink :href="about.cta.href" class="whatis-cta">{{ about.cta.label }}</CtaLink>
                    <button type="button" class="group inline-flex min-h-11 items-center gap-4 font-medium transition-colors duration-300 hover:text-green" @click="watching = true">
                        <span class="grid size-11 place-items-center rounded-full border border-current transition-colors duration-300 group-hover:bg-night group-hover:text-snow"><Icon name="play" :size="14" /></span>
                        {{ t('sections.watchOverview') }}
                    </button>
                </div>
            </div>
        </div>

        <div v-if="about.baccoStats" class="container-site mt-16 border-t border-line-light pt-6">
            <dl class="grid grid-cols-2 gap-6 md:grid-cols-4">
                <div v-for="s in about.baccoStats" :key="s.label" class="flex flex-col-reverse">
                    <dt class="mt-2 text-sm text-ink-2">{{ s.label }}</dt>
                    <dd class="t-stat">{{ s.value }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-ink-2">{{ about.baccoStatsSource }}</p>
        </div>

        <Dialog v-model:open="watching" :label="t('sections.watchOverview')" variant="modal" tone="light">
            <video v-if="about.video" :src="about.video" controls autoplay playsinline class="aspect-video w-full bg-night" />
            <p v-else class="text-lg">{{ t('sections.overviewSoon') }}</p>
        </Dialog>
    </section>
</template>

<style scoped>
.whatis-img {
    object-position: 100% 50%;
    transform-origin: 100% 50%;
    will-change: transform;
}
/* Desktop: the photo is a right-hand panel fading into the design's light blue → cream wash, so the copy never sits on it */
@media (min-width: 1024px) {
    .whatis {
        background: linear-gradient(170deg, #daeffe 0%, #f5fbfb 35%, #fcf7eb 70%, #fef4da 100%);
    }
    .whatis-bg {
        left: auto;
        width: 50%;
        mask-image: linear-gradient(90deg, transparent, #000 30%);
    }
    .whatis-shade {
        display: none;
    }
    .whatis .container-site > div {
        max-width: min(52rem, 50vw);
    }
    /* Three equal columns so the highlights always stay on one row */
    .whatis-points {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
    }
    .whatis-points > li {
        padding-right: 1rem;
        gap: 0.75rem;
    }
    .whatis-points > li:not(:first-child) {
        padding-left: 1rem;
    }
}
/* Light wash from the left keeps the copy readable over the photo */
.whatis-shade {
    background: linear-gradient(90deg, rgb(255 255 255 / 0.55) 0%, rgb(255 255 255 / 0.4) 35%, rgb(255 255 255 / 0.12) 52%, transparent 62%);
}
.whatis-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    line-height: 1.08;
    letter-spacing: -0.01em;
    font-size: clamp(2.5rem, 4.6vw, 4.5rem);
}
/* One headline line per mask (the shared .line-mask is inline-block for word reveals) */
.whatis-h2 .line-mask {
    display: block;
}
.whatis-teal {
    background: linear-gradient(100deg, #0a9aa8 0%, var(--green) 55%, #066a75 100%);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}
.whatis-icon {
    background: rgb(8 127 140 / 0.12);
}
/* Rounded gold pill instead of the site's notched button */
.whatis-cta {
    clip-path: none;
    border-radius: 999px;
    padding: 1.1rem 2.25rem;
    font-size: 1rem;
    text-transform: none;
    letter-spacing: 0;
    background: var(--gold);
    box-shadow: 0 10px 24px -14px rgb(234 185 75 / 0.8);
}
.whatis-cta:hover {
    background: #e0aa38;
}
/* Mobile: the photo sits behind the copy, so wash it out evenly */
@media (max-width: 1023px) {
    .whatis-img {
        object-position: 72% 50%;
    }
    .whatis-shade {
        background: linear-gradient(to bottom, rgb(255 255 255 / 0.82), rgb(255 255 255 / 0.9) 50%, rgb(255 255 255 / 0.95));
    }
}
/* Desktop: one screen tall below the fixed header; type and spacing scale with height */
@media (min-width: 1024px) {
    .whatis {
        --hdr: 112px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        min-height: 100svh;
        padding-block: calc(var(--hdr) + 2svh) 3svh;
    }
    .whatis-h2 {
        margin-top: 2.5svh;
        font-size: clamp(2.25rem, min(4.4vw, 7svh), 4.5rem);
    }
    .whatis-points,
    .whatis-actions {
        margin-top: 3.5svh;
    }
}
@media (min-width: 1280px) {
    .whatis {
        --hdr: 152px;
    }
}
</style>
