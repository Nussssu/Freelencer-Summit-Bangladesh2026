<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Breadcrumbs from '@/components/inner/Breadcrumbs.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useAgendaCalendar } from '@/composables/useAgendaCalendar';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { AgendaSessionContent, InnerPage } from '@/types/inner';

// One agenda session's own page: photo hero with breadcrumbs (title only, no intro), the session brief with its facts,
// "what you'll learn" and "who should attend", a sticky at-a-glance card, the speaker, related sessions and the closing band.
const props = defineProps<{ page: InnerPage; content: AgendaSessionContent }>();
const { t, d } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const s = computed(() => props.content.session);
// "10:00–10:45" → "10:00 AM – 10:45 AM"
const clock = (hm: string) => {
    const [h, m] = hm.trim().split(':').map(Number);
    return `${((h + 11) % 12) + 1}:${String(m).padStart(2, '0')} ${h < 12 ? 'AM' : 'PM'}`;
};
const timeOf = (time: string) => time.split('–').map(clock).join(' – ');
const minutes = computed(() => {
    const [from, to] = s.value.time.split('–').map((p) => p.trim().split(':').map(Number));
    return from && to ? to[0] * 60 + to[1] - (from[0] * 60 + from[1]) : 0;
});
// Day N is starts_at + N-1, shown only once the date is confirmed.
const date = computed(() => {
    if (!summit.value.dateConfirmed) return t('sections.dateTbc');
    const at = new Date(summit.value.startsAt);
    at.setDate(at.getDate() + Math.max(0, props.content.days.indexOf(s.value.day)));
    return d(at, { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
});
const speaker = computed(() => s.value.speaker ?? t('sections.speakerTba'));

const facts = computed<{ icon: GlyphName; label: string; value: string }[]>(() => [
    { icon: 'calendar', label: t('inner.agenda.date'), value: date.value },
    { icon: 'clock', label: t('inner.agenda.time'), value: timeOf(s.value.time) },
    { icon: 'pin', label: t('inner.agenda.hall'), value: s.value.hall },
    { icon: 'layers', label: t('inner.agenda.track'), value: s.value.track ?? s.value.type },
    { icon: 'user', label: t('inner.agenda.speaker'), value: speaker.value },
    { icon: 'grid', label: t('inner.agenda.type'), value: s.value.type },
]);
const glance = computed<{ icon: GlyphName; label: string; value: string }[]>(() => [
    { icon: 'calendar', label: t('inner.agenda.dayDate'), value: `${s.value.day} — ${date.value}` },
    { icon: 'clock', label: t('inner.agenda.time'), value: minutes.value ? `${timeOf(s.value.time)} (${t('inner.agenda.minutes', { n: minutes.value })})` : timeOf(s.value.time) },
    { icon: 'pin', label: t('inner.agenda.venue'), value: s.value.hall },
    { icon: 'user', label: t('inner.agenda.speaker'), value: speaker.value },
    { icon: 'layers', label: t('inner.agenda.format'), value: s.value.type },
]);
const { sessionCal } = useAgendaCalendar();
const calendar = computed(() => sessionCal(s.value, props.content.days));
// The title's last word takes the teal emphasis, as in the agenda's headings.
const emphasis = computed(() => s.value.title.split(' ').at(-1) ?? '');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />

    <!-- Photo band: breadcrumbs and the page title, no intro line -->
    <section class="relative isolate overflow-hidden bg-night text-paper" data-header="light" aria-labelledby="session-hero">
        <div class="absolute inset-0 -z-10" aria-hidden="true">
            <img :src="page.hero.image ?? '/media/photos/agenda-hero.jpg'" alt="" fetchpriority="high" decoding="async" class="h-full w-full object-cover" />
        </div>
        <div class="hero-glow absolute inset-0 -z-10" aria-hidden="true" />
        <div class="container-site pt-[calc(var(--header-h)+2.5rem)] pb-14 md:pb-20 xl:pt-[calc(153px+2.5rem)]">
            <Breadcrumbs :items="page.crumbs" class="crumbs-light" />
            <SplitHeading id="session-hero" as="h1" :text="page.hero.title" :emphasis="page.hero.emphasis" emphasis-class="text-gold" class="mt-4 text-center font-display text-[clamp(2.25rem,5vw,3.75rem)] leading-[1.05] font-extrabold" />
            <span class="mx-auto mt-4 block h-1 w-16 rounded-full bg-gold" aria-hidden="true" />
        </div>
    </section>

    <div ref="root" class="ground">
        <section class="in-section" aria-labelledby="session-title">
            <div class="container-site grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem] lg:items-start xl:grid-cols-[minmax(0,1fr)_24rem]">
                <!-- Left column holds under the header while the taller glance card scrolls; once their bottoms meet they scroll on together.
                     shortcut: xl offset hard-codes the 2-row header (153px), same as NewsHero. -->
                <div class="lg:sticky lg:top-[calc(var(--header-h)+1.5rem)] xl:top-[calc(153px+1.5rem)]">
                    <SplitHeading id="session-title" :text="s.title" :emphasis="emphasis" emphasis-class="text-green" class="in-h2 text-[clamp(2rem,3.6vw,3rem)]!" />

                    <dl class="mt-6 grid border-t border-night/10 sm:grid-cols-2">
                        <div v-for="(f, i) in facts" :key="f.label" class="flex items-center gap-4 border-b border-night/10 py-4" :class="i % 2 === 0 ? 'sm:border-r sm:pr-6' : 'sm:pl-6'">
                            <dt class="shrink-0 text-green"><Glyph :name="f.icon" class="size-7" /><span class="sr-only">{{ f.label }}</span></dt>
                            <dd class="min-w-0">
                                <span class="block text-sm text-ink-2" aria-hidden="true">{{ f.label }}</span>
                                <span class="block font-bold text-night">{{ f.value }}</span>
                            </dd>
                        </div>
                    </dl>

                    <div class="mt-6 grid gap-4 md:grid-cols-2">
                        <div v-if="s.learn?.length" class="rounded-2xl bg-green/[0.07] p-5" data-reveal="fade">
                            <h2 class="flex items-center gap-3 text-lg font-bold text-night"><Glyph name="calendar" class="size-6 text-green" />{{ t('inner.agenda.learn') }}</h2>
                            <ul class="mt-3 grid gap-2.5 text-sm text-ink-2">
                                <li v-for="l in s.learn" :key="l" class="flex items-start gap-2.5"><Glyph name="check" class="mt-0.5 size-4.5 shrink-0 text-green" />{{ l }}</li>
                            </ul>
                        </div>
                        <div v-if="s.audience?.length" class="rounded-2xl bg-green/[0.07] p-5" data-reveal="fade">
                            <h2 class="flex items-center gap-3 text-lg font-bold text-night"><Glyph name="people" class="size-6 text-green" />{{ t('inner.agenda.audience') }}</h2>
                            <ul class="mt-3 grid gap-2.5 text-sm text-ink-2">
                                <li v-for="a in s.audience" :key="a" class="flex items-start gap-2.5"><Glyph name="check" class="mt-0.5 size-4.5 shrink-0 text-green" />{{ a }}</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Session at a Glance -->
                <aside class="rounded-2xl border border-night/10 bg-white p-3 shadow-[0_24px_50px_-30px_rgb(7_20_17/0.35)]" aria-labelledby="session-glance">
                    <h2 id="session-glance" class="flex items-center gap-3 rounded-xl bg-green/[0.07] px-4 py-3.5 text-xl font-bold text-night"><Glyph name="calendar" class="size-7 text-green" />{{ t('inner.agenda.glance') }}</h2>
                    <dl class="px-3">
                        <div v-for="g in glance" :key="g.label" class="flex items-start gap-4 border-b border-night/10 py-3.5 last:border-b-0">
                            <dt class="shrink-0 pt-0.5 text-green"><Glyph :name="g.icon" class="size-6" /><span class="sr-only">{{ g.label }}</span></dt>
                            <dd class="min-w-0 text-sm">
                                <span class="block font-bold text-night" aria-hidden="true">{{ g.label }}</span>
                                <span class="block text-ink-2">{{ g.value }}</span>
                            </dd>
                        </div>
                    </dl>
                    <div class="grid gap-3 p-3">
                        <a :href="calendar" target="_blank" rel="noopener" class="in-btn is-ghost justify-center"><Glyph name="calendar" class="size-5" />{{ t('inner.agenda.addCalendar') }}</a>                        <Link href="/agenda" class="in-btn is-ghost justify-center">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6" /></svg>
                            {{ t('inner.agenda.back') }}
                        </Link>
                        <Link href="/register" class="in-btn is-gold justify-center">
                            {{ t('inner.agenda.registerSummit') }}
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                        </Link>
                    </div>
                </aside>
            </div>
        </section>

        <!-- Meet the Speaker -->
        <section class="pb-12 md:pb-16" aria-labelledby="session-speaker">
            <div class="container-site">
                <SplitHeading id="session-speaker" :text="content.headings.speaker.title" :emphasis="content.headings.speaker.emphasis ?? undefined" emphasis-class="text-green" class="in-h2 text-[clamp(1.75rem,3vw,2.5rem)]!" />
                <div class="mt-5 grid items-center gap-6 rounded-2xl border border-night/10 bg-white/80 p-4 md:grid-cols-[16rem_1fr] md:p-5" data-reveal="fade">
                    <div class="grid aspect-[16/10] place-items-center rounded-xl bg-green/10 text-green"><Glyph name="user" class="size-16" /></div>
                    <div>
                        <h3 class="text-2xl font-bold text-night">{{ speaker }}</h3>
                        <p v-if="s.track" class="mt-2 border-l-2 border-green pl-4 font-bold text-green">{{ s.track }}</p>
                        <p class="in-body mt-3 max-w-3xl">{{ t('inner.agenda.speakerTbaBody') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Related Sessions -->
        <section v-if="content.related.length" class="pb-16 md:pb-20" aria-labelledby="session-related">
            <div class="container-site">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <SplitHeading id="session-related" :text="content.headings.related.title" :emphasis="content.headings.related.emphasis ?? undefined" emphasis-class="text-green" class="in-h2 text-[clamp(1.75rem,3vw,2.5rem)]!" />
                    <Link href="/agenda" class="in-link font-semibold text-green">
                        {{ t('inner.agenda.fullAgenda') }}
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                    </Link>
                </div>
                <ul class="mt-6 grid gap-4 md:grid-cols-3">
                    <li v-for="r in content.related" :key="r.slug" data-reveal="fade">
                        <Link :href="r.href" class="group relative flex h-full flex-col rounded-2xl border border-night/10 bg-white p-5 transition-colors hover:border-green">
                            <span class="self-start rounded-full bg-green/10 px-3 py-1 text-xs font-semibold text-night">{{ r.type }}</span>
                            <span class="absolute top-5 right-5 grid size-8 place-items-center rounded-full bg-green/10 text-green transition-transform group-hover:translate-x-0.5" aria-hidden="true">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                            </span>
                            <span class="mt-3 pr-8 text-lg leading-snug font-bold text-night">{{ r.title }}</span>
                            <span class="mt-auto flex flex-wrap items-center gap-3 pt-4 text-sm text-ink-2">
                                <span class="flex items-center gap-1.5"><Glyph name="calendar" class="size-4.5" />{{ r.day }}</span>
                                <span class="h-4 w-px bg-night/15" aria-hidden="true" />
                                <span class="flex items-center gap-1.5"><Glyph name="clock" class="size-4.5" />{{ timeOf(r.time) }}</span>
                            </span>
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Closing band, same as the agenda page's -->
        <section class="px-4 pb-16 md:px-6 md:pb-24" aria-labelledby="session-cta">
            <div class="cta-band relative mx-auto max-w-[90rem] overflow-hidden rounded-2xl px-6 py-12 text-paper md:px-12 md:py-14">
                <div class="relative grid items-center gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <SplitHeading
                            id="session-cta"
                            :text="content.headings.cta.title"
                            :emphasis="content.headings.cta.emphasis ?? undefined"
                            emphasis-class="text-gold"
                            class="max-w-[16ch] font-display text-[clamp(2.5rem,4.6vw,3.75rem)] leading-[0.95] font-black uppercase"
                        />
                    </div>
                    <div class="flex flex-wrap gap-4 lg:col-span-5 lg:justify-end" data-reveal="fade">
                        <CtaLink href="/register" class="!px-7 !py-4">{{ t('inner.agenda.registerSummit') }}</CtaLink>
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
/* Same ground as the About page: ice blue with soft teal and summit-gold glows alternating sides down the page */
.ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 30%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 62%, rgb(8 127 140 / 0.18), transparent 70%),
        radial-gradient(900px 640px at 100% 92%, rgb(234 185 75 / 0.32), transparent 70%),
        var(--mist);
}
.hero-glow {
    background: linear-gradient(90deg, rgb(7 20 17 / 0.92) 0%, rgb(6 69 76 / 0.75) 45%, rgb(7 20 17 / 0.25) 100%);
}
.crumbs-light :deep(ol),
.crumbs-light :deep(a),
.crumbs-light :deep([aria-current]) {
    color: rgb(255 255 255 / 0.9);
}
.cta-band {
    background:
        radial-gradient(60% 120% at 100% 0%, rgb(8 127 140 / 0.55), transparent 70%),
        linear-gradient(120deg, #06454c, #0a5f69);
}
</style>
