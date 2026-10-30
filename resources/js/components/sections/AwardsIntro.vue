<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { gsap, ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';

// After the agenda: eyebrow, two-line headline (emphasis in teal, gold square stop), teal CTA, three
// award cards and a crystal trophy. The trophy is already there as a faint, blurred ghost behind the agenda above, and
// scroll carries it down into this section, sharpening and settling on its plinth; once in place it floats and glints.
const { t } = useI18n();
const { site } = useShared();
const root = ref<HTMLElement>();
const trophy = ref<HTMLElement>();
useReveal(root);

const nominate = computed(() => site.value.ctas.primary.find((c) => c.id === 'nominate')!.href);
const cards = computed(() => [
    { id: 'categories', href: '/awards/categories', tone: 'is-ice', icon: 'trophy' },
    { id: 'eligibility', href: '/awards', tone: 'is-white', icon: 'doc' },
    { id: 'nominate', href: nominate.value, tone: 'is-cream', icon: 'pen' },
]);

useGsap(root, (reduced) => {
    const el = trophy.value;
    if (reduced || !el) return;
    const mm = gsap.matchMedia();
    mm.add({ desktop: '(min-width: 1024px)', mobile: '(max-width: 1023px)' }, (ctx) => {
        // Start with the trophy's centre at this height of the screen while the section is still just below it,
        // i.e. sitting over the section above (offsetTop ignores transforms, so it survives refreshes)
        const at = ctx.conditions!.desktop ? 0.42 : 0.6;
        // Travel and settle over a long stretch of scroll as the section enters, easing in and out so it glides
        // rather than drops. A long scrub keeps the trophy from snapping into place on quick wheels / trackpad flicks.
        const tl = gsap.timeline({
            scrollTrigger: {
                trigger: root.value,
                start: 'top bottom',
                end: ctx.conditions!.desktop ? 'top 30%' : 'top 45%',
                scrub: 2.4,
                invalidateOnRefresh: true,
            },
        });
        tl.fromTo(el, { y: () => -(innerHeight * (1 - at) + el.offsetTop + el.offsetHeight / 2), scale: 0.88, rotate: -6 }, { y: 0, scale: 1, rotate: 0, ease: 'sine.inOut', duration: 1 }, 0).fromTo(
            el,
            { opacity: 0, filter: 'blur(10px)' },
            { opacity: 1, filter: 'blur(0px)', ease: 'sine.inOut', duration: 0.75 },
            0.1,
        );
    });
    // Settled ambient motion starts only while this section is on screen (not merely on page reload).
    const ambient = gsap.timeline({ paused: true });
    ambient
        .to(el.querySelector('.aw-float'), { y: -8, duration: 3.2, ease: 'sine.inOut', yoyo: true, repeat: -1 }, 0)
        .fromTo(el.querySelector('.aw-sweep'), { x: -160 }, { x: 260, duration: 2.4, ease: 'power2.inOut', repeat: -1, repeatDelay: 2.6 }, 0)
        .to(root.value!.querySelectorAll('.aw-spark'), { scale: 0.55, opacity: 0.45, duration: 1.4, ease: 'sine.inOut', yoyo: true, repeat: -1, stagger: 0.7, transformOrigin: '50% 50%' }, 0);
    ScrollTrigger.create({
        trigger: root.value,
        start: 'top 90%',
        end: 'bottom 10%',
        onEnter: () => ambient.play(),
        onEnterBack: () => ambient.play(),
        onLeave: () => ambient.pause(),
        onLeaveBack: () => ambient.pause(),
    });
    // Background arcs draw in with the section
    root.value!.querySelectorAll<SVGPathElement>('.aw-arc').forEach((p) => {
        const len = p.getTotalLength();
        gsap.fromTo(p, { strokeDasharray: len, strokeDashoffset: len }, { strokeDashoffset: 0, ease: 'none', scrollTrigger: { trigger: root.value, start: 'top 90%', end: 'top 20%', scrub: true } });
    });
});
</script>

<template>
    <section ref="root" aria-labelledby="aw-title" class="aw on-light flow section relative z-0">
        <!-- Decoration: gold orbit lines, teal dots and a sparkle top right; a pale pool bottom left -->
        <svg class="aw-deco" viewBox="0 0 800 600" preserveAspectRatio="xMaxYMin meet" aria-hidden="true">
            <path class="aw-arc" d="M330 0 C 420 150 600 190 800 170" fill="none" stroke="#e3c27a" stroke-width="1.2" />
            <path class="aw-arc" d="M300 260 C 380 90 640 60 760 260 C 800 330 790 420 760 480" fill="none" stroke="#e3c27a" stroke-width="1" opacity="0.8" />
            <circle cx="370" cy="140" r="7" fill="var(--green)" />
            <circle cx="752" cy="410" r="7" fill="var(--green)" />
            <path class="aw-spark" d="M690 150 l5 16 16 5 -16 5 -5 16 -5 -16 -16 -5 16 -5Z" fill="#e0b545" />
        </svg>
        <span class="aw-pool" aria-hidden="true" />

        <div class="container-site aw-layout">
            <div class="aw-intro">
                <h2 id="aw-title" class="aw-h2" data-reveal="lines" :aria-label="`${t('sections.awTitle')} ${t('sections.awEmphasis')}.`">
                    <span class="block" aria-hidden="true">
                        <template v-for="(w, j) in t('sections.awTitle').split(' ')" :key="j"><span class="line-mask"><span class="line-inner">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                    <span class="block" aria-hidden="true">
                        <template v-for="(w, j) in t('sections.awEmphasis').split(' ')" :key="j"><span class="line-mask"><span class="line-inner text-green">{{ w }}<span v-if="j === t('sections.awEmphasis').split(' ').length - 1" class="aw-stop" /></span></span>{{ ' ' }}</template>
                    </span>
                </h2>
                <div class="mt-8" data-reveal="fade">
                    <Link :href="withUtm('/awards/categories', utm)" class="aw-cta">
                        {{ t('sections.awCta') }}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </Link>
                </div>
            </div>

            <ul class="aw-cards">
                <li v-for="c in cards" :key="c.id" data-reveal="fade">
                    <Link :href="withUtm(c.href, utm)" class="aw-card" :class="c.tone">
                        <span class="aw-icon" aria-hidden="true">
                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <template v-if="c.icon === 'trophy'"><path d="M8 4h8v5a4 4 0 0 1-8 0V4ZM8 6H4.5a3 3 0 0 0 3.5 4M16 6h3.5a3 3 0 0 1-3.5 4M12 13v4M8 21h8M9.5 17h5" /></template>
                                <template v-else-if="c.icon === 'doc'"><path d="M7 3h7l4 4v14H7V3Z" /><path d="M14 3v4h4M10 11h5M10 14h5M10 17h3" /></template>
                                <template v-else><path d="M15.5 4.5l4 4L9 19l-5 1 1-5L15.5 4.5Z" /><path d="M13.5 6.5l4 4" /></template>
                            </svg>
                        </span>
                        <span class="aw-card-title">{{ t(`sections.awCards.${c.id}.title`) }}</span>
                        <span class="aw-card-text">{{ t(`sections.awCards.${c.id}.text`) }}</span>
                        <span class="aw-arrow" aria-hidden="true">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </span>
                    </Link>
                </li>
            </ul>

            <!-- Crystal trophy on a marble plinth (drawn, so it can sharpen from a blur) -->
            <div ref="trophy" class="aw-trophy" aria-hidden="true">
                <svg class="aw-float" viewBox="0 0 260 500">
                    <defs>
                        <linearGradient id="aw-c1" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#fffaf0" />
                            <stop offset="0.5" stop-color="#f1d9a4" />
                            <stop offset="1" stop-color="#c99a4b" />
                        </linearGradient>
                        <linearGradient id="aw-c2" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#e8c27c" />
                            <stop offset="0.45" stop-color="#b8853a" />
                            <stop offset="1" stop-color="#6e4a1c" />
                        </linearGradient>
                        <linearGradient id="aw-c3" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#ffffff" stop-opacity="0.95" />
                            <stop offset="1" stop-color="#f6e2b6" stop-opacity="0.6" />
                        </linearGradient>
                        <linearGradient id="aw-c4" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0" stop-color="#5b3c17" />
                            <stop offset="1" stop-color="#a4732f" />
                        </linearGradient>
                        <linearGradient id="aw-gold" x1="0" y1="0" x2="1" y2="0">
                            <stop offset="0" stop-color="#9c7230" />
                            <stop offset="0.35" stop-color="#f3d58f" />
                            <stop offset="0.7" stop-color="#c9a056" />
                            <stop offset="1" stop-color="#8a6326" />
                        </linearGradient>
                        <linearGradient id="aw-marble" x1="0" y1="0" x2="1" y2="1">
                            <stop offset="0" stop-color="#2a3133" />
                            <stop offset="0.5" stop-color="#14191b" />
                            <stop offset="1" stop-color="#050708" />
                        </linearGradient>
                        <linearGradient id="aw-sweep-g" x1="0" x2="1">
                            <stop offset="0" stop-color="#fff" stop-opacity="0" />
                            <stop offset="0.5" stop-color="#fff" stop-opacity="0.75" />
                            <stop offset="1" stop-color="#fff" stop-opacity="0" />
                        </linearGradient>
                        <radialGradient id="aw-glow" cx="50%" cy="50%" r="50%">
                            <stop offset="0" stop-color="#f7dfa6" stop-opacity="0.75" />
                            <stop offset="1" stop-color="#f7dfa6" stop-opacity="0" />
                        </radialGradient>
                        <clipPath id="aw-crystal"><path d="M62 18 L212 92 L192 338 L96 338 Z" /></clipPath>
                    </defs>
                    <ellipse cx="140" cy="488" rx="130" ry="12" fill="url(#aw-glow)" />
                    <!-- crystal facets -->
                    <g clip-path="url(#aw-crystal)">
                        <path d="M62 18 L96 338 L132 338 L106 56 Z" fill="url(#aw-c1)" />
                        <path d="M62 18 L106 56 L132 338 L192 338 L212 92 Z" fill="url(#aw-c2)" />
                        <path d="M106 56 L212 92 L156 124 Z" fill="url(#aw-c3)" />
                        <path d="M156 124 L212 92 L192 338 L160 338 Z" fill="url(#aw-c4)" opacity="0.85" />
                        <path d="M106 56 L156 124 L160 338 L132 338 Z" fill="#f4dca9" opacity="0.55" />
                        <rect class="aw-sweep" x="0" y="0" width="70" height="360" fill="url(#aw-sweep-g)" transform="skewX(-18)" />
                    </g>
                    <g fill="none" stroke="#fff" stroke-linejoin="round" opacity="0.7" stroke-width="1.2">
                        <path d="M62 18 L212 92 L192 338 L96 338 Z" />
                        <path d="M62 18 L106 56 L212 92 M106 56 L132 338 M156 124 L160 338 M106 56 L156 124 L212 92" opacity="0.6" />
                    </g>
                    <!-- collar + plinth -->
                    <path d="M84 338 H204 L214 366 H74 Z" fill="url(#aw-gold)" />
                    <rect x="56" y="366" width="176" height="12" fill="#2b2216" />
                    <rect x="42" y="378" width="204" height="96" rx="3" fill="url(#aw-marble)" />
                    <g fill="none" stroke="#c9d1d3" stroke-linecap="round" opacity="0.35">
                        <path d="M60 400 C 90 420 100 390 130 430 S 170 450 190 470" stroke-width="1" />
                        <path d="M150 380 C 160 410 200 400 214 440" stroke-width="0.8" />
                        <path d="M48 450 C 70 440 90 470 112 474" stroke-width="0.7" />
                    </g>
                    <rect x="38" y="472" width="212" height="8" rx="1" fill="url(#aw-gold)" />
                    <path class="aw-spark" d="M66 4 l3 10 10 3 -10 3 -3 10 -3 -10 -10 -3 10 -3Z" fill="#fff8e6" />
                </svg>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Same spacing rhythm as the agenda above it */
.aw.section {
    padding-block: var(--section-gap);
}
.aw-deco {
    position: absolute;
    top: 0;
    right: 0;
    z-index: -1;
    width: min(60vw, 820px);
    height: auto;
    pointer-events: none;
}
.aw-pool {
    position: absolute;
    left: -12rem;
    bottom: -10rem;
    z-index: -1;
    width: 34rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(214 234 240 / 0.8), transparent);
    pointer-events: none;
}
.aw-layout {
    display: grid;
    gap: 2.5rem;
    align-items: center;
}
.aw-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 0.98;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    color: var(--night);
}
.aw-stop {
    display: inline-block;
    width: 0.16em;
    height: 0.16em;
    margin-left: 0.04em;
    border-radius: 0.02em;
    background: var(--gold);
}
.aw-cta {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    padding: 1.0625rem 1.75rem;
    border-radius: 0.5rem;
    background: var(--green);
    color: var(--white);
    font-size: 1.0625rem;
    font-weight: 600;
    box-shadow: 0 14px 30px -16px rgb(8 127 140 / 0.8);
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        transform var(--dur-hover) var(--ease-hover);
}
.aw-cta svg,
.aw-arrow svg {
    transition: transform var(--dur-hover) var(--ease-hover);
}
.aw-cta:hover {
    background: var(--delta);
    transform: translateY(-2px);
}
.aw-cta:hover svg,
.aw-card:hover .aw-arrow svg {
    transform: translateX(4px);
}

