<script setup lang="ts">
import { computed, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import AgendaSession from '@/components/agenda/AgendaSession.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Glyph, { type GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import { useAgendaCalendar } from '@/composables/useAgendaCalendar';
import { useReveal } from '@/composables/useReveal';
import { useShared } from '@/composables/useShared';
import type { AgendaContent, AgendaSession as AgendaSessionRow, InnerPage } from '@/types/inner';

// Brief §7, §14: day-based agenda with filters by date, session type and hall, speakers, add to calendar, downloads.
// shortcut: filters run client-side on the HomeContent sample list; move to SessionQuery + useDirectory once sessions live in the CMS.
const props = defineProps<{ page: InnerPage; content: AgendaContent }>();
const { t, d } = useI18n();
const { summit } = useShared();
const root = ref<HTMLElement>();
useReveal(root);

const opts = (values: string[]) => [...new Set(values)];
const days = computed(() => opts([...props.content.agenda.days, ...props.content.agenda.sessions.map((s) => s.day)]));
// A day with no sessions published yet (as opposed to one the type filter emptied).
const dayEmpty = computed(() => !props.content.agenda.sessions.some((s) => s.day === day.value));
const day = ref(days.value[0] ?? '');
const type = ref('');
const rows = computed(() => props.content.agenda.sessions.filter((s) => s.day === day.value && (!type.value || s.type === type.value)));
const filtered = computed(() => !!type.value);
const clear = () => (type.value = '');
// Day tabs show the calendar date only once it is confirmed; day N is starts_at + N-1.
const dayDate = (i: number) => {
    if (!summit.value.dateConfirmed) return t('sections.dateTbc');
    const at = new Date(summit.value.startsAt);
    at.setDate(at.getDate() + i);
    return d(at, { weekday: 'short', day: 'numeric', month: 'short', year: 'numeric' });
};

const cal = useAgendaCalendar();
const sessionCal = (s: AgendaSessionRow) => cal.sessionCal(s, days.value);

const typeStyle: Record<string, [GlyphName, string, string]> = {
    Keynote: ['mic', 'tone-sun', 'highlight-keynote'],
    Seminar: ['cap', 'tone-teal', 'highlight-keynote'],
    Workshop: ['ai', 'tone-violet', 'highlight-workshops'],
    'Career Talk': ['briefcase', 'tone-mint', 'highlight-career'],
    Panel: ['people', 'tone-rose', 'highlight-keynote'],
    'AI Awareness': ['ai', 'tone-sky', 'highlight-ai'],
    'Success Story': ['star', 'tone-sun', 'highlight-stories'],
    'Marketplace Session': ['store', 'tone-blue', 'highlight-marketplace'],
    'Freelancer-to-Entrepreneur': ['rocket', 'tone-mint', 'highlight-stories'],
    'Award Session': ['trophy', 'tone-sun', 'highlight-awards'],
    Networking: ['handshake', 'tone-sky', 'highlight-networking'],
};
const styleOf = (ty: string) => typeStyle[ty] ?? (['mic', 'tone-teal', 'highlight-keynote'] as const);
// Track tiles filter the timeline above and bring it into view.
const pickType = (ty: string) => {
    type.value = ty;
    document.getElementById('ag-programme')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
// An uploaded agenda PDF wins; otherwise the programme PDF generated from these sessions.
const pdf = computed(() => props.content.downloads.find((x) => x.kind === 'agenda')?.href ?? '/agenda/summit-programme.pdf');

// One row open at a time.
const open = ref<string | null>(props.content.agenda.sessions[0]?.ics ?? null);
// Plan your day strip: when and where, with the "to be confirmed" note while either is open (same rule as TbcNotice).
const tbc = computed(() => !summit.value.dateConfirmed || !summit.value.venue);
const planDate = computed(() => (summit.value.dateConfirmed ? d(new Date(summit.value.startsAt), { day: 'numeric', month: 'long', year: 'numeric' }) : t('inner.tbc.lateNov', { edition: summit.value.edition })));
const planPlace = computed(() => (summit.value.venue ? `${summit.value.venue}, ${summit.value.city}` : t('header.city')));
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <!-- First screen: the hero keeps its height and the Plan your day strip fills the rest of the viewport -->
    <div class="flex min-h-svh flex-col">
        <NewsHero id="agenda-title" :title="page.hero.title" :emphasis="page.hero.emphasis" :image="page.hero.image ?? undefined" />
        <!-- Plan your day: one strip with the heading, the date and the place -->
        <section class="flex flex-1 items-center border-b border-green/10 bg-[#e2f1f7]" aria-labelledby="ag-plan">
            <div class="container-site w-full">
                <div class="flex flex-col gap-6 pt-8 pb-24 md:py-10 lg:flex-row lg:items-center lg:justify-between">
                    <div>
                        <SplitHeading id="ag-plan" :text="content.headings.plan.title" :emphasis="content.headings.plan.emphasis ?? undefined" emphasis-class="text-green" class="in-h2 text-[clamp(2rem,3.4vw,3rem)]!" />
                        <div class="mt-6 flex flex-wrap gap-3">
                            <InBtn href="/register" tone="gold">{{ t('cta.register') }}</InBtn>
                        </div>
                    </div>
                    <dl class="grid gap-5 sm:grid-cols-2 sm:gap-0 lg:flex">
                        <div class="flex items-center gap-4 sm:pr-8 lg:border-l lg:border-night/10 lg:px-10">
                            <dt class="text-green"><Glyph name="calendar" class="size-8" /><span class="sr-only">{{ t('inner.tbc.date') }}</span></dt>
                            <dd>
                                <span class="block text-lg font-bold text-night">{{ planDate }}</span>
                                <span v-if="tbc" class="block text-sm text-ink-2">{{ t('header.tbc') }}</span>
                            </dd>
                        </div>
                        <div class="flex items-center gap-4 sm:border-l sm:border-night/10 sm:pl-8 lg:pr-2 lg:pl-10">
                            <dt class="text-green"><Glyph name="pin" class="size-8" /><span class="sr-only">{{ t('inner.tbc.venue') }}</span></dt>
                            <dd class="text-lg font-bold text-night">{{ planPlace }}</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>
    </div>

    <div ref="root" class="ground">
        <!-- Programme: day tabs, filters and the session timeline -->
        <section class="in-section" aria-labelledby="ag-programme">
            <div class="container-site">
                <SectionHead id="ag-programme" :heading="content.headings.programme" class="items-center!">
                    <a :href="pdf" download class="in-btn is-ghost aspect-square px-0! justify-center" :aria-label="t('inner.agenda.downloadPdf')" :title="t('inner.agenda.downloadPdf')"><Glyph name="download" class="size-5" /></a>
                </SectionHead>

                <div class="grid gap-3 sm:grid-cols-3 lg:max-w-4xl" role="group" :aria-label="t('inner.agenda.days')">
                    <button
                        v-for="(dy, i) in days"
                        :key="dy"
                        type="button"
                        class="in-card flex-row! items-center gap-4 px-5 py-4 text-left"
                        :class="day === dy ? 'bg-green! text-white' : 'is-solid text-night hover:border-green'"
                        :aria-pressed="day === dy"
                        @click="day = dy"
                    >
                        <Glyph name="calendar" class="size-8 shrink-0" :class="day !== dy && 'text-green'" />
                        <span>
                            <span class="block text-lg font-bold">{{ dy }}</span>
                            <span class="block text-sm opacity-80">{{ dayDate(i) }}</span>
                        </span>
                    </button>
                </div>

                <div class="in-card is-solid mt-5 grid gap-4 p-4 md:p-5">
                    <div class="grid gap-3 lg:grid-cols-[8rem_1fr] lg:items-center">
                        <p class="font-semibold text-night">{{ t('inner.list.type') }}:</p>
                        <ul class="flex gap-2 overflow-x-auto pb-1 lg:flex-wrap lg:overflow-visible lg:pb-0" role="group" :aria-label="t('inner.list.type')">
                            <li v-for="ty in ['', ...content.agenda.types]" :key="ty">
                                <button type="button" class="in-chip gap-2" :class="type === ty && 'border-gold! bg-gold! text-night!'" :aria-pressed="type === ty" @click="type = ty">
                                    <Glyph :name="ty ? styleOf(ty)[0] : 'grid'" class="size-4.5" />{{ ty || t('inner.agenda.allSessions') }}
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <div class="mt-5 flex flex-wrap items-center justify-between gap-3">
                    <p class="in-meta flex items-center gap-2" aria-live="polite">{{ t('inner.list.results', rows.length) }}</p>
                    <button v-if="filtered" type="button" class="in-link min-h-0! text-sm" @click="clear">{{ t('inner.list.clear') }}</button>
                </div>
                <div v-if="rows.length" class="in-card is-solid mt-4 p-3 md:p-4">
                    <ol class="relative grid gap-1 before:absolute before:top-8 before:bottom-8 before:left-[15px] before:w-0.5 before:bg-green/25 md:before:left-[19px]">
                        <AgendaSession
                            v-for="s in rows"
                            :key="s.ics"
                            :session="s"
                            :calendar="sessionCal(s)"
                            :icon="styleOf(s.type)[0]"
                            :tone="styleOf(s.type)[1]"
                            :photo="`/media/photos/${styleOf(s.type)[2]}.jpg`"
                            :open="open === s.ics"
                            @toggle="open = open === s.ics ? null : s.ics"
                        />
                    </ol>
                </div>
                <EmptyState v-else-if="dayEmpty" class="mt-4" level="h3" icon="calendar" :title="t('inner.empty.title')" :body="t('inner.empty.body')" />
                <EmptyState v-else class="mt-4" level="h3" icon="target" :title="t('inner.empty.title')" :body="t('inner.list.noMatch')">
                    <button type="button" class="in-btn is-ghost" @click="clear">{{ t('inner.list.clear') }}</button>
                </EmptyState>
            </div>
        </section>

        <!-- Programme tracks: one tile per session type, each filters the agenda -->
        <section class="in-section" aria-labelledby="ag-tracks">
            <div class="container-site">
                <SectionHead id="ag-tracks" :heading="content.headings.tracks" />
                <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-11">
                    <li v-for="ty in content.agenda.types" :key="ty" data-reveal="fade">
                        <button type="button" class="in-card is-solid is-link h-full w-full items-center gap-2.5 px-2 py-4 text-center" :aria-label="t('inner.agenda.showType', { type: ty })" @click="pickType(ty)">
                            <span class="grid size-12 place-items-center rounded-xl bg-(--disc) text-(--tint)" :class="styleOf(ty)[1]"><Glyph :name="styleOf(ty)[0]" class="size-6" /></span>
                            <span class="text-xs leading-tight font-semibold text-night">{{ ty }}</span>
                        </button>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Closing band, styled like the About page's -->
        <section class="px-4 pb-16 md:px-6 md:pb-24" aria-labelledby="ag-cta">
            <div class="cta-band relative mx-auto max-w-[90rem] overflow-hidden rounded-2xl px-6 py-12 text-paper md:px-12 md:py-14">
                <div class="relative grid items-center gap-10 lg:grid-cols-12">
                    <div class="lg:col-span-7">
                        <SplitHeading
                            id="ag-cta"
                            :text="content.headings.cta.title"
                            :emphasis="content.headings.cta.emphasis ?? undefined"
                            emphasis-class="text-gold"
                            class="max-w-[16ch] font-display text-[clamp(2.5rem,4.6vw,3.75rem)] leading-[0.95] font-black uppercase"
                        />
                    </div>
                    <div class="flex flex-wrap gap-4 lg:col-span-5 lg:justify-end" data-reveal="fade">
                        <CtaLink href="/register" class="!px-7 !py-4">{{ t('inner.agenda.register') }}</CtaLink>
                        <CtaLink href="/grand-summit" tone="outline" class="!px-7 !py-4">{{ t('inner.agenda.explore') }}</CtaLink>
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
.cta-band {
    background:
        radial-gradient(60% 120% at 100% 0%, rgb(8 127 140 / 0.55), transparent 70%),
        linear-gradient(120deg, #06454c, #0a5f69);
}
</style>
