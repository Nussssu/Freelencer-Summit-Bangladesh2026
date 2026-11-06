<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
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
const halls = computed(() => opts(props.content.agenda.sessions.map((s) => s.hall)));
const speakers = computed(() => opts(props.content.agenda.sessions.flatMap((s) => (s.speaker ? [s.speaker] : []))).sort());

// Programme filters after takeoffistanbul.com/en/schedule: search, stage, speakers, tags, date and an hour range.
const HOURS = [8, 18] as const;
const q = ref('');
const stage = ref('');
const picked = ref<string[]>([]);
const tags = ref<string[]>([]);
const day = ref('');
const from = ref<number>(HOURS[0]);
const to = ref<number>(HOURS[1]);
const filtersOpen = ref(false);

// Desktop sticky sidebar that fits its height: when the filters are taller than the screen, the sticky top goes
// negative so the sidebar scrolls up with the page until its bottom shows, then holds there (nothing is clipped).
const aside = ref<HTMLElement>();
const stickTop = ref('calc(153px + 1.5rem)');
const fitSticky = () => {
    const h = aside.value?.offsetHeight ?? 0;
    stickTop.value = `${Math.min(153 + 24, innerHeight - h - 24)}px`;
};
let ro: ResizeObserver | undefined;
onMounted(() => {
    fitSticky();
    ro = new ResizeObserver(fitSticky);
    if (aside.value) ro.observe(aside.value);
    addEventListener('resize', fitSticky);
});
onBeforeUnmount(() => {
    ro?.disconnect();
    removeEventListener('resize', fitSticky);
});
// Speaker and tag selects add a removable token each; the arrays are mutated in place (refs unwrap in the template).
const pick = (list: string[], e: Event) => {
    const el = e.target as HTMLSelectElement;
    if (el.value && !list.includes(el.value)) list.push(el.value);
    el.value = '';
};
const drop = (list: string[], v: string) => list.splice(list.indexOf(v), 1);
// The two thumbs never cross.
const setFrom = (e: Event) => (from.value = Math.min(Number((e.target as HTMLInputElement).value), to.value));
const setTo = (e: Event) => (to.value = Math.max(Number((e.target as HTMLInputElement).value), from.value));
const hours = (time: string) => time.split('–').map((p) => Number(p.trim().split(':')[0]));

