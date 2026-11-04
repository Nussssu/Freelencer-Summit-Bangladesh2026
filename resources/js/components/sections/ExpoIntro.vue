<script setup lang="ts">
import { ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { EASE, gsap, reveal, ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { utm, withUtm } from '@/composables/useUtm';

// After the awards intro: eyebrow, two-line headline (emphasis in teal), then two photo cards
// (exhibit / sponsor). Cards rise in one after the other while their photo wipes open from the top and settles from a
// zoom; the soft arc behind them swells in with the scroll.
const { t } = useI18n();
const root = ref<HTMLElement>();
const grid = ref<HTMLElement>();
useReveal(root);

const cards = [
    { id: 'exhibit', href: '/exhibition/apply', image: '/media/photos/exhibit-booth.jpg', tone: 'is-ice' },
    { id: 'sponsor', href: '/sponsorship-opportunity', image: '/media/photos/sponsor-hall.jpg', tone: 'is-cream' },
];

useGsap(root, (reduced) => {
    const el = grid.value;
    if (reduced || !el) return;
    const items = [...el.children] as HTMLElement[];
    items.forEach((card, i) => {
        const media = card.querySelector('.ex-media')!;
        const img = card.querySelector('img');
        const copy = card.querySelectorAll('[data-ex-copy]');
        gsap.set(card, { y: 70, opacity: 0 });
        gsap.set(media, { clipPath: 'inset(0% 0% 100% 0%)' });
        if (img) gsap.set(img, { scale: 1.3 });
        gsap.set(copy, { y: 20, opacity: 0 });
        ScrollTrigger.create({
            trigger: el,
            start: 'top 85%',
            once: true,
            onEnter: () => reveal(() => {
                const d = i * 0.18;
                gsap.to(card, { y: 0, opacity: 1, duration: 1.1, ease: EASE, delay: d, clearProps: 'transform,opacity' });
                gsap.to(media, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.3, ease: 'power3.inOut', delay: d + 0.1, clearProps: 'clipPath' });
                if (img) gsap.to(img, { scale: 1, duration: 1.8, ease: EASE, delay: d + 0.1, clearProps: 'transform' });
                gsap.to(copy, { y: 0, opacity: 1, duration: 0.8, ease: EASE, delay: d + 0.55, stagger: 0.08, clearProps: 'transform,opacity' });
            }),
        });
    });
    gsap.fromTo(root.value!.querySelector('.ex-arc'), { scale: 0.8, opacity: 0 }, { scale: 1, opacity: 1, ease: 'none', scrollTrigger: { trigger: root.value, start: 'top bottom', end: 'top 30%', scrub: true } });
});
</script>

<template>
    <section ref="root" aria-labelledby="ex-title" class="ex on-light flow section relative z-[1] overflow-hidden">
        <span class="ex-arc" aria-hidden="true" />
        <span class="ex-pool" aria-hidden="true" />

        <div class="container-site ex-layout">
            <div class="ex-intro">
                <h2 id="ex-title" class="ex-h2" data-reveal="lines" :aria-label="`${t('sections.exTitle')} ${t('sections.exEmphasis')}`">
                    <span class="block" aria-hidden="true">
                        <template v-for="(w, j) in t('sections.exTitle').split(' ')" :key="j"><span class="line-mask"><span class="line-inner">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                    <span class="block" aria-hidden="true">
                        <span class="line-mask"><span class="line-inner text-green">{{ t('sections.exEmphasis') }}</span></span>
                    </span>
                </h2>
            </div>

            <ul ref="grid" class="ex-cards">
                <li v-for="c in cards" :key="c.id">
                    <Link :href="withUtm(c.href, utm)" class="ex-card group" :class="c.tone">
                        <span class="ex-media"><img :src="c.image" alt="" loading="lazy" /></span>
                        <span class="ex-body">
                            <span class="ex-title" data-ex-copy>{{ t(`sections.exCards.${c.id}.title`) }}</span>
                            <span class="ex-text" data-ex-copy>{{ t(`sections.exCards.${c.id}.text`) }}</span>
                            <span class="ex-more" data-ex-copy>
                                {{ t('sections.exLearn') }}
                                <span class="ex-arrow" aria-hidden="true">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                </span>
                            </span>
                        </span>
                    </Link>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
.ex.section {
    padding-block: var(--section-gap);
}
/* A large soft ice dome behind the cards, and a pale pool bottom left */
.ex-arc {
    position: absolute;
    top: 8%;
    right: -18%;
    z-index: -1;
    width: min(95vw, 1300px);
    aspect-ratio: 1.6;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(220 236 242 / 0.85), rgb(220 236 242 / 0.35) 70%, transparent);
    pointer-events: none;
}
.ex-pool {
    position: absolute;
    left: -14rem;
    bottom: -14rem;
    z-index: -1;
    width: 34rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(214 234 240 / 0.8), transparent);
    pointer-events: none;
}
.ex-layout {
    display: grid;
    gap: 2.5rem;
    align-items: center;
}
.ex-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 0.98;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    color: var(--night);
}
.ex-arrow svg {
    transition: transform var(--dur-hover) var(--ease-hover);
}

