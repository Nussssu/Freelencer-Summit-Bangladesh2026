<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import AboutHero from '@/components/about/AboutHero.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Glyph from '@/components/inner/Glyph.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import StatusBadge from '@/components/inner/StatusBadge.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, Option, OptionGroup, Paginated, ProgramCard, SectionHeading } from '@/types/inner';

// District Roadshows (and Divisional Summits): photo hero, then the directory laid out like the Agenda's programme: a
// search and filter sidebar (status, division, and district when the page sends districts) beside date-led rows, each
// linking to its detail page. Every programme on one page.
const props = defineProps<{
    page: InnerPage;
    content: {
        filters: { q: string; status: string; division: string; district?: string };
        programs: Paginated<ProgramCard>;
        divisionOptions: Option[];
        districtGroups?: OptionGroup[];
        statusOptions: Option[];
        search?: string;
        headings: Record<'directory', SectionHeading>;
        empty: { title: string; body: string };
    };
}>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
const { filters, loading, filtered, clear } = useDirectory({ ...props.content.filters }, { only: ['content'], anchor: '#directory' });
const filtersOpen = ref(false);

// The district list narrows to the chosen division; a district outside it is dropped.
const divisionLabel = computed(() => props.content.divisionOptions.find((o) => o.value === filters.division)?.label);
const allDistricts = props.content.districtGroups ?? [];
const districtGroups = computed(() => (divisionLabel.value ? allDistricts.filter((g) => g.label === divisionLabel.value) : allDistricts));
const searchLabel = props.content.search ?? 'Search roadshows';
watch(
    () => filters.division,
    () => {
        if (filters.district && !districtGroups.value.some((g) => g.options.some((o) => o.value === filters.district))) filters.district = '';
    },
);

