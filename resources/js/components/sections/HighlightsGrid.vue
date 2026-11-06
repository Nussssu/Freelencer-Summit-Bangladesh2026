<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { EASE, ScrollTrigger, gsap, reducedMotion, reveal, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import type { Highlight } from '@/types/content';

// After "Why attend": eyebrow and two-line headline (emphasis in teal), then the nine highlights as a 2 / 3 / 4 photo
// mosaic. A click picks the active tile, which opens its one-line summary; it auto-advances while on screen until the
// visitor takes over. Tiles open with a clip wipe while the photo settles.
const props = defineProps<{ highlights: Highlight[] }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
const grid = ref<HTMLElement>();
useReveal(root);

// Better-fitting event photos for some tiles (by the data's file name), with the focal point to keep in frame.
const picks: Record<string, { src: string; pos?: string }> = {
    'highlight-keynote.jpg': { src: '/media/photos/about-summit.jpg', pos: '78% center' },
    'highlight-marketplace.jpg': { src: '/media/photos/gallery-02.jpg' },
    'highlight-career.jpg': { src: '/media/photos/about-summit-light.jpg', pos: '85% center' },
    'highlight-networking.jpg': { src: '/media/photos/gallery-11.jpg', pos: 'center 30%' },
};
const photo = (src: string) => picks[src.split('/').pop()!] ?? { src };
const active = ref(0);
const go = (i: number) => (active.value = (i + props.highlights.length) % props.highlights.length);

let timer: ReturnType<typeof setInterval> | undefined;
let io: IntersectionObserver | undefined;
let paused = false;
let userTook = false;
const stop = () => clearInterval(timer);
const play = () => {
    stop();
    if (!userTook && !paused) timer = setInterval(() => go(active.value + 1), 4500);
};
const pick = (i: number) => {
    userTook = true;
    stop();
    go(i);
};
const hold = (on: boolean) => {
    paused = on;
    on ? stop() : play();
};
onMounted(() => {
    if (reducedMotion()) return;
    io = new IntersectionObserver(([e]) => (e.isIntersecting ? play() : stop()), { threshold: 0.35 });
    io.observe(root.value!);
});
onBeforeUnmount(() => {
    stop();
    io?.disconnect();
});

useGsap(root, (reduced) => {
    const el = grid.value;
    if (reduced || !el) return;
    const tiles = [...el.children] as HTMLElement[];
    tiles.forEach((tile, i) => {
        const img = tile.querySelector('img');
        const copy = tile.querySelectorAll('[data-hg-copy]');
        gsap.set(tile, { clipPath: 'inset(100% 0% 0% 0% round 0.875rem)' });
        if (img) gsap.set(img, { scale: 1.25 });
        gsap.set(copy, { opacity: 0, y: 18 });
        ScrollTrigger.create({
            trigger: tile,
            start: 'top 92%',
            once: true,
            onEnter: () => reveal(() => {
                const delay = (i < 2 ? i : i < 5 ? i - 2 : i - 5) * 0.09;
                gsap.to(tile, { clipPath: 'inset(0% 0% 0% 0% round 0.875rem)', duration: 1.1, ease: EASE, delay, onComplete: () => gsap.set(tile, { clearProps: 'clipPath' }) });
                if (img) gsap.to(img, { scale: 1, duration: 1.6, ease: EASE, delay, clearProps: 'transform' });
                gsap.to(copy, { opacity: 1, y: 0, duration: 0.8, ease: EASE, delay: delay + 0.25, stagger: 0.06 });
            }),
        });
    });
});
</script>

<template>
    <section ref="root" aria-labelledby="hg-title" class="hg on-light flow section relative z-[1]">
        <div class="container-site">
            <div class="hg-head">
                <h2 id="hg-title" class="hg-h2" data-reveal="lines" :aria-label="`${t('sections.hgTitle')} ${t('sections.hgEmphasis')}`">
                    <span class="block" aria-hidden="true">
                        <template v-for="(w, j) in t('sections.hgTitle').split(' ')" :key="j"><span class="line-mask"><span class="line-inner">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                    <span class="block" aria-hidden="true">
                        <span class="line-mask"><span class="line-inner text-green">{{ t('sections.hgEmphasis') }}</span></span>
                    </span>
                </h2>
            </div>

            <ul ref="grid" class="hg-grid" @mouseenter="hold(true)" @mouseleave="hold(false)" @focusin="hold(true)" @focusout="hold(false)">
                <li v-for="(h, i) in highlights" :key="h.title" class="hg-tile" :class="{ 'is-active': i === active }">
                    <img v-if="h.image" :src="photo(h.image).src" :style="{ objectPosition: photo(h.image).pos }" alt="" loading="lazy" class="hg-img" />
                    <button type="button" class="hg-hit" :aria-pressed="i === active" @click="pick(i)">
                        <span class="hg-title" data-hg-copy>{{ h.title }}</span>
                        <span class="hg-foot" data-hg-copy>
                            <span class="hg-text">{{ h.text }}</span>
                        </span>
                    </button>
                </li>
            </ul>
        </div>
    </section>
</template>

<style scoped>
/* Same spacing as "Why attend" above it */
.hg.section {
    padding-block: var(--section-gap) 0;
}
.hg-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 0.98;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5.2vw, 5rem);
}