/* Cards */
.aw-cards {
    display: grid;
    gap: 1rem;
}
.aw-card {
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    height: 100%;
    padding: 2rem 1.75rem;
    border: 1px solid rgb(7 20 17 / 0.06);
    border-radius: 1rem;
    box-shadow: 0 1px 2px rgb(7 20 17 / 0.03);
    transition:
        transform var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover);
}
.aw-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 22px 44px -24px rgb(7 20 17 / 0.3);
}
.is-ice {
    background: linear-gradient(180deg, rgb(226 240 244 / 0.95), rgb(236 245 247 / 0.8));
    --icon-bg: #fff;
    --icon: #d5a02e;
}
.is-white {
    background: rgb(255 255 255 / 0.85);
    --icon-bg: #e3f1f4;
    --icon: var(--green);
}
.is-cream {
    background: linear-gradient(180deg, rgb(253 241 214 / 0.9), rgb(252 245 228 / 0.75));
    --icon-bg: #fff8ea;
    --icon: #d5a02e;
}
.aw-icon {
    display: grid;
    place-items: center;
    width: 4.5rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: var(--icon-bg);
    color: var(--icon);
}
.aw-card-title {
    margin-top: 1.5rem;
    font-size: 1.625rem;
    font-weight: 800;
    line-height: 1.12;
    letter-spacing: -0.01em;
    color: var(--night);
}
.aw-card-text {
    margin-top: 0.875rem;
    font-size: 1rem;
    line-height: 1.45;
    color: var(--ink-2);
}
.aw-arrow {
    display: grid;
    place-items: center;
    margin-top: 1.5rem;
    width: 3.25rem;
    aspect-ratio: 1;
    border: 1px solid rgb(7 20 17 / 0.12);
    border-radius: 50%;
    background: rgb(255 255 255 / 0.8);
    color: var(--delta);
}

