<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { EASE, gsap, reveal, ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';

// Before the footer: centred eyebrow, one-line headline (emphasis in teal) and a lead, then the five ways to take part as
// tinted cards. Each photo is cut on a diagonal with a tinted icon disc sitting on the cut; a round arrow closes the card.
// Cards rise in one after the other on first view.
const { t } = useI18n();
const { site } = useShared();
const root = ref<HTMLElement>();
const grid = ref<HTMLElement>();
useReveal(root);

const tones: Record<string, string> = { register: 'is-sky', nominate: 'is-sun', sponsor: 'is-mint', exhibit: 'is-violet', forum: 'is-rose' };
const cards = computed(() =>
    ['register', 'nominate', 'sponsor', 'exhibit', 'forum'].map((id) => ({
        id,
        href: site.value.ctas.primary.find((c) => c.id === id)?.href ?? '/',
        tone: tones[id],
        image: `/media/photos/involved-${id}.jpg`,
    })),
);

useGsap(root, (reduced) => {
    const el = grid.value;
    if (reduced || !el) return;
    const items = [...el.children] as HTMLElement[];
    gsap.set(items, { y: 60, opacity: 0 });
    ScrollTrigger.create({
        trigger: el,
        start: 'top 85%',
        once: true,
        onEnter: () => reveal(() => gsap.to(items, { y: 0, opacity: 1, duration: 1.1, ease: EASE, stagger: 0.1, clearProps: 'transform,opacity' })),
    });
});
</script>

<template>
    <section ref="root" aria-labelledby="gi-title" class="gi on-light flow section relative z-[1] overflow-hidden">
        <span class="gi-pool" aria-hidden="true" />
        <span class="gi-dome" aria-hidden="true" />
        <svg class="gi-thread" viewBox="0 0 140 130" fill="none" aria-hidden="true">
            <path d="M8 8C50 30 92 72 132 122" stroke="#7cc7d3" stroke-width="1.5" />
            <circle cx="8" cy="8" r="7" fill="#e9b923" />
            <circle cx="132" cy="122" r="6" fill="#1d9aa8" />
        </svg>

        <div class="container-site">
            <div class="gi-head">
                <h2 id="gi-title" class="gi-h2" data-reveal="lines" :aria-label="`${t('sections.giTitle')} ${t('sections.giEmphasis')}`">
                    <span aria-hidden="true">
                        <template v-for="(w, j) in t('sections.giTitle').split(' ')" :key="`a${j}`"><span class="line-mask"><span class="line-inner">{{ w }}</span></span>{{ ' ' }}</template>
                        <template v-for="(w, j) in t('sections.giEmphasis').split(' ')" :key="`b${j}`"><span class="line-mask"><span class="line-inner text-green">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                </h2>
                <p class="gi-lead" data-reveal="fade">{{ t('sections.giLead') }}</p>
            </div>

            <ul ref="grid" class="gi-cards">
                <li v-for="c in cards" :key="c.id">
                    <Link :href="withUtm(c.href, utm)" class="gi-card group" :class="c.tone">
                        <span class="gi-media">
                            <span class="gi-photo"><img :src="c.image" alt="" loading="lazy" /></span>
                            <span class="gi-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                    <template v-if="c.id === 'register'"><circle cx="10" cy="8" r="3.5" /><path d="M3.5 20a6.5 6.5 0 0 1 13 0M18.5 6v5M16 8.5h5" /></template>
                                    <template v-else-if="c.id === 'nominate'"><path d="m12 3.5 2.6 5.3 5.8.8-4.2 4.1 1 5.8-5.2-2.7-5.2 2.7 1-5.8-4.2-4.1 5.8-.8L12 3.5Z" /></template>
                                    <template v-else-if="c.id === 'sponsor'"><path d="m11 17 2 2a1.4 1.4 0 0 0 2-2M13 15l2.5 2.5a1.4 1.4 0 0 0 2-2l-3-3M10 12.5l3.4-3.4a1.9 1.9 0 0 1 2.7 0L21 14M3 14l4.5-4.5a2 2 0 0 1 2.4-.3L12 10.5M3 9.5 6 6.5M21 9.5l-3-3M9 16l2 2a1.4 1.4 0 0 1-2 2l-2-2a1.4 1.4 0 0 1 2-2Z" /></template>
                                    <template v-else-if="c.id === 'exhibit'"><path d="M4 9h16l-1.5-4.5h-13L4 9ZM4 9v1.5a2.7 2.7 0 0 0 5.3 0 2.7 2.7 0 0 0 5.4 0 2.7 2.7 0 0 0 5.3 0V9M5.5 13v6.5h13V13M9.5 19.5V16h5v3.5" /></template>
                                    <template v-else><circle cx="12" cy="7.5" r="3" /><circle cx="5.5" cy="9.5" r="2.2" /><circle cx="18.5" cy="9.5" r="2.2" /><path d="M6.5 19.5a5.5 5.5 0 0 1 11 0ZM2 18.5a4 4 0 0 1 4.6-4M22 18.5a4 4 0 0 0-4.6-4" /></template>
                                </svg>
                            </span>
                        </span>
                        <span class="gi-body">
                            <span class="gi-title">{{ t(`sections.giCards.${c.id}.title`) }}</span>
                            <span class="gi-text">{{ t(`sections.giCards.${c.id}.text`) }}</span>
                            <span class="gi-arrow" aria-hidden="true">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </span>
                        </span>
                    </Link>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.gi.section {
    padding-block: var(--section-gap);
}
/* Soft ice pool on the left, a pale dome top right and a thin thread with a gold and a teal bead */
.gi-pool {
    position: absolute;
    left: -16rem;
    top: 12%;
    z-index: -1;
    width: 40rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(214 232 242 / 0.85), transparent);
    pointer-events: none;
}
.gi-dome {
    position: absolute;
    right: -20%;
    top: -30%;
    z-index: -1;
    width: min(70vw, 1000px);
    aspect-ratio: 1.3;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(222 238 245 / 0.9), rgb(222 238 245 / 0.3) 75%, transparent);
    pointer-events: none;
}
.gi-thread {
    position: absolute;
    top: 9rem;
    right: 10%;
    z-index: -1;
    width: 7rem;
    display: none;
}
.gi-head {
    max-width: 62rem;
    margin-inline: auto;
    text-align: center;
}
.gi-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    line-height: 1.05;
    letter-spacing: -0.025em;
    font-size: clamp(2.125rem, 4.4vw, 4rem);
    color: var(--night);
}
.gi-lead {
    margin-top: 0.875rem;
    font-size: clamp(1rem, 1.4vw, 1.3125rem);
    line-height: 1.5;
    color: var(--ink-2);
}

