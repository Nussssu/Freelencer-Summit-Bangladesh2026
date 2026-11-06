<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AboutClosing from '@/components/about/AboutClosing.vue';
import Glyph from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SpeakerDialog from '@/components/speakers/SpeakerDialog.vue';
import SpeakerHoverCard from '@/components/speakers/SpeakerHoverCard.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Countdown from '@/components/ui/Countdown.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { EASE, gsap, useGsap } from '@/composables/motion';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { Highlight, Speaker } from '@/types/content';
import type { InnerPage, SectionHeading } from '@/types/inner';

// Grand Summit event page. Hero after gitex.com (cinematic photo, bold title, gold register CTA); at-a-glance strip,
// minimal highlights and speaker portraits after takeoffistanbul.com; agenda rows after londontechweek.com; exhibition
// block after GITEX; then venue and the dark teal closing band. Date, venue and city come from the summit settings.
const props = defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'glance' | 'highlights' | 'agenda' | 'speakers' | 'expo' | 'venue' | 'cta', SectionHeading>;
        highlights: Highlight[];
        sessions: { type: string; title: string; day: string; time: string; hall: string; speaker: string | null }[];
        sessionsSample: boolean;
        speakers: Speaker[];
        expo: { photo: string };
    };
}>();
const { summit, site } = useShared();
// Index of the speaker shown in the popup (null = closed).
const speaker = ref<number | null>(null);
const hero = ref<HTMLElement>();
const root = ref<HTMLElement>();
useReveal(hero);
useReveal(root);
useGsap(hero, (reduced) => {
    if (reduced) return;
    gsap.fromTo('[data-hero-img]', { scale: 1.1 }, { scale: 1, duration: 2.8, ease: EASE });
});

