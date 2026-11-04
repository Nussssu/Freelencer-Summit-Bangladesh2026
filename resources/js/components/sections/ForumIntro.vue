<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { EASE, gsap, reveal, ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { utm, withUtm } from '@/composables/useUtm';

// After exhibition & sponsorship: eyebrow, forum name as a two-line headline, three benefits with tinted icons,
// a teal "join" pill and an underlined registration link. Right: a community photo bleeding to the page edge that
// wipes open from the right, with a gold curve and dots.
const { t } = useI18n();
const root = ref<HTMLElement>();
const photo = ref<HTMLElement>();
useReveal(root);

const lines = computed(() => {
    const [first, ...rest] = t('sections.forum').split(' ');
    return rest.length ? [first, rest.join(' ')] : [first];
});
const items = [
    { id: 'community', tone: 'is-teal' },
    { id: 'mentorship', tone: 'is-gold' },
    { id: 'opportunities', tone: 'is-ice' },
];

useGsap(root, (reduced) => {
    const el = photo.value;
    if (reduced || !el) return;
    const frame = el.querySelector('.fm-frame')!;
    const img = el.querySelector('img')!;
    const curve = el.querySelector<SVGPathElement>('.fm-curve')!;
    const dots = root.value!.querySelectorAll('.fm-dots i');
    const len = curve.getTotalLength();

    gsap.set(frame, { clipPath: 'inset(0% 0% 0% 100%)' });
    gsap.set(img, { scale: 1.25 });
    gsap.set(curve, { strokeDasharray: len, strokeDashoffset: len });
    gsap.set(dots, { scale: 0, opacity: 0 });

    ScrollTrigger.create({
        trigger: el,
        start: 'top 80%',
        once: true,
        onEnter: () => reveal(() => {
            const tl = gsap.timeline({ defaults: { ease: EASE } });
            tl.to(frame, { clipPath: 'inset(0% 0% 0% 0%)', duration: 1.4, ease: 'power3.inOut', clearProps: 'clipPath' })
                .to(img, { scale: 1, duration: 2, clearProps: 'transform' }, 0)
                .to(dots, { scale: 1, opacity: 1, duration: 0.5, stagger: { each: 0.05, from: 'start', grid: [3, 3] } }, 0.4)
                .to(curve, { strokeDashoffset: 0, duration: 1.8, ease: 'power2.inOut' }, 0.7);
        }),
    });
    // A slow drift of the photo inside its frame while the section passes
    ScrollTrigger.create({
        trigger: root.value,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true,
        onUpdate: (st) => gsap.set(img, { yPercent: (st.progress - 0.5) * -8 }),
    });
});
</script>

<template>
    <section ref="root" aria-labelledby="fm-title" class="fm on-light flow section relative z-[1] overflow-hidden">
        <span class="fm-orb" aria-hidden="true" />
        <span class="fm-pool" aria-hidden="true" />

        <div class="container-site fm-layout">
            <div class="fm-intro">
                <h2 id="fm-title" class="fm-h2" data-reveal="lines" :aria-label="t('sections.forum')">
                    <span v-for="(l, i) in lines" :key="i" class="block" aria-hidden="true">
                        <template v-for="(w, j) in l.split(' ')" :key="j"><span class="line-mask"><span class="line-inner">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                </h2>

                <ul class="fm-items">
                    <li v-for="it in items" :key="it.id" class="fm-item" data-reveal="fade">
                        <span class="fm-icon" :class="it.tone" aria-hidden="true">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                                <template v-if="it.id === 'community'"><circle cx="9" cy="8" r="3" /><path d="M3.5 19a5.5 5.5 0 0 1 11 0" /><circle cx="17" cy="9" r="2.3" /><path d="M16 14.2a4.5 4.5 0 0 1 5 4.8" /></template>
                                <template v-else-if="it.id === 'mentorship'"><circle cx="9" cy="7.5" r="3" /><path d="M3 20a6 6 0 0 1 9-5.2" /><rect x="13" y="12" width="8" height="6" rx="1.2" /><path d="M15 20l1.5-2M15.5 14.5h3M15.5 16h2" /></template>
                                <template v-else><rect x="3" y="7" width="18" height="13" rx="2" /><path d="M9 7V5a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2M3 12h18" /><rect x="10.5" y="11" width="3" height="2.5" rx=".5" /></template>
                            </svg>
                        </span>
                        <span>
                            <span class="fm-item-title">{{ t(`sections.fmItems.${it.id}.title`) }}</span>
                            <span class="fm-item-text">{{ t(`sections.fmItems.${it.id}.text`) }}</span>
                        </span>
                    </li>
                </ul>

                <div class="mt-10 flex flex-wrap items-center gap-x-12 gap-y-5" data-reveal="fade">
                    <Link :href="withUtm('/forum', utm)" class="fm-join">
                        {{ t('sections.fmJoin') }}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </Link>
                </div>
            </div>

            <div ref="photo" class="fm-photo">
                <span class="fm-dots" aria-hidden="true"><i v-for="n in 9" :key="n" /></span>
                <div class="fm-frame"><img src="/media/photos/forum-community.jpg" alt="" loading="lazy" /></div>
                <svg class="fm-curve-svg" viewBox="0 0 500 220" fill="none" aria-hidden="true">
                    <path class="fm-curve" d="M20 150 C 120 230 300 230 420 140 S 520 10 500 -20" stroke="#e8c879" stroke-width="2" stroke-linecap="round" />
                </svg>
            </div>
        </div>
    </section>
</template>

<style scoped>
.fm.section {
    padding-block: var(--section-gap);
}
/* A big pale disc behind the top of the photo and a pool bottom left */
.fm-orb {
    position: absolute;
    top: -22rem;
    left: 38%;
    z-index: -1;
    width: 44rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(218 236 242 / 0.9), rgb(218 236 242 / 0.4) 75%, transparent);
    pointer-events: none;
}
.fm-pool {
    position: absolute;
    left: -14rem;
    bottom: -16rem;
    z-index: -1;
    width: 34rem;
    aspect-ratio: 1;
    border-radius: 50%;
    background: radial-gradient(closest-side, rgb(214 234 240 / 0.8), transparent);
    pointer-events: none;
}
.fm-layout {
    display: grid;
    gap: 3.5rem;
    align-items: center;
}
.fm-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    color: var(--night);
}

