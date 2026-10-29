<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { useReveal } from '@/composables/useReveal';
import { utm, withUtm } from '@/composables/useUtm';
import type { Audience, Heading } from '@/types/content';

// Section 5. "Why attend": eyebrow, two-line headline (emphasis in teal), lead, then a 3×3 grid of audience cards,
// each a link with a tinted icon (teal / gold alternating), title, one-line summary and an arrow. Site background.
const props = defineProps<{ audiences: Audience[]; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const lines = computed(() => {
    const { title, emphasis } = props.heading;
    const i = title.indexOf(emphasis);
    return i < 0 ? [{ text: title, em: false }] : [{ text: title.slice(0, i).trim(), em: false }, { text: emphasis, em: true }].filter((l) => l.text);
});
</script>

<template>
    <section ref="root" data-section="5" aria-labelledby="why-title" class="why on-light flow section relative z-[1]">
        <div class="container-site">
            <p class="flex items-center gap-4 text-[0.8125rem] font-medium tracking-[0.25em] uppercase" data-reveal="fade">
                <span class="h-px w-11 bg-gold" aria-hidden="true" />{{ t('sections.why') }}?
            </p>
            <h2 id="why-title" class="why-h2 mt-5" data-reveal="lines" :aria-label="heading.title">
                <!-- Word masks, as SplitHeading (Built with partners): words rise in, one stagger step per line -->
                <span v-for="(l, i) in lines" :key="i" class="why-line" aria-hidden="true">
                    <template v-for="(w, j) in l.text.split(' ')" :key="j"><span class="line-mask"><span class="line-inner" :class="l.em && 'text-green'">{{ w }}</span></span>{{ ' ' }}</template>
                </span>
            </h2>
            <p class="why-lead mt-4 max-w-2xl text-lg leading-snug text-ink-2" data-reveal="fade">{{ t('sections.whyLead') }}</p>

            <ul class="why-grid mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-3 lg:gap-5">
                <li v-for="(a, i) in audiences" :key="a.title" data-reveal="fade">
                    <Link :href="withUtm(a.href, utm)" class="why-card group" :class="i % 2 ? 'is-gold' : 'is-teal'">
                        <span class="why-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <template v-if="a.icon === 'people'"><circle cx="12" cy="7.5" r="3" /><path d="M6.5 19a5.5 5.5 0 0 1 11 0" /><circle cx="5" cy="9.5" r="2" /><path d="M1.5 17a3.5 3.5 0 0 1 4.5-2.4" /><circle cx="19" cy="9.5" r="2" /><path d="M22.5 17a3.5 3.5 0 0 0-4.5-2.4" /></template>
                                <template v-else-if="a.icon === 'rocket'"><path d="M14 4c3-1 6-1 6-1s0 3-1 6l-6 6-5-5 6-6Z" /><path d="M8 10H5l-2 3 4 1M14 16v3l-3 2-1-4" /><circle cx="15" cy="9" r="1.5" /><path d="M6 18c-1 1-1.5 3-1.5 3s2-.5 3-1.5" /></template>
                                <template v-else-if="a.icon === 'cap'"><path d="M2 9.5 12 5l10 4.5L12 14 2 9.5Z" /><path d="M6 11.5V16c3 2.5 9 2.5 12 0v-4.5M21 10v5" /><circle cx="21" cy="16.5" r="1" /></template>
                                <template v-else-if="a.icon === 'bulb'"><path d="M9 17h6M10 20h4M12 3a6 6 0 0 0-3.5 10.9c.4.3.5.7.5 1.1v2h6v-2c0-.4.1-.8.5-1.1A6 6 0 0 0 12 3Z" /><path d="M12 1v-.5M4 4 3 3M20 4l1-1M2 10H1M23 10h-1" /></template>
                                <template v-else-if="a.icon === 'building'"><path d="M4 21V5l9-2v18M13 8h7v13M2 21h20" /><path d="M7 7h2M7 10h2M7 13h2M7 16h2M10 7h0M16 11h1M16 14h1M16 17h1" /></template>
                                <template v-else-if="a.icon === 'briefcase'"><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 12h18" /><rect x="10.5" y="11" width="3" height="2.5" rx=".5" /></template>
                                <template v-else-if="a.icon === 'globe'"><circle cx="12" cy="12" r="9" /><path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18M4.5 7.5h15M4.5 16.5h15" /></template>
                                <template v-else-if="a.icon === 'chart'"><path d="M4 20v-5M9 20v-8M14 20v-6M19 20V9" /><path d="m4 11 5-4 4 3 7-6M16 4h4v4" /></template>
                                <template v-else><path d="m2 12 4-4 4 1 2-1 2 1 4-1 4 4-3 3" /><path d="m7 15 2.5 2.5a1.5 1.5 0 0 0 2.1-2.1M10 14l3 3a1.5 1.5 0 0 0 2.1-2.1l-3-3M13 13l2 2a1.5 1.5 0 0 0 2.1-2.1L14 10" /><path d="m6 15 2 2a1.4 1.4 0 0 1-2 2l-2-2" /></template>
                            </svg>
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="why-title block">{{ a.title }}</span>
                            <span class="why-text mt-1.5 block text-ink-2">{{ a.summary }}</span>
                        </span>
                        <span class="why-arrow" aria-hidden="true">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </span>
                    </Link>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
/* Standard spacing between the blocks around it (counters above, programme finder below) */
.why.section {
    padding-block: var(--section-gap) 0;
}
.why-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 0.98;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5.2vw, 5rem);
}
.why-line {
    display: block;
}
.why-card {
    display: flex;
    align-items: center;
    gap: 1.25rem;
    height: 100%;
    padding: 1.25rem 1.25rem 1.25rem 1.25rem;
    border: 1px solid rgb(7 20 17 / 0.06);
    border-radius: 0.875rem;
    background: rgb(255 255 255 / 0.55);
    box-shadow: 0 1px 2px rgb(7 20 17 / 0.03);
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover),
        transform var(--dur-hover) var(--ease-hover);
}
.why-card:hover,
.why-card:focus-visible {
    background: rgb(255 255 255 / 0.9);
    box-shadow: 0 14px 34px -18px rgb(7 20 17 / 0.25);
    transform: translateY(-3px);
}
.is-teal {
    --tint: rgb(8 127 140 / 0.1);
    --ink: var(--green);
}
.is-gold {
    --tint: rgb(234 185 75 / 0.18);
    --ink: #d18b14;
}
.why-icon {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 5.75rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--tint);
    color: var(--ink);
}
.why-icon svg {
    width: 46%;
    height: 46%;
}
.why-title {
    font-size: 1.25rem;
    font-weight: 700;
    letter-spacing: -0.01em;
    color: var(--night);
}
.why-text {
    font-size: 0.9375rem;
    line-height: 1.45;
}
.why-arrow {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 2.5rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--tint);
    color: var(--ink);
    transition: transform var(--dur-hover) var(--ease-hover);
}
.is-teal .why-arrow {
    color: var(--night);
}
.why-card:hover .why-arrow {
    transform: translateX(4px);
}
@media (max-width: 639px) {
    .why-icon {
        width: 4rem;
    }
}
/* Desktop: type, cards and gaps scale with height so the section fits one screen */
@media (min-width: 1024px) {
    .why-h2 {
        margin-top: 2svh;
        font-size: clamp(2rem, min(5vw, 7svh), 5rem);
    }
    .why-lead {
        margin-top: 1.2svh;
        font-size: clamp(0.9375rem, 2.1svh, 1.25rem);
    }
    .why-grid {
        margin-top: 3svh;
        gap: clamp(0.625rem, 1.6svh, 1.25rem);
    }
    .why-card {
        padding: clamp(0.625rem, 1.6svh, 1.5rem) 1.25rem;
    }
    .why-icon {
        width: clamp(3rem, 7.5svh, 5.75rem);
    }
    .why-title {
        font-size: clamp(1rem, 2.2svh, 1.3125rem);
    }
    .why-text {
        font-size: clamp(0.75rem, 1.65svh, 0.9375rem);
    }
}
</style>