const register = computed(() => props.page.hero.actions?.find((a) => a.tone === 'gold') ?? site.value.ctas.primary.find((c) => c.id === 'register') ?? site.value.ctas.primary[0]);
const agendaLink = computed(() => props.page.hero.actions?.find((a) => a.tone !== 'gold'));
const when = computed(() =>
    summit.value.dateConfirmed
        ? new Date(summit.value.startsAt).toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric', timeZone: 'Asia/Dhaka' })
        : `Late November ${summit.value.edition}`,
);
const venue = computed(() => summit.value.venue || 'To be announced');
const glance = computed(() => [
    { label: 'Date', value: when.value },
    { label: 'Venue', value: venue.value },
    { label: 'Location', value: `${summit.value.city}, Bangladesh` },
]);
// One accent per session type, as the stage colours on the London Tech Week agenda
const accents = ['#087f8c', '#eab94b', '#2f8fdc', '#7b5cd6', '#e8506b'];
const accent = (type: string) => accents[[...type].reduce((h, c) => (h * 31 + c.charCodeAt(0)) >>> 0, 7) % accents.length];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <!-- Hero: cinematic stage photo, title bottom-left, one gold action -->
    <section ref="hero" class="relative isolate overflow-hidden bg-night text-paper" data-header="light" aria-labelledby="gs-title">
        <div v-if="page.hero.image" class="absolute inset-0 -z-10" aria-hidden="true">
            <img data-hero-img :src="page.hero.image" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover object-[50%_35%]" />
        </div>
        <div class="gs-hero-fade absolute inset-0 -z-10" aria-hidden="true" />
        <!-- shortcut: xl offset hard-codes the 2-row header (153px), same as PageHero. -->
        <div class="container-site flex min-h-[36rem] flex-col justify-end pt-[calc(var(--header-h)+4rem)] pb-16 md:min-h-[44rem] md:pb-24 xl:pt-[calc(153px+4rem)]">
            <p class="t-label text-gold" data-reveal="fade">{{ summit.name }} {{ summit.edition }} · {{ summit.city }}</p>
            <SplitHeading
                id="gs-title"
                as="h1"
                :text="page.hero.title"
                :emphasis="page.hero.emphasis"
                emphasis-class="text-gold"
                class="mt-5 max-w-[14ch] font-display text-[clamp(3rem,7.4vw,6.5rem)] leading-[0.9] font-black tracking-[-0.01em] [text-wrap:balance] uppercase"
            />
            <div class="mt-10 flex flex-wrap items-center gap-x-8 gap-y-4" data-reveal="fade">
                <InBtn :href="register.href" tone="gold">{{ register.label }}</InBtn>
                <Link v-if="agendaLink" :href="agendaLink.href" class="gs-link text-paper">{{ agendaLink.label }} →</Link>
            </div>
        </div>
    </section>

    <div ref="root" class="ground on-light">
        <!-- At a glance: edition numeral, then date / venue / location in one strip -->
        <section class="in-section" aria-labelledby="gs-glance">
            <div class="container-site grid items-center gap-10 lg:grid-cols-12">
                <div class="lg:col-span-4" data-reveal="fade">
                    <p class="font-display text-[clamp(5rem,12vw,9.5rem)] leading-[0.8] font-black text-green">’{{ summit.edition.slice(-2) }}</p>
                    <h2 id="gs-glance" class="t-label mt-4 text-ink-2">{{ content.headings.glance.title }}</h2>
                </div>
                <div class="lg:col-span-8">
                    <dl class="grid border-y border-night/12 sm:grid-cols-3">
                        <div v-for="(g, i) in glance" :key="g.label" class="py-6 sm:px-6 sm:py-8" :class="i > 0 && 'border-t border-night/12 sm:border-t-0 sm:border-l'" data-reveal="fade">
                            <dt class="t-label text-ink-2">{{ g.label }}</dt>
                            <dd class="mt-3 text-2xl leading-tight font-extrabold text-night md:text-[1.75rem]">{{ g.value }}</dd>
                        </div>
                    </dl>
                    <div v-if="summit.dateConfirmed" class="mt-6" data-reveal="fade"><Countdown :target="summit.startsAt" /></div>
                </div>
            </div>
        </section>

        <!-- Highlights: six numbered, text-only items -->
        <section class="in-section" aria-labelledby="gs-highlights">
            <div class="container-site">
                <SplitHeading id="gs-highlights" :text="content.headings.highlights.title" :emphasis="content.headings.highlights.emphasis ?? undefined" emphasis-class="text-green" class="in-h2" />
                <ol class="mt-12 grid gap-x-10 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(h, i) in content.highlights" :key="h.title" class="gs-hl group border-t border-night/12 py-8" data-reveal="fade">
                        <span class="font-display text-2xl font-black text-green">{{ String(i + 1).padStart(2, '0') }}</span>
                        <h3 class="mt-4 text-[1.375rem] leading-tight font-extrabold text-night">{{ h.title }}</h3>
                        <p class="mt-2 max-w-[30ch] text-ink-2">{{ h.text }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Agenda preview: time, coloured type, title, hall -->
        <section class="in-section" aria-labelledby="gs-agenda">
            <div class="container-site">
                <SectionHead :id="'gs-agenda'" :heading="content.headings.agenda">
                    <span v-if="content.sessionsSample" class="in-pill">Provisional</span>
                    <InBtn href="/agenda">View full agenda</InBtn>
                </SectionHead>
                <ol class="overflow-hidden rounded-2xl bg-white ring-1 ring-night/8">
                    <li v-for="s in content.sessions" :key="s.title" class="grid gap-2 border-b border-night/8 px-5 py-5 last:border-b-0 sm:grid-cols-[9rem_1fr_auto] sm:items-center sm:gap-8 sm:px-8 sm:py-6" data-reveal="fade">
                        <div>
                            <p class="text-lg font-extrabold text-night">{{ s.time }}</p>
                            <p class="in-meta">{{ s.day }}</p>
                        </div>
                        <div class="min-w-0">
                            <p class="flex items-center gap-2 text-xs font-bold tracking-wide uppercase" :style="{ color: accent(s.type) }">{{ s.type }}</p>
                            <h3 class="mt-1.5 text-lg leading-snug font-extrabold text-night sm:text-xl">{{ s.title }}</h3>
                        </div>
                        <p class="in-meta sm:text-right">{{ s.hall }}</p>
                    </li>
                </ol>
            </div>
        </section>

        <!-- Featured speakers: portrait cards; a click opens the speaker popup (no profile pages) -->
        <section class="in-section" aria-labelledby="gs-speakers">
            <div class="container-site">
                <SectionHead :id="'gs-speakers'" :heading="content.headings.speakers"><InBtn href="/speakers" tone="ghost">All speakers</InBtn></SectionHead>
                <ul class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
                    <li v-for="(s, i) in content.speakers" :key="s.slug" data-reveal="fade"><SpeakerHoverCard :person="s" @click="speaker = i" /></li>
                </ul>
                <SpeakerDialog v-model:index="speaker" :people="content.speakers" />
            </div>
        </section>

        <!-- Exhibition & networking: large hall photo, heading and exhibition actions -->
        <section class="in-section" aria-labelledby="gs-expo">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-12">
                <div class="in-media is-round aspect-[16/10] lg:col-span-7 lg:aspect-[4/3]" data-reveal="clip"><img :src="content.expo.photo" alt="" loading="lazy" /></div>
                <div class="lg:col-span-5">
                    <SplitHeading id="gs-expo" :text="content.headings.expo.title" :emphasis="content.headings.expo.emphasis ?? undefined" emphasis-class="text-green" class="in-h2" />
                    <div class="mt-8 flex flex-wrap gap-3" data-reveal="fade">
                        <InBtn href="/exhibition/apply" tone="gold">Exhibit at Summit</InBtn>
                        <InBtn href="/exhibition" tone="ghost">Explore the exhibition</InBtn>
                    </div>
                </div>
            </div>
        </section>

        <!-- Venue & location: city, date, venue -->
        <section class="in-section" aria-labelledby="gs-venue">
            <div class="container-site grid gap-10 lg:grid-cols-12 lg:items-end">
                <div class="lg:col-span-5">
                    <SplitHeading id="gs-venue" :text="content.headings.venue.title" :emphasis="content.headings.venue.emphasis ?? undefined" emphasis-class="text-green" class="in-h2" />
                    <p class="mt-6 font-display text-[clamp(4rem,10vw,8rem)] leading-[0.85] font-black text-night uppercase" data-reveal="fade">{{ summit.city }}</p>
                </div>
                <dl class="divide-y divide-night/12 border-y border-night/12 lg:col-span-7">
                    <div class="flex items-center gap-5 py-6" data-reveal="fade">
                        <span class="in-disc tone-teal w-12!"><Glyph name="calendar" /></span>
                        <div><dt class="in-meta">When</dt><dd class="text-xl font-extrabold text-night">{{ when }}</dd></div>
                    </div>
                    <div class="flex items-center gap-5 py-6" data-reveal="fade">
                        <span class="in-disc tone-sun w-12!"><Glyph name="building" /></span>
                        <div><dt class="in-meta">Venue</dt><dd class="text-xl font-extrabold text-night">{{ venue }}</dd></div>
                    </div>
                    <div class="flex items-center gap-5 py-6" data-reveal="fade">
                        <span class="in-disc tone-sky w-12!"><Glyph name="pin" /></span>
                        <div><dt class="in-meta">City</dt><dd class="text-xl font-extrabold text-night">{{ summit.city }}, Bangladesh</dd></div>
                    </div>
                </dl>
            </div>
        </section>

        <AboutClosing :heading="content.headings.cta" :page="page">
            <CtaLink :href="register.href" class="!px-7 !py-4">{{ register.label }}</CtaLink>
        </AboutClosing>
    </div>
</template>

<style scoped>
.gs-hero-fade {
    background:
        linear-gradient(0deg, rgb(7 20 17 / 0.95) 0%, rgb(7 20 17 / 0.55) 45%, rgb(7 20 17 / 0.2) 75%, rgb(7 20 17 / 0.45) 100%),
        linear-gradient(90deg, rgb(6 69 76 / 0.55), transparent 65%);
}
.gs-link {
    border-bottom: 1.5px solid rgb(255 255 255 / 0.5);
    padding-bottom: 0.2rem;
    font-weight: 700;
    transition: border-color 0.3s ease;
}
.gs-link:hover {
    border-color: var(--gold);
}
.gs-hl span {
    display: inline-block;
    transition: transform 0.4s cubic-bezier(0.2, 0.7, 0.2, 1);
}
.gs-hl:hover span {
    transform: translateX(6px);
}
/* The About page's ground: ice blue with soft teal and summit-gold glows alternating down the page */
.ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 30%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 62%, rgb(8 127 140 / 0.18), transparent 70%),
        radial-gradient(900px 640px at 100% 92%, rgb(234 185 75 / 0.32), transparent 70%),
        var(--mist);
}
</style>