/* Benefits */
.fm-items {
    display: grid;
    gap: 1.5rem;
    margin-top: 2.25rem;
}
.fm-item {
    display: flex;
    align-items: center;
    gap: 1.5rem;
}
.fm-icon {
    display: grid;
    place-items: center;
    flex-shrink: 0;
    width: 4.25rem;
    aspect-ratio: 1;
    border-radius: 50%;
    color: var(--night);
}
.is-teal {
    background: #e2f2f2;
}
.is-gold {
    background: #fcefd9;
}
.is-ice {
    background: #e5eef6;
}
.fm-item-title {
    display: block;
    font-size: 1.25rem;
    font-weight: 700;
    color: var(--night);
}
.fm-item-text {
    display: block;
    margin-top: 0.25rem;
    color: var(--ink-2);
}

/* CTAs */
.fm-join {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    padding: 1.125rem 2.25rem;
    border-radius: 999px;
    background: var(--green);
    color: var(--white);
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    box-shadow: 0 16px 32px -16px rgb(8 127 140 / 0.85);
    transition:
        background-color var(--dur-hover) var(--ease-hover),
        transform var(--dur-hover) var(--ease-hover);
}
.fm-join:hover {
    background: var(--delta);
    transform: translateY(-2px);
}
.fm-join svg {
    transition: transform var(--dur-hover) var(--ease-hover);
}
.fm-join:hover svg {
    transform: translateX(4px);
}

/* Photo and decoration */
.fm-photo {
    position: relative;
}
.fm-frame {
    overflow: hidden;
    aspect-ratio: 1.31;
    border-radius: 1.5rem;
}
.fm-frame img {
    /* taller than the frame so the scroll drift never shows an edge */
    width: 100%;
    height: 112%;
    margin-top: -6%;
    object-fit: cover;
}
.fm-dots {
    position: absolute;
    top: 1.5rem;
    left: -3rem;
    display: grid;
    grid-template-columns: repeat(3, 4px);
    gap: 1rem;
}
.fm-dots i {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #e3c27a;
}
.fm-curve-svg {
    position: absolute;
    right: 0;
    bottom: -4.5rem;
    width: 75%;
    height: auto;
    pointer-events: none;
}

@media (max-width: 639px) {
    .fm-join {
        padding: 1rem 1.75rem;
    }
    .fm-dots {
        display: none;
    }
}
/* Desktop: the photo runs to the right edge of the page with only its left corners rounded */
@media (min-width: 1024px) {
    .fm-layout {
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr);
        gap: clamp(2.5rem, 4vw, 4.5rem);
    }
    .fm-h2 {
        font-size: clamp(2.25rem, 3.15vw, 4.25rem);
        white-space: nowrap;
    }
    .fm-photo {
        --bleed: calc(max(0px, (100vw - var(--container-site)) / 2) + 32px);
        margin-right: calc(-1 * var(--bleed));
    }
    .fm-frame {
        aspect-ratio: auto;
        height: clamp(30rem, 44vw, 42rem);
        border-radius: 3rem 0 0 3rem;
    }
}
@media (prefers-reduced-motion: reduce) {
    .fm-join {
        transition: none;
    }
}
</style>