/* Cards */
.gi-cards {
    display: grid;
    gap: 1.25rem;
    margin-top: clamp(2rem, 4vw, 2.75rem);
}
.gi-card {
    --tint: var(--green);
    --disc: rgb(8 127 140 / 0.12);
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    border-radius: 1.25rem;
    box-shadow: 0 18px 40px -28px rgb(7 20 17 / 0.28);
    transition:
        transform var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover);
}
.gi-card:hover {
    transform: translateY(-6px);
    box-shadow: 0 28px 54px -28px rgb(7 20 17 / 0.4);
}
.is-sky {
    --tint: #1a8fb8;
    --disc: #dcf0f8;
    background: linear-gradient(180deg, #f1f8fc, #f7fbfd);
}
.is-sun {
    --tint: #d89a12;
    --disc: #fdecc4;
    background: linear-gradient(180deg, #fff7e8, #fffaf2);
}
.is-mint {
    --tint: #169a86;
    --disc: #d6f2ea;
    background: linear-gradient(180deg, #f1faf7, #f8fcfb);
}
.is-violet {
    --tint: #7046d8;
    --disc: #e9e1fb;
    background: linear-gradient(180deg, #f5f1fd, #f9f7fe);
}
.is-rose {
    --tint: #d0384e;
    --disc: #fbdfe3;
    background: linear-gradient(180deg, #fdf1f3, #fef8f9);
}

/* Photo cut on a rising diagonal; the icon disc is placed in percentages of the photo so it sits on the cut at every width */
.gi-media {
    position: relative;
    display: block;
    aspect-ratio: 341 / 219;
}
.gi-photo {
    position: absolute;
    inset: 0;
    overflow: hidden;
    border-radius: 1.25rem 1.25rem 0 0;
    clip-path: polygon(0 0, 100% 0, 100% 100%, 0 70%);
}
.gi-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: scale 0.9s var(--ease-out);
}
.gi-card:hover .gi-photo img {
    scale: 1.06;
}
.gi-icon {
    position: absolute;
    left: 10.5%;
    top: 63.5%;
    display: grid;
    place-items: center;
    width: 21.5%;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--disc);
    color: var(--tint);
    box-shadow: 0 0 0 4px rgb(255 255 255 / 0.55);
}
.gi-icon svg {
    width: 46%;
    height: 46%;
}
.gi-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    padding: 1.5rem 2.25rem 1.75rem;
}
.gi-title {
    font-size: clamp(1.375rem, 1.7vw, 1.625rem);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.015em;
    color: var(--night);
}
.gi-text {
    flex: 1;
    margin-top: 0.5rem;
    font-size: 1.0625rem;
    line-height: 1.45;
    color: var(--ink-2);
}
.gi-arrow {
    display: grid;
    place-items: center;
    width: 3.5rem;
    aspect-ratio: 1;
    margin-top: 1.5rem;
    border: 1.5px solid var(--tint);
    border-radius: 50%;
    color: var(--tint);
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        color var(--dur-hover) var(--ease-hover);
}
.gi-arrow svg {
    transition: transform var(--dur-hover) var(--ease-hover);
}
.gi-card:hover .gi-arrow {
    background: var(--tint);
    color: var(--white);
}
.gi-card:hover .gi-arrow svg {
    transform: translateX(3px);
}

@media (min-width: 640px) {
    .gi-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 900px) {
    .gi-cards {
        grid-template-columns: repeat(3, 1fr);
    }
}
@media (min-width: 1280px) {
    .gi-cards {
        grid-template-columns: repeat(5, 1fr);
    }
    .gi-body {
        padding-inline: 1.5rem;
    }
    .gi-text {
        font-size: 0.9375rem;
    }
    .gi-thread {
        display: block;
    }
}
@media (prefers-reduced-motion: reduce) {
    .gi-card,
    .gi-photo img {
        transition: none;
    }
}
</style>
