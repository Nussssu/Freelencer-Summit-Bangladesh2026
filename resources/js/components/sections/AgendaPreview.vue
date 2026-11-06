<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { useI18n } from 'vue-i18n';
import { EASE, gsap, reveal, ScrollTrigger, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import { utm, withUtm } from '@/composables/useUtm';
import type { Agenda, Heading } from '@/types/content';

// Section 8. Left, top-aligned with the first card: eyebrow, two-line headline (emphasis in teal) and gold CTA. Right: the
// first four sessions as cards with a coloured type bar, day, time, type tag and title, speaker and hall.
const props = defineProps<{ agenda: Agenda; heading: Heading }>();
const { t } = useI18n();
const { site } = useShared();
const root = ref<HTMLElement>();
const list = ref<HTMLElement>();
useReveal(root);

const cta = computed(() => site.value.ctas.secondary.find((c) => c.id === 'agenda')!);
const lines = computed(() => {
    const { title, emphasis } = props.heading;
    const i = title.indexOf(emphasis);
    return i < 0 ? [title] : [title.slice(0, i).trim(), emphasis].filter(Boolean);
});
const rows = computed(() => props.agenda.sessions.slice(0, 4).map((s, i) => ({ ...s, i })));

// Accent colour per session type, by keyword so new CMS types still get one
const accents: [string, string][] = [
    ['keynote', '#087f8c'],
    ['workshop', '#2f6fe4'],
    ['panel', '#d18b14'],
    ['ai', '#8b5cf6'],
    ['career', '#0e9f6e'],
    ['marketplace', '#0b8bd9'],
    ['entrepreneur', '#7c3aed'],
    ['award', '#c98a10'],
];
const accent = (type: string) => accents.find(([k]) => new RegExp(`\\b${k}`, 'i').test(type))?.[1] ?? '#087f8c';

// Dummy portraits until speakers are assigned to sessions (photos already on the site)
const faces = ['musnad-e-ahmed.png', 'emrazina-islam.jpg', 'saber-shah.jpg', 'shuvasish-bhowmick.jpg'].map((f) => `/media/speakers/${f}`);

// "10:00–10:45" → ["10:00 AM", "10:45 AM"]
const clock = (hm: string) => {
    const [h, m] = hm.trim().split(':').map(Number);
    return `${String(h % 12 || 12).padStart(2, '0')}:${String(m).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
};
const times = (range: string) => {
    const [a, b] = range.split(/[–-]/);
    return b ? [clock(a), clock(b)] : [range];
};

// Cards slide in from the right one after another; the type bar grows down and the avatar pops
useGsap(root, (reduced) => {
    const l = list.value;
    if (reduced || !l) return;
    const cards = [...l.children] as HTMLElement[];
    gsap.set(cards, { x: 60, opacity: 0 });
    gsap.set(l.querySelectorAll('.ag-bar'), { scaleY: 0 });
    gsap.set(l.querySelectorAll('.ag-avatar'), { scale: 0.6, opacity: 0 });
    ScrollTrigger.create({
        trigger: l,
        start: 'top 85%',
        once: true,
        onEnter: () => reveal(() => {
            gsap.to(cards, { x: 0, opacity: 1, duration: 1, ease: EASE, stagger: 0.12, clearProps: 'transform,opacity' });
            gsap.to(l.querySelectorAll('.ag-bar'), { scaleY: 1, duration: 0.9, ease: EASE, stagger: 0.12, delay: 0.2, clearProps: 'transform' });
            gsap.to(l.querySelectorAll('.ag-avatar'), { scale: 1, opacity: 1, duration: 0.8, ease: 'back.out(1.7)', stagger: 0.12, delay: 0.35, clearProps: 'transform,opacity' });
        }),
    });
});
</script>

<template>
    <section ref="root" data-section="8" aria-labelledby="ag-title" class="ag on-light flow section relative z-[1] overflow-hidden">
        <div class="container-site ag-layout">
            <!-- Left: intro -->
            <div class="ag-intro">
                <h2 id="ag-title" class="ag-h2" data-reveal="lines" :aria-label="heading.title">
                    <span v-for="(l, i) in lines" :key="i" class="block" aria-hidden="true">
                        <template v-for="(w, j) in l.split(' ')" :key="j"><span class="line-mask"><span class="line-inner" :class="i === lines.length - 1 && lines.length > 1 && 'text-green'">{{ w }}</span></span>{{ ' ' }}</template>
                    </span>
                </h2>
                <div class="mt-8" data-reveal="fade">
                    <Link :href="withUtm(cta.href, utm)" class="ag-cta">
                        {{ cta.label }}
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </Link>
                </div>
            </div>

            <!-- Right: sessions -->
            <ol ref="list" class="ag-list">
                <li v-for="s in rows" :key="s.i" class="ag-card" :style="{ '--accent': accent(s.type) }">
                    <span class="ag-bar" aria-hidden="true" />
                    <p class="ag-day">{{ s.day }}</p>
                    <p class="ag-time"><span v-for="(x, j) in times(s.time)" :key="j" class="block"><span v-if="j" class="ag-dash">–</span>{{ x }}</span></p>
                    <div class="ag-body">
                        <span class="ag-tag">{{ s.type }}</span>
                        <h3 class="ag-title">{{ s.title }}</h3>
                    </div>
                    <div class="ag-who">
                        <img :src="faces[s.i % faces.length]" alt="" loading="lazy" class="ag-avatar" />
                        <span class="ag-name">{{ s.speaker ?? t('sections.speakerTba') }}</span>
                    </div>
                    <div class="ag-where">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-6.5-5.6-6.5-11a6.5 6.5 0 0 1 13 0c0 5.4-6.5 11-6.5 11Z" /><circle cx="12" cy="10" r="2.3" /></svg>
                        <span class="ag-hall">{{ s.hall }}</span>
                    </div>
                </li>
            </ol>
        </div>
    </section>
</template>

<style scoped>
.ag-layout {
    display: grid;
    gap: clamp(2.5rem, 4vw, 3.5rem);
}
.ag-h2 {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1;
    letter-spacing: -0.02em;
    font-size: clamp(2.5rem, 5vw, 4.25rem);
    color: var(--night);
}
.ag-cta {
    display: inline-flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem 1.75rem;
    border-radius: 0.5rem;
    background: linear-gradient(180deg, #e9c27a, #d7a54d);
    color: var(--night);
    font-size: 1.125rem;
    font-weight: 700;
    box-shadow: 0 14px 30px -16px rgb(201 138 16 / 0.8);
    transition:
        transform var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover);
}
.ag-cta svg {
    transition: transform var(--dur-hover) var(--ease-hover);
}
.ag-cta:hover {
    transform: translateY(-2px);
    box-shadow: 0 20px 36px -16px rgb(201 138 16 / 0.9);
}
.ag-cta:hover svg {
    transform: translateX(4px);
}

/* Session cards: stacked on phones, one row of columns from md */
.ag-list {
    display: grid;
    gap: 1rem;
}
.ag-card {
    position: relative;
    display: grid;
    grid-template-columns: auto 1fr;
    grid-template-areas:
        'day time'
        'body body'
        'who where';
    align-items: center;
    gap: 1rem 1.25rem;
    padding: 1.25rem 1.25rem 1.25rem 1.5rem;
    border-radius: 0.875rem;
    background: rgb(255 255 255 / 0.85);
    box-shadow:
        0 1px 2px rgb(7 20 17 / 0.04),
        0 16px 36px -24px rgb(7 20 17 / 0.25);
    overflow: hidden;
    transition:
        transform var(--dur-hover) var(--ease-hover),
        box-shadow var(--dur-hover) var(--ease-hover);
}
.ag-card:hover {
    transform: translateY(-3px);
    box-shadow:
        0 1px 2px rgb(7 20 17 / 0.04),
        0 24px 44px -24px rgb(7 20 17 / 0.32);
}
.ag-bar {
    position: absolute;
    inset: 0 auto 0 0;
    width: 5px;
    background: var(--accent);
    transform-origin: top;
}
.ag-day {
    grid-area: day;
    font-weight: 800;
    text-transform: uppercase;
    white-space: nowrap;
    color: var(--night);
}
.ag-time {
    grid-area: time;
    font-size: 0.9375rem;
    white-space: nowrap;
    color: var(--night);
    font-variant-numeric: tabular-nums;
}
.ag-dash {
    display: inline-block;
    margin-right: 0.3em;
    transform: translateY(-0.12em);
}
.ag-body {
    grid-area: body;
    min-width: 0;
}
.ag-tag {
    display: inline-block;
    padding: 0.2rem 0.6rem;
    border-radius: 0.3rem;
    background: color-mix(in srgb, var(--accent) 12%, transparent);
    color: var(--accent);
    font-size: 0.75rem;
    font-weight: 600;
    letter-spacing: 0.06em;
    text-transform: uppercase;
}
.ag-title {
    margin-top: 0.5rem;
    font-size: 1.25rem;
    font-weight: 700;
    line-height: 1.2;
    color: var(--night);
}
.ag-who {
    grid-area: who;
    display: flex;
    align-items: center;
    gap: 0.875rem;
    min-width: 0;
}
.ag-avatar {
    flex-shrink: 0;
    width: 3.5rem;
    aspect-ratio: 1;
    border-radius: 50%;
    object-fit: cover;
    object-position: center 20%;
    background: var(--ice);
    box-shadow:
        0 0 0 3px #fff,
        0 0 0 4px color-mix(in srgb, var(--accent) 35%, transparent);
}
.ag-name {
    min-width: 0;
    line-height: 1.25;
    overflow-wrap: anywhere;
}
.ag-name,
.ag-hall {
    font-weight: 600;
    color: var(--night);
}
.ag-where {
    grid-area: where;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 0;
    color: var(--night);
}
.ag-where svg {
    flex-shrink: 0;
}
.ag-hall {
    min-width: 0;
    line-height: 1.25;
    overflow-wrap: anywhere;
}
@media (min-width: 768px) {
    .ag-card {
        grid-template-columns: 4.5rem 8rem minmax(0, 1fr) 13rem 11rem;
        grid-template-areas: 'day time body who where';
        gap: 0;
        padding: 1.5rem 1.5rem 1.5rem 1.75rem;
    }
    .ag-card > :not(.ag-bar, .ag-day) {
        align-self: stretch;
        padding-inline: 1.25rem;
        border-left: 1px solid rgb(7 20 17 / 0.08);
    }
    .ag-time,
    .ag-body {
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: flex-start;
    }
}
/* Two columns from 1280: the intro's top lines up with the first session card */
@media (min-width: 1280px) {
    .ag-layout {
        grid-template-columns: minmax(18rem, 0.8fr) 2.7fr;
        gap: clamp(2.5rem, 4vw, 4.5rem);
        align-items: start;
    }
    /* the eyebrow sits above the column, so the headline's top lines up with the first card */
    .ag-intro {
        position: relative;
    }
    .ag-eyebrow {
        position: absolute;
        bottom: calc(100% + 1.25rem);
        left: 0;
    }
    .ag-h2 {
        margin-top: -0.06em;
        font-size: clamp(2.5rem, 3.6vw, 3.75rem);
    }
}
@media (prefers-reduced-motion: reduce) {
    .ag-card,
    .ag-cta {
        transition: none;
    }
}
</style>
