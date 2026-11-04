<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AboutClosing from '@/components/about/AboutClosing.vue';
import AboutHero from '@/components/about/AboutHero.vue';
import AboutIntro from '@/components/about/AboutIntro.vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import ListLayout from '@/components/inner/ListLayout.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import SearchField from '@/components/inner/SearchField.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import StatusBadge from '@/components/inner/StatusBadge.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import { useReveal } from '@/composables/useReveal';
import type { AboutContent, InnerPage, Option, OptionGroup, Paginated, ProgramCard, SectionHeading } from '@/types/inner';

// District Roadshows: photo hero and short intro after startupgrind.com (Dhaka chapter), the event directory after
// startupgrind.com/events (search, district and status filters, date-led rows, each linking to its detail page), photos
// from completed roadshows only, and the teal closing band to the National Journey. Everything comes from the CMS.
const props = defineProps<{
    page: InnerPage;
    content: {
        filters: { q: string; district: string; status: string };
        programs: Paginated<ProgramCard>;
        districtGroups: OptionGroup[];
        statusOptions: Option[];
        gallery: { src: string; caption: string | null; program: string; href: string }[];
        about: AboutContent['intro'];
        headings: Record<'directory' | 'gallery' | 'cta', SectionHeading>;
        empty: { title: string; body: string };
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const { filters, loading, filtered, go, clear } = useDirectory({ ...props.content.filters }, { only: ['content'], anchor: '#directory' });

const part = (iso: string | null, o: Intl.DateTimeFormatOptions) => (iso ? new Date(iso).toLocaleDateString('en-GB', { ...o, timeZone: 'Asia/Dhaka' }) : null);
const time = (iso: string | null) => (iso ? new Date(iso).toLocaleTimeString('en-GB', { hour: 'numeric', minute: '2-digit', hour12: true, timeZone: 'Asia/Dhaka' }) : null);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />

    <div ref="root" class="ground on-light">
        <AboutIntro :intro="content.about" />

        <!-- Directory -->
        <section id="directory" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]" aria-labelledby="dr-directory">
            <div class="container-site">
                <SectionHead id="dr-directory" :heading="content.headings.directory" />
                <ListLayout bare layout="rows" :meta="content.programs.meta" :count="content.programs.data.length" :loading="loading" :filtered="filtered" :empty-title="content.empty.title" :empty-body="content.empty.body" @page="go" @clear="clear">
                    <template #toolbar>
                        <SearchField v-model="filters.q" class="lg:w-72" placeholder="Search roadshows" />
                        <label class="flex flex-col gap-1.5 lg:w-60">
                            <span class="in-meta">District</span>
                            <select v-model="filters.district" class="dr-select">
                                <option value="">All districts</option>
                                <optgroup v-for="g in content.districtGroups" :key="g.label" :label="g.label">
                                    <option v-for="o in g.options" :key="o.value" :value="o.value">{{ o.label }}</option>
                                </optgroup>
                            </select>
                        </label>
                        <FilterChips v-model="filters.status" :options="content.statusOptions" label="Status" class="min-w-0 flex-1 overflow-x-auto" />
                    </template>

                    <Link
                        v-for="p in content.programs.data"
                        :key="p.slug"
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
                                <SampleRibbon v-if="p.sample" />
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
                </ListLayout>
            </div>
        </section>

        <!-- Photos from completed roadshows only; hidden until there are some -->
        <section v-if="content.gallery.length" class="in-section" aria-labelledby="dr-gallery">
            <div class="container-site">
                <SectionHead id="dr-gallery" :heading="content.headings.gallery" />
                <ul class="grid grid-cols-2 gap-3 md:grid-cols-4">
                    <li v-for="(g, i) in content.gallery" :key="g.src" :class="i === 0 && 'md:col-span-2 md:row-span-2'" data-reveal="fade">
                        <Link :href="g.href" class="group relative block h-full overflow-hidden rounded-xl">
                            <img :src="g.src" :alt="g.caption ?? g.program" loading="lazy" class="aspect-[4/3] h-full w-full object-cover transition-transform duration-700 group-hover:scale-105" />
                            <span class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-night/80 to-transparent p-3 pt-10 text-sm font-semibold text-white opacity-0 transition-opacity group-hover:opacity-100">{{ g.program }}</span>
                        </Link>
                    </li>
                </ul>
            </div>
        </section>

        <AboutClosing :heading="content.headings.cta" :page="page">
            <CtaLink href="/national-journey" class="!px-7 !py-4">See the national journey</CtaLink>
        </AboutClosing>
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
.dr-select {
    min-height: 3rem;
    width: 100%;
    appearance: none;
    border: 1px solid rgb(7 20 17 / 0.14);
    border-radius: 0.625rem;
    background: var(--white) url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%23071411' stroke-width='1.6'%3E%3Cpath d='M2 4l4 4 4-4'/%3E%3C/svg%3E") no-repeat right 14px center;
    padding: 0 2.5rem 0 1rem;
    color: var(--night);
}
.dr-select:focus-visible {
    outline: 2px solid var(--green);
    outline-offset: 2px;
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