/* Cards */
.ex-cards {
    display: grid;
    gap: 1.25rem;
}
.ex-card {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;
    border: 1px solid rgb(7 20 17 / 0.06);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgb(7 20 17 / 0.03);
    transition:
        transform var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover);
}
.ex-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 26px 50px -26px rgb(7 20 17 / 0.35);
}
.is-ice {
    background: linear-gradient(180deg, rgb(232 243 246 / 0.95), rgb(240 247 249 / 0.85));
}
.is-cream {
    background: linear-gradient(180deg, rgb(252 244 228 / 0.95), rgb(252 247 238 / 0.85));
}
.ex-media {
    display: block;
    aspect-ratio: 2.1;
    overflow: hidden;
    border-radius: 1rem;
}
.ex-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: scale 0.9s var(--ease-out);
}
.ex-card:hover .ex-media img {
    scale: 1.06;
}
.ex-body {
    display: flex;
    flex: 1;
    flex-direction: column;
    align-items: flex-start;
    padding: 1.5rem 1.75rem 1.75rem;
}
.ex-title {
    font-size: clamp(1.5rem, 2vw, 1.875rem);
    font-weight: 800;
    line-height: 1.15;
    letter-spacing: -0.01em;
    color: var(--night);
}
.ex-text {
    flex: 1;
    margin-top: 0.625rem;
    font-size: 1.0625rem;
    line-height: 1.5;
    color: var(--ink-2);
}
.ex-more {
    display: inline-flex;
    align-items: center;
    gap: 1.25rem;
    margin-top: 1.5rem;
    font-size: 1.125rem;
    font-weight: 600;
    color: var(--green);
}
.ex-arrow {
    display: grid;
    place-items: center;
    width: 3.5rem;
    aspect-ratio: 1;
    border: 1.5px solid currentColor;
    border-radius: 50%;
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        color var(--dur-hover) var(--ease-hover);
}
.ex-card:hover .ex-arrow {
    background: var(--green);
    border-color: var(--green);
    color: var(--white);
}
.ex-card:hover .ex-arrow svg {
    transform: translateX(3px);
}

@media (min-width: 768px) {
    .ex-cards {
        grid-template-columns: repeat(2, 1fr);
    }
}
@media (min-width: 1024px) {
    .ex-layout {
        grid-template-columns: minmax(0, 0.9fr) minmax(0, 2fr);
        gap: clamp(2rem, 4vw, 5rem);
    }
    .ex-h2 {
        font-size: clamp(2.25rem, 3.6vw, 4rem);
    }
}
@media (prefers-reduced-motion: reduce) {
    .ex-card,
    .ex-media img {
        transition: none;
    }
}
</style>