const part = (iso: string | null, o: Intl.DateTimeFormatOptions) => (iso ? new Date(iso).toLocaleDateString('en-GB', { ...o, timeZone: 'Asia/Dhaka' }) : null);
const time = (iso: string | null) => (iso ? new Date(iso).toLocaleTimeString('en-GB', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Dhaka' }) : null);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />

    <div ref="root" class="ground on-light">
        <!-- Directory: search and filter sidebar, roadshow rows beside it. Below xl the filters fold behind a Filter button. -->
        <section id="directory" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]" aria-labelledby="dr-directory">
            <div class="container-site">
                <SectionHead id="dr-directory" :heading="content.headings.directory" />

                <div class="flex flex-col gap-6 xl:flex-row xl:items-start xl:gap-8">
                    <!-- Mobile bar: Filter toggle and search -->
                    <div class="flex items-center gap-3 xl:hidden">
                        <button type="button" class="in-btn is-ghost min-h-11! shrink-0 gap-2! px-4! py-2!" :aria-expanded="filtersOpen" aria-controls="dr-filters" @click="filtersOpen = !filtersOpen">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
                            {{ t('inner.agenda.filter') }}
                        </button>
                        <label class="relative flex-1">
                            <span class="sr-only">{{ t('inner.list.search') }}</span>
                            <Glyph name="search" class="pointer-events-none absolute top-1/2 left-3.5 size-4.5 -translate-y-1/2 text-ink-2" />
                            <input v-model="filters.q" type="search" :placeholder="searchLabel" autocomplete="off" class="dr-input pl-11!" />
                        </label>
                    </div>

                    <aside id="dr-filters" class="w-full shrink-0 xl:sticky xl:top-[calc(153px+1.5rem)] xl:block xl:w-[22rem]" :class="!filtersOpen && 'hidden'" :aria-label="t('inner.agenda.filter')">
                        <label class="relative mb-4 hidden xl:block">
                            <span class="sr-only">{{ t('inner.list.search') }}</span>
                            <input v-model="filters.q" type="search" :placeholder="searchLabel" autocomplete="off" class="dr-input pr-11!" />
                            <Glyph name="search" class="pointer-events-none absolute top-1/2 right-3.5 size-4.5 -translate-y-1/2 text-ink-2" />
                        </label>

                        <!-- Status -->
                        <div class="in-card is-solid mb-6 p-6">
                            <h3 class="mb-4 text-lg font-semibold text-green">Status</h3>
                            <div class="mb-4">
                                <button type="button" class="dr-pill" :aria-pressed="!filters.status" @click="filters.status = ''">{{ t('inner.list.all') }}</button>
                            </div>
                            <div class="flex flex-wrap gap-3">
                                <button v-for="o in content.statusOptions" :key="o.value" type="button" class="dr-pill" :aria-pressed="filters.status === o.value" @click="filters.status = filters.status === o.value ? '' : o.value">{{ o.label }}</button>
                            </div>
                        </div>

                        <!-- Filter -->
                        <div class="in-card is-solid gap-7 p-6">
                            <h3 class="text-lg font-semibold text-green">{{ t('inner.agenda.filter') }}</h3>

                            <label v-if="content.districtGroups" class="block">
                                <span class="mb-3 block text-sm font-semibold text-night">District</span>
                                <select v-model="filters.district" class="dr-select">
                                    <option value="">All districts</option>
                                    <optgroup v-for="g in districtGroups" :key="g.label" :label="g.label">
                                        <option v-for="o in g.options" :key="o.value" :value="o.value">{{ o.label }}</option>
                                    </optgroup>
                                </select>
                            </label>

                            <label class="block">
                                <span class="mb-3 block text-sm font-semibold text-night">Division</span>
                                <select v-model="filters.division" class="dr-select">
                                    <option value="">All divisions</option>
                                    <option v-for="o in content.divisionOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
                                </select>
                            </label>

                            <button type="button" class="dr-clear" @click="clear">{{ t('inner.list.clear') }}</button>
                        </div>
                    </aside>

                    <!-- Roadshows -->
                    <div class="flex w-full min-w-0 flex-col gap-4">
                        <p class="in-meta" aria-live="polite">{{ loading ? t('inner.list.loading') : t('inner.list.results', content.programs.data.length) }}</p>
                        <ul v-if="content.programs.data.length" class="grid gap-5 transition-opacity" :class="loading && 'opacity-50'">
                            <li v-for="p in content.programs.data" :key="p.slug">
                                <Link
                                    :href="p.href"
                                    class="dr-row group grid grid-cols-[4.5rem_1fr] items-center gap-5 rounded-2xl bg-white p-4 ring-1 ring-night/8 sm:grid-cols-[5.5rem_1fr_auto] sm:gap-8 sm:p-6"
                                >
                                    <span class="grid aspect-square place-items-center rounded-xl bg-mist text-center" :class="!p.startsAt && 'text-ink-2'">
                                        <span v-if="p.startsAt" class="leading-none">
                                            <span class="block font-display text-3xl font-black text-green sm:text-4xl">{{ part(p.startsAt, { day: 'numeric' }) }}</span>
                                            <span class="mt-1 block text-xs font-bold tracking-wide uppercase">{{ part(p.startsAt, { month: 'short' }) }}</span>
                                        </span>
                                        <span v-else class="text-xs font-bold tracking-wide uppercase">Date<br />TBA</span>
                                    </span>
                                    <span class="min-w-0">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <StatusBadge :status="p.status" :label="p.statusLabel" />
                                        </span>
                                        <span class="mt-2 block text-lg leading-snug font-extrabold text-night transition-colors group-hover:text-green sm:text-xl">{{ p.title }}</span>
                                        <span class="in-meta mt-1 block">
                                            {{ [p.location, p.district].filter(Boolean).join(' · ') || 'Venue to be announced' }}<template v-if="time(p.startsAt)"> · {{ time(p.startsAt) }}</template>
                                        </span>
                                        <span v-if="p.partner" class="in-meta mt-0.5 block">Organised with {{ p.partner }}</span>
                                    </span>
                                    <span class="hidden size-11 place-items-center rounded-full border border-night/12 text-night transition group-hover:border-green group-hover:bg-green group-hover:text-white sm:grid" aria-hidden="true">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M13 6l6 6-6 6" /></svg>
                                    </span>
                                </Link>
                            </li>
                        </ul>
                        <EmptyState v-else-if="filtered" level="h3" icon="target" :title="t('inner.list.noMatch')">
                            <button type="button" class="in-btn is-ghost" @click="clear">{{ t('inner.list.clear') }}</button>
                        </EmptyState>
                        <EmptyState v-else level="h3" icon="clock" :title="content.empty.title" :body="content.empty.body" />
                    </div>
                </div>
            </div>
        </section>
    </div>
</template>

<style scoped>
.dr-row {
    transition:
        transform 0.35s ease,
        box-shadow 0.35s ease;
}
.dr-row:hover {
    transform: translateY(-3px);
    box-shadow: 0 24px 48px -32px rgb(7 20 17 / 0.45);
}
/* Filter controls, matching the Agenda programme's sidebar */
.dr-input,
.dr-select {
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
.dr-input:focus,
.dr-select:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgb(8 127 140 / 0.15);
}
.dr-select {
    appearance: none;
    padding-right: 2.5rem;
    background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23071411' stroke-width='1.6'%3E%3Cpath d='M2 4l4 4 4-4'/%3E%3C/svg%3E") no-repeat right 14px center;
    font-size: 0.875rem;
}
.dr-pill {
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
.dr-pill:hover,
.dr-pill[aria-pressed='true'] {
    border-color: transparent;
    background: linear-gradient(90deg, #06454c, var(--green));
    color: var(--white);
}
.dr-clear {
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
.dr-clear:hover {
    background: linear-gradient(90deg, var(--green), #06454c);
    color: var(--white);
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