/* Mosaic: two side by side (the first full width), and on desktop rows of 2 / 3 / 4 on a 20-column grid */
.hg-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 0.625rem;
    margin-top: 2.5rem;
}
.hg-tile:first-child {
    grid-column: span 2;
}
.hg-tile {
    position: relative;
    overflow: hidden;
    min-height: 13rem;
    border-radius: 0.875rem;
    background: var(--delta);
    isolation: isolate;
}
.hg-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    z-index: -2;
    transition:
        scale 0.9s var(--ease-out),
        filter 0.6s var(--ease-out);
    filter: saturate(0.9) brightness(0.85);
}
.hg-tile::after {
    content: '';
    position: absolute;
    inset: 0;
    z-index: -1;
    background: linear-gradient(100deg, rgb(4 14 22 / 0.88) 0%, rgb(4 14 22 / 0.55) 42%, rgb(4 14 22 / 0.05) 80%);
    transition: opacity 0.6s var(--ease-out);
}
.hg-hit {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    width: 100%;
    height: 100%;
    padding: clamp(1.125rem, 2.4vw, 2rem);
    text-align: left;
    color: var(--white);
}
.hg-title {
    max-width: 11ch;
    font-size: clamp(1.375rem, 2vw, 2rem);
    font-weight: 700;
    line-height: 1.1;
    letter-spacing: -0.015em;
}
.hg-foot {
    display: flex;
    align-items: center;
    gap: 0.875rem;
    margin-top: auto;
    padding-top: 1rem;
}
.hg-text {
    max-width: 30ch;
    font-size: 0.875rem;
    line-height: 1.35;
    opacity: 0;
    transform: translateX(-8px);
    transition:
        opacity 0.45s var(--ease-out),
        transform 0.45s var(--ease-out);
}

/* Hover and the active tile: photo pushes in; the active tile opens its summary */
.hg-tile:hover .hg-img,
.hg-tile.is-active .hg-img {
    scale: 1.06;
    filter: saturate(1.05) brightness(0.95);
}
.hg-tile.is-active .hg-text {
    opacity: 1;
    transform: none;
}
/* The first tile shows its title only, no summary */
.hg-tile:first-child .hg-text {
    display: none;
}
.hg-tile.is-active {
    box-shadow:
        0 0 0 2px var(--green),
        0 22px 44px -22px rgb(8 127 140 / 0.6);
}
.hg-hit:focus-visible {
    outline: 2px solid var(--gold);
    outline-offset: -4px;
    border-radius: 0.875rem;
}

/* Phones: compact half-width tiles; only the full-width first tile has room for the summary */
@media (max-width: 639px) {
    .hg-tile {
        min-height: 10.5rem;
        border-radius: 0.75rem;
    }
    .hg-hit {
        padding: 0.875rem;
    }
    .hg-title {
        font-size: 1.0625rem;
    }
    .hg-tile:not(:first-child) .hg-text {
        display: none;
    }
    .hg-tile:first-child {
        min-height: 12.5rem;
    }
    .hg-tile:first-child .hg-title {
        font-size: 1.375rem;
    }
}
@media (min-width: 640px) {
    .hg-grid {
        gap: 0.875rem;
    }
}
@media (min-width: 1024px) {
    .hg-h2 {
        margin-top: 2svh;
        font-size: clamp(2rem, min(5vw, 7svh), 5rem);
    }
    .hg-grid {
        grid-template-columns: repeat(20, 1fr);
        grid-template-rows: clamp(11rem, 26svh, 16rem) clamp(9rem, 19svh, 12rem) clamp(9rem, 19svh, 12rem);
        gap: clamp(0.625rem, 1.4svh, 0.875rem);
        margin-top: 4svh;
    }
    .hg-tile {
        min-height: 0;
    }
    .hg-tile:nth-child(1) {
        grid-column: span 11;
    }
    .hg-tile:nth-child(2) {
        grid-column: span 9;
    }
    .hg-tile:nth-child(n + 3):nth-child(-n + 4) {
        grid-column: span 7;
    }
    .hg-tile:nth-child(5) {
        grid-column: span 6;
    }
    .hg-tile:nth-child(n + 6) {
        grid-column: span 5;
    }
    .hg-tile:nth-child(n + 3) .hg-hit {
        padding: clamp(1rem, 2.4svh, 1.5rem) 1.5rem;
    }
    .hg-tile:nth-child(n + 3) .hg-title {
        font-size: clamp(1.125rem, 2.6svh, 1.5rem);
    }
    .hg-tile:nth-child(n + 6) .hg-text {
        font-size: 0.8125rem;
    }
}
@media (prefers-reduced-motion: reduce) {
    .hg-img,
    .hg-text {
        transition: none;
    }
}
</style>