/* Trophy */
.aw-trophy {
    justify-self: center;
    width: min(13rem, 52vw);
    pointer-events: none;
    will-change: transform, filter, opacity;
}
.aw-trophy svg {
    display: block;
    width: 100%;
    height: auto;
    overflow: visible;
}

@media (max-width: 639px) {
    .aw-cta {
        gap: 0.75rem;
        padding: 0.9375rem 1.25rem;
        font-size: 1rem;
        white-space: nowrap;
    }
}
@media (min-width: 640px) {
    .aw-cards {
        grid-template-columns: repeat(3, 1fr);
    }
    .aw-card-text {
        flex: 1;
    }
}
@media (min-width: 1024px) {
    .aw-layout {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.55fr) minmax(12rem, 0.62fr);
        gap: clamp(1.5rem, 2.5vw, 2.5rem);
    }
    .aw-h2 {
        font-size: clamp(2.25rem, 3.3vw, 3.875rem);
    }
    .aw-trophy {
        width: 100%;
        max-width: 19rem;
        align-self: end;
        margin-bottom: -2rem;
    }
}
@media (max-width: 1023px) {
    .aw-trophy {
        grid-row: 2;
    }
}
@media (prefers-reduced-motion: reduce) {
    .aw-card,
    .aw-cta {
        transition: none;
    }
}
</style>
