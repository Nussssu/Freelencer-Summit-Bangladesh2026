<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { EASE, gsap, reveal, ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { utm, withUtm } from '@/composables/useUtm';

// After the forum: eyebrow, two-line headline (emphasis in teal) and a "view all" link, then a story carousel. The
// featured card is large and the rest queue beside it, the arrow after them; the arrow or a dot brings another forward and the row re-flows
// (FLIP) while the cards grow and shrink into place. Cards rise in from the right on first view. Sample people for now.
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

const stories = ['s1', 's2', 's3'].map((id, i) => ({ id, image: `/media/photos/story-${i + 1}.jpg` }));
const active = ref(0);
const ordered = computed(() => stories.map((_, i) => stories[(active.value + i) % stories.length]));
const go = (i: number) => (active.value = (i + stories.length) % stories.length);

useGsap(root, (reduced) => {
    const el = root.value?.querySelector<HTMLElement>('.st-track');
    if (reduced || !el) return;
    const cards = [...el.children] as HTMLElement[];
    gsap.set(cards, { x: 90, opacity: 0 });
    ScrollTrigger.create({
        trigger: el,
        start: 'top 82%',
        once: true,
        onEnter: () => reveal(() => gsap.to(cards, { x: 0, opacity: 1, duration: 1.1, ease: EASE, stagger: 0.14, clearProps: 'transform,opacity' })),
    });
});
</script>

<template>
    <section ref="root" aria-labelledby="st-title" class="st on-light flow section relative z-[1] overflow-hidden">
        <span class="st-arc" aria-hidden="true" />
        <span class="st-pool" aria-hidden="true" />

        <div class="container-site st-layout">
            <div class="st-intro">
                <h2 id="st-title" class="st-h2" data-reveal="lines" :aria-label="`${t('sections.stTitle')} ${t('sections.stEmphasis')}`">
                    <span class="block" aria-hidden="true">
                        <template v-for="(w, j) in t('sections.stTitle').split(' ')" :key="j"><span class="line-mask"><span class="line-inner">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                    <span class="block" aria-hidden="true">
                        <template v-for="(w, j) in t('sections.stEmphasis').split(' ')" :key="j"><span class="line-mask"><span class="line-inner text-green">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                </h2>
                <div class="mt-8" data-reveal="fade">
                    <Link :href="withUtm('/success-stories', utm)" class="st-all group">
                        <span class="st-round" aria-hidden="true">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </span>
                        {{ t('sections.stAll') }}
                    </Link>
                </div>
            </div>

            <div class="st-stage">
                <div class="st-row">
                    <TransitionGroup tag="ul" name="st" class="st-track" aria-live="polite">
                        <li v-for="(s, i) in ordered" :key="s.id" class="st-card" :class="i === 0 && 'is-active'" :aria-hidden="i !== 0 ? 'true' : undefined">
                            <Link :href="withUtm('/success-stories', utm)" class="st-link" :tabindex="i === 0 ? undefined : -1">
                                <span class="st-media">
                                    <img :src="s.image" alt="" loading="lazy" />
                                    <span class="st-play" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M8 5.5v13l10.5-6.5L8 5.5Z" /></svg>
                                    </span>
                                </span>
                                <span class="st-body">
                                    <span class="st-title">{{ t(`sections.stItems.${s.id}.title`) }}</span>
                                    <span class="st-dash" aria-hidden="true" />
                                    <span class="st-name">{{ t(`sections.stItems.${s.id}.name`) }}</span>
                                    <span class="st-role">{{ t(`sections.stItems.${s.id}.role`) }}</span>
                                </span>
                            </Link>
                        </li>
                    </TransitionGroup>
                    <button type="button" class="st-next" :aria-label="t('sections.stNext')" @click="go(active + 1)">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </button>
                </div>
                <div class="st-dots">
                    <button v-for="(s, i) in stories" :key="s.id" type="button" class="st-dot" :class="i === active && 'is-on'" :aria-label="t('sections.stGoto', { n: i + 1 })" :aria-current="i === active || undefined" @click="go(i)" />
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
.st.section {
    padding-block: var(--section-gap);
}
.st-arc {
    position: absolute;
    top: 4%;
    right: -20%;
    z-index: -1;
    width: min(100vw, 1400px);
    aspect-ratio: 1.7;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(222 237 243 / 0.85), rgb(222 237 243 / 0.3) 72%, transparent);
    pointer-events: none;
}
.st-pool {
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
.st-layout {
    display: grid;
    gap: 3rem;
    align-items: center;
}
.st-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 0.98;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    color: var(--night);
}
.st-all {
    display: inline-flex;
    align-items: center;
    gap: 1.75rem;
    font-size: 1.1875rem;
    font-weight: 500;
    color: var(--green);
}
.st-round {
    display: grid;
    place-items: center;
    width: 4.25rem;
    aspect-ratio: 1;
    border: 1px solid rgb(7 20 17 / 0.14);
    border-radius: 50%;
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        color var(--dur-hover) var(--ease-hover),
        border-color var(--dur-hover) var(--ease-hover);
}
.st-round svg {
    transition: transform var(--dur-hover) var(--ease-hover);
}
.st-all:hover .st-round {
    background: var(--green);
    border-color: var(--green);
    color: var(--white);
}
.st-all:hover .st-round svg {
    transform: translateX(3px);
}