const rows = computed(() => {
    const needle = q.value.trim().toLowerCase();
    return props.content.agenda.sessions.filter((s) => {
        const [start, end = start] = hours(s.time);
        return (
            (!day.value || s.day === day.value) &&
            (!stage.value || s.hall === stage.value) &&
            (!picked.value.length || (!!s.speaker && picked.value.includes(s.speaker))) &&
            (!tags.value.length || tags.value.includes(s.type)) &&
            start >= from.value &&
            end <= to.value &&
            (!needle || [s.title, s.summary, s.speaker, s.hall, s.type, s.track].some((f) => f?.toLowerCase().includes(needle)))
        );
    });
});
// A day with no sessions published yet (as opposed to one the other filters emptied).
const dayEmpty = computed(() => !!day.value && !props.content.agenda.sessions.some((s) => s.day === day.value));
const filtered = computed(() => !!(q.value || stage.value || picked.value.length || tags.value.length || day.value || from.value !== HOURS[0] || to.value !== HOURS[1]));
const clear = () => {
    q.value = stage.value = day.value = '';
    picked.value = [];
    tags.value = [];
    from.value = HOURS[0];
    to.value = HOURS[1];
};
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
// An uploaded agenda PDF wins; otherwise the programme PDF generated from these sessions.
const pdf = computed(() => props.content.downloads.find((x) => x.kind === 'agenda')?.href ?? '/agenda/summit-programme.pdf');

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
        <!-- Programme, after takeoffistanbul.com/en/schedule: search and filter sidebar (stages, speaker, tag, date,
             time range), the session cards beside it. Below xl the filters fold behind a Filter button. -->
        <section class="in-section" aria-labelledby="ag-programme">
            <div class="container-site">
                <SectionHead id="ag-programme" :heading="content.headings.programme" class="items-center!">
                    <a :href="pdf" download class="in-btn is-ghost justify-center gap-2!">
                        <Glyph name="download" class="size-5" />
                        {{ t('inner.agenda.downloadPdf') }}
                    </a>
                </SectionHead>

                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:gap-8">
                    <!-- Mobile bar: Filter toggle and search -->
                    <div class="flex items-center gap-3 xl:hidden">
                        <button type="button" class="in-btn is-ghost min-h-11! shrink-0 gap-2! px-4! py-2!" :aria-expanded="filtersOpen" aria-controls="ag-filters" @click="filtersOpen = !filtersOpen">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                            {{ t('inner.agenda.filter') }}
                        </button>
                        <label class="relative flex-1">
                            <span class="sr-only">{{ t('inner.list.search') }}</span>
                            <Glyph name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-ink-2" />
                            <input v-model="q" type="search" :placeholder="t('inner.list.search')" autocomplete="off" class="ag-input pl-11!" />
                        </label>
                    </div>

                    <!-- Desktop: sticky; a sidebar taller than the screen scrolls with the page until its end shows (fitSticky) -->
                    <aside
                        id="ag-filters"
                        ref="aside"
                        class="ag-filters w-full shrink-0 xl:sticky xl:block xl:w-[22rem]"
                        :class="!filtersOpen && 'hidden'"
                        :style="{ '--ag-top': stickTop }"
                        :aria-label="t('inner.agenda.filter')"
                    >
                        <label class="relative mb-4 hidden xl:block">
                            <span class="sr-only">{{ t('inner.list.search') }}</span>
                            <input v-model="q" type="search" :placeholder="t('inner.list.search')" autocomplete="off" class="ag-input pr-11!" />
                            <Glyph name="search" class="pointer-events-none absolute top-1/2 right-3.5 size-4.5 -translate-y-1/2 text-ink-2" />
                        </label>

                        <!-- Stages -->
                        <div class="in-card is-solid mb-6 p-6">
                            <h3 class="mb-4 text-lg font-semibold text-green">{{ t('inner.agenda.stages') }}</h3>
                            <div class="mb-4">
                                <button type="button" class="ag-stage" :aria-pressed="!stage" @click="stage = ''">{{ t('inner.list.all') }}</button>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button v-for="h in halls" :key="h" type="button" class="ag-stage" :aria-pressed="stage === h" @click="stage = stage === h ? '' : h">{{ h }}</button>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="in-card is-solid gap-7 p-6">
                            <h3 class="text-lg font-semibold text-green">{{ t('inner.agenda.filter') }}</h3>

                            <div>
                                <ul v-if="picked.length" class="mb-3 flex flex-wrap gap-2">
                                    <li v-for="sp in picked" :key="sp">
                                        <button type="button" class="ag-token" :aria-label="t('inner.agenda.remove', { name: sp })" @click="drop(picked, sp)">{{ sp }}<span aria-hidden="true">×</span></button>
                                    </li>
                                </ul>
                                <label class="block">
                                    <span class="sr-only">{{ t('inner.agenda.selectSpeaker') }}</span>
                                    <select class="ag-select" :disabled="!speakers.length" @change="pick(picked, $event)">
                                        <option value="">{{ speakers.length ? t('inner.agenda.selectSpeaker') : t('inner.agenda.noSpeakers') }}</option>
                                        <option v-for="sp in speakers" :key="sp" :value="sp" :disabled="picked.includes(sp)">{{ sp }}</option>
                                    </select>
                                </label>
                            </div>

                            <div>
                                <ul v-if="tags.length" class="mb-3 flex flex-wrap gap-2">
                                    <li v-for="tg in tags" :key="tg">
                                        <button type="button" class="ag-token" :aria-label="t('inner.agenda.remove', { name: tg })" @click="drop(tags, tg)">{{ tg }}<span aria-hidden="true">×</span></button>
                                    </li>
                                </ul>
                                <label class="block">
                                    <span class="sr-only">{{ t('inner.agenda.selectTag') }}</span>
                                    <select class="ag-select" @change="pick(tags, $event)">
                                        <option value="">{{ t('inner.agenda.selectTag') }}</option>
                                        <option v-for="ty in content.agenda.types" :key="ty" :value="ty" :disabled="tags.includes(ty)">{{ ty }}</option>
                                    </select>
                                </label>
                            </div>

                            <fieldset>
                                <legend class="mb-4 text-sm font-semibold text-night">{{ t('inner.agenda.selectDate') }}</legend>
                                <div class="flex flex-col gap-3">
                                    <label class="flex cursor-pointer items-center gap-3 text-sm text-night select-none">
                                        <input v-model="day" type="radio" name="ag-day" value="" class="ag-radio" />
                                        <span>{{ t('inner.list.all') }}</span>
                                    </label>
                                    <label v-for="(dy, i) in days" :key="dy" class="flex cursor-pointer items-center gap-3 text-sm text-night select-none">
                                        <input v-model="day" type="radio" name="ag-day" :value="dy" class="ag-radio" />
                                        <span>{{ dy }} <span class="text-ink-2">· {{ dayDate(i) }}</span></span>
                                    </label>
                                </div>
                            </fieldset>

                            <div>
                                <p class="mb-4 text-sm font-semibold text-night">{{ t('inner.agenda.timeRange') }}</p>
                                <div class="ag-range" :style="{ '--a': `${((from - HOURS[0]) / (HOURS[1] - HOURS[0])) * 100}%`, '--b': `${((to - HOURS[0]) / (HOURS[1] - HOURS[0])) * 100}%` }">
                                    <input type="range" :min="HOURS[0]" :max="HOURS[1]" step="1" :value="from" :aria-label="t('inner.agenda.from')" @input="setFrom" />
                                    <input type="range" :min="HOURS[0]" :max="HOURS[1]" step="1" :value="to" :aria-label="t('inner.agenda.to')" @input="setTo" />
                                </div>
                                <div class="mt-5 flex justify-between px-1 font-semibold text-night">
                                    <span>{{ from }}:00</span>
                                    <span>{{ to }}:00</span>
                                </div>
                            </div>

                            <button type="button" class="ag-clear" @click="clear">{{ t('inner.list.clear') }}</button>
                        </div>
                    </aside>

                    <!-- Sessions -->
                    <div class="flex w-full min-w-0 flex-col gap-4">
                        <div class="flex flex-wrap items-baseline justify-between gap-3">
                            <p v-if="day" class="text-xl font-bold text-night uppercase md:text-3xl">{{ day }} · {{ dayDate(days.indexOf(day)) }}</p>
                            <p class="in-meta" aria-live="polite">{{ t('inner.list.results', rows.length) }}</p>
                        </div>
                        <ol v-if="rows.length" class="flex flex-col gap-6 md:gap-8">
                            <AgendaSession v-for="s in rows" :key="s.ics" :session="s" :calendar="sessionCal(s)" :icon="styleOf(s.type)[0]" :tone="styleOf(s.type)[1]" />
                        </ol>
                        <EmptyState v-else-if="dayEmpty" level="h3" icon="calendar" :title="t('inner.empty.title')" :body="t('inner.empty.body')" />
                        <EmptyState v-else level="h3" icon="target" :title="t('inner.empty.title')" :body="t('inner.list.noMatch')">
                            <button v-if="filtered" type="button" class="in-btn is-ghost" @click="clear">{{ t('inner.list.clear') }}</button>
                        </EmptyState>
                    </div>
                </div>
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
/* Filter sidebar (desktop): sticky at the top computed by fitSticky */
@media (min-width: 1280px) {
    .ag-filters {
        top: var(--ag-top, calc(153px + 1.5rem));
    }
}
/* Same ground as the About page: ice blue with soft teal and summit-gold glows alternating sides down the page */
.ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 30%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 62%, rgb(8 127 140 / 0.18), transparent 70%),
        radial-gradient(900px 640px at 100% 92%, rgb(234 185 75 / 0.32), transparent 70%),
        var(--mist);
}
/* Programme filters: the reference's controls in the site's teal, gold and ice */
.ag-input,
.ag-select {
    display: block;
    width: 100%;
    min-height: 2.875rem;
    border: 1px solid rgb(7 20 17 / 0.15);
    border-radius: 0.75rem;
    background-color: var(--white);
    padding: 0.5rem 1rem;
    color: var(--night);
    outline: none;
    transition:
        border-color 0.2s ease,
        box-shadow 0.2s ease;
}
.ag-input:focus,
.ag-select:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgb(8 127 140 / 0.15);
}
.ag-select {
    appearance: none;
    padding-right: 2.5rem;
    background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23071411' stroke-width='1.6'%3E%3Cpath d='M2 4l4 4 4-4'/%3E%3C/svg%3E") no-repeat right 14px center;
    font-size: 0.875rem;
}
.ag-select:disabled {
    cursor: not-allowed;
    opacity: 0.6;
}
.ag-stage {
    display: inline-block;
    border: 1px solid rgb(7 20 17 / 0.15);
    border-radius: 999px;
    padding: 0.5rem 1.5rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--ink-2);
    transition:
        background 0.2s ease,
        color 0.2s ease,
        border-color 0.2s ease;
}
.ag-stage:hover,
.ag-stage[aria-pressed='true'] {
    border-color: transparent;
    background: linear-gradient(90deg, #06454c, var(--green));
    color: var(--white);
}
.ag-token {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    border-radius: 0.375rem;
    background: rgb(8 127 140 / 0.1);
    padding: 0.25rem 0.75rem;
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--green);
}
.ag-token:hover {
    background: rgb(8 127 140 / 0.18);
}
.ag-radio {
    appearance: none;
    width: 1rem;
    height: 1rem;
    flex-shrink: 0;
    cursor: pointer;
    border: 1px solid rgb(7 20 17 / 0.35);
    border-radius: 999px;
    transition: all 0.2s ease;
}
.ag-radio:checked {
    border-color: var(--green);
    background: var(--green);
    box-shadow: inset 0 0 0 3px var(--white);
}
.ag-radio:focus-visible {
    outline: 2px solid var(--green);
    outline-offset: 2px;
}
/* Two stacked range inputs: only the thumbs take pointer events, the track between them is teal */
.ag-range {
    position: relative;
    height: 1.25rem;
}
.ag-range::before {
    content: '';
    position: absolute;
    top: 50%;
    right: 0;
    left: 0;
    height: 4px;
    translate: 0 -50%;
    border-radius: 999px;
    background: linear-gradient(90deg, rgb(7 20 17 / 0.12) var(--a), var(--green) var(--a) var(--b), rgb(7 20 17 / 0.12) var(--b));
}
.ag-range input {
    position: absolute;
    inset: 0;
    width: 100%;
    margin: 0;
    appearance: none;
    background: none;
    pointer-events: none;
}
.ag-range input::-webkit-slider-thumb {
    width: 1.25rem;
    height: 1.25rem;
    appearance: none;
    cursor: pointer;
    border: 3px solid var(--white);
    border-radius: 999px;
    background: var(--green);
    box-shadow: 0 2px 6px rgb(7 20 17 / 0.3);
    pointer-events: auto;
}
.ag-range input::-moz-range-thumb {
    width: 1.25rem;
    height: 1.25rem;
    cursor: pointer;
    border: 3px solid var(--white);
    border-radius: 999px;
    background: var(--green);
    box-shadow: 0 2px 6px rgb(7 20 17 / 0.3);
    pointer-events: auto;
}
.ag-range input:focus-visible::-webkit-slider-thumb {
    outline: 2px solid var(--gold);
    outline-offset: 2px;
}
.ag-clear {
    width: 100%;
    border: 1px solid var(--green);
    border-radius: 0.75rem;
    padding: 0.5rem;
    font-weight: 600;
    color: var(--green);
    transition:
        background 0.2s ease,
        color 0.2s ease;
}
.ag-clear:hover {
    background: linear-gradient(90deg, var(--green), #06454c);
    color: var(--white);
}
.cta-band {
    background:
        radial-gradient(60% 120% at 100% 0%, rgb(8 127 140 / 0.55), transparent 70%),
        linear-gradient(120deg, #06454c, #0a5f69);
}
</style>