/* Carousel */
.st-stage {
    position: relative;
}
.st-row {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.st-track {
    position: relative;
    display: flex;
    min-width: 0;
    align-items: center;
    gap: 1.25rem;
}
.st-card {
    flex-shrink: 0;
    width: clamp(12rem, 13.5vw, 17rem);
    transition: width 0.7s var(--ease-out);
}
.st-card.is-active {
    width: clamp(18rem, 25vw, 29rem);
}
.st-link {
    display: flex;
    flex-direction: column;
    overflow: hidden;
    border-radius: 1rem;
    background: #13233a;
    color: var(--white);
    box-shadow: 0 30px 60px -30px rgb(7 20 17 / 0.55);
    transition:
        transform var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover);
}
.st-link:hover {
    transform: translateY(-4px);
    box-shadow: 0 36px 70px -30px rgb(7 20 17 / 0.65);
}
.st-media {
    position: relative;
    display: block;
    aspect-ratio: 1.12;
}
.st-media img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: scale 0.9s var(--ease-out);
}
.st-link:hover .st-media img {
    scale: 1.05;
}
/* the photo melts into the card's navy so the copy can sit over its lower edge */
.st-media::after {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, transparent 38%, rgb(19 35 58 / 0.55) 68%, #13233a 100%);
}
.st-play {
    position: absolute;
    left: 50%;
    top: 70%;
    z-index: 1;
    display: grid;
    place-items: center;
    width: 3.5rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--white);
    color: var(--night);
    translate: -50% -50%;
    box-shadow: 0 10px 24px -8px rgb(0 0 0 / 0.5);
    transition:
        width 0.7s var(--ease-out),
        scale var(--dur-hover) var(--ease-hover);
}
.st-play svg {
    width: 38%;
    margin-left: 6%;
}
.is-active .st-play {
    width: 4.5rem;
}
.is-active .st-play::before {
    content: '';
    position: absolute;
    inset: 0;
    border-radius: 50%;
    border: 2px solid rgb(255 255 255 / 0.7);
    animation: st-ping 2.4s var(--ease-out) infinite;
}
@keyframes st-ping {
    from {
        transform: scale(1);
        opacity: 0.9;
    }
    to {
        transform: scale(1.7);
        opacity: 0;
    }
}
.st-link:hover .st-play {
    scale: 1.08;
}
.st-body {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    margin-top: -1.25rem;
    padding: 0 1.5rem 1.5rem;
}
.st-title {
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.15;
    letter-spacing: -0.01em;
    transition: font-size 0.7s var(--ease-out);
}
.st-dash {
    width: 2.25rem;
    height: 2px;
    margin-top: 1rem;
    background: var(--gold);
}
.st-name {
    margin-top: 1rem;
    font-weight: 700;
}
.st-role {
    margin-top: 0.125rem;
    font-size: 0.875rem;
    color: rgb(255 255 255 / 0.75);
}

.is-active .st-body {
    padding: 0 2rem 2rem;
}
.is-active .st-title {
    font-size: clamp(1.5rem, 2.1vw, 2rem);
}

/* re-order: cards slide to their new slots */
.st-move {
    transition: transform 0.7s var(--ease-out);
}

.st-next {
    flex-shrink: 0;
    display: grid;
    place-items: center;
    width: 4.5rem;
    aspect-ratio: 1;
    border: 1px solid rgb(7 20 17 / 0.14);
    border-radius: 50%;
    background: rgb(255 255 255 / 0.85);
    color: var(--green);
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        color var(--dur-hover) var(--ease-hover);
}
.st-next:hover {
    background: var(--green);
    color: var(--white);
}
.st-dots {
    display: flex;
    justify-content: center;
    gap: 0.75rem;
    margin-top: 1.75rem;
}
.st-dot {
    width: 0.625rem;
    height: 0.625rem;
    border-radius: 999px;
    background: rgb(7 20 17 / 0.15);
    transition:
        width 0.4s var(--ease-out),
        background-color 0.4s var(--ease-out);
}
.st-dot.is-on {
    width: 1.75rem;
    background: var(--green);
}

/* Phones: one card at a time */
@media (max-width: 767px) {
    .st-card {
        display: none;
    }
    .st-card.is-active {
        display: block;
        width: 100%;
    }
    .st-row {
        flex-direction: column;
        align-items: flex-end;
        gap: 1rem;
    }
    .st-track {
        width: 100%;
    }
    .st-next {
        width: 3.5rem;
    }
    .st-dots {
        justify-content: flex-start;
        margin-top: -2.5rem;
    }
}
/* Desktop: the carousel runs past the container towards the page edge, with the arrow over the last card */
@media (min-width: 1024px) {
    .st-layout {
        grid-template-columns: minmax(0, 0.85fr) minmax(0, 2fr);
        gap: clamp(2rem, 3.5vw, 4rem);
    }
    .st-h2 {
        font-size: clamp(2.25rem, 3.2vw, 4.25rem);
        white-space: nowrap;
    }
    .st-stage {
        margin-right: calc(-1 * (max(0px, (100vw - var(--container-site)) / 2) + 32px));
    }
    .st-row {
        padding-right: clamp(1rem, 3vw, 3rem);
    }
}
@media (prefers-reduced-motion: reduce) {
    .st-card,
    .st-play,
    .st-title,
    .st-move {
        transition: none;
    }
    .is-active .st-play::before {
        animation: none;
    }
}
</style>
