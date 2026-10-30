<script setup lang="ts">
import { ref } from 'vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import ListLayout from '@/components/inner/ListLayout.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import SearchField from '@/components/inner/SearchField.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { ExhibitorCard, InnerPage, Option, Paginated, SectionHeading } from '@/types/inner';

// Brief §11: who should exhibit and why, floor plan with booth status, searchable exhibitor directory, visitor information.
const props = defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'why' | 'who' | 'floor' | 'directory' | 'visit', SectionHeading>;
        why: { title: string; icon: GlyphName; tone: string }[];
        who: { title: string; icon: GlyphName }[];
        booths: { code: string; status: string; size: string | null; x: number; y: number; w: number; h: number; sample: boolean }[];
        boothsNote: string;
        legend: Option[];
        filters: { q: string; sector: string };
        exhibitors: Paginated<ExhibitorCard>;
        sectors: Option[];
        visit: { icon: GlyphName; title: string; text: string }[];
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const { filters, loading, filtered, go, clear } = useDirectory({ ...props.content.filters }, { only: ['content'], anchor: '#directory' });
const fill: Record<string, string> = { available: '#7fe0c8', reserved: '#eab94b', confirmed: '#f42a41' };
const photos = ['/media/photos/exhibit-booth.jpg', '/media/photos/highlight-exhibition.jpg', '/media/photos/highlight-marketplace.jpg', '/media/photos/sponsor-hall.jpg'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.why" />
                <ul class="grid gap-5 md:grid-cols-3">
                    <li v-for="w in content.why" :key="w.title" data-reveal="fade"><TintCard :title="w.title" :icon="w.icon" :tone="w.tone" /></li>
                </ul>
            </div>
        </section>

        <!-- Who should exhibit: photo cards -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.who"><InBtn href="/exhibition/apply" tone="gold">Exhibit at Summit</InBtn></SectionHead>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(w, i) in content.who" :key="w.title" class="in-card is-solid overflow-hidden" data-reveal="fade">
                        <span class="in-media aspect-[4/3]"><img :src="photos[i % 4]" alt="" loading="lazy" /></span>
                        <p class="in-h3 p-5 text-lg!">{{ w.title }}</p>
                    </li>
                </ul>
            </div>
        </section>

        <!-- Floor plan -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.floor">
                    <ul class="flex flex-wrap items-center gap-4">
                        <li v-for="l in content.legend" :key="l.value" class="flex items-center gap-2 text-sm font-semibold"><span class="size-3.5 rounded-full" :style="{ background: fill[l.value] }" aria-hidden="true" />{{ l.label }}</li>
                    </ul>
                </SectionHead>
                <div class="in-card is-solid in-pad" data-reveal="fade">
                    <div class="overflow-x-auto">
                        <svg viewBox="0 0 800 300" class="min-w-[560px]" role="img" aria-label="Exhibition floor plan with booth status">
                            <g v-for="b in content.booths" :key="b.code">
                                <rect :x="b.x" :y="b.y" :width="b.w || 90" :height="b.h || 90" rx="10" :fill="fill[b.status] ?? '#e7f2f5'" fill-opacity="0.55" stroke="#071411" stroke-opacity="0.12" />
                                <text :x="b.x + (b.w || 90) / 2" :y="b.y + (b.h || 90) / 2 + 5" text-anchor="middle" font-size="15" font-weight="700" fill="#071411">{{ b.code }}</text>
                            </g>
                        </svg>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-3">
                        <SampleRibbon v-if="content.booths.some((b) => b.sample)" />
                        <p class="in-meta">{{ content.boothsNote }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Directory -->
        <section id="directory" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]">
            <div class="container-site">
                <SectionHead :heading="content.headings.directory" />
                <ListLayout bare layout="rows" :meta="content.exhibitors.meta" :count="content.exhibitors.data.length" :loading="loading" :filtered="filtered" empty-title="Exhibitors announced soon" empty-body="Confirmed exhibitors appear here with their booth number and sector." @page="go" @clear="clear">
                    <template #toolbar>
                        <SearchField v-model="filters.q" class="lg:w-72" placeholder="Search exhibitors" />
                        <FilterChips v-model="filters.sector" :options="content.sectors" label="Sector" class="min-w-0 flex-1 overflow-x-auto" />
                    </template>
                    <InfoCard v-for="e in content.exhibitors.data" :key="e.slug" layout="row" :title="e.name" :href="e.href" :logo="e.logo" :meta="[e.booth ? `Booth ${e.booth}` : 'Booth to be allocated']" :tags="[e.sector]" :sample="e.sample" />
                    <template #empty><InBtn href="/exhibition/apply" tone="gold">Apply for a booth</InBtn></template>
                </ListLayout>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.visit" />
                <ul class="grid gap-5 md:grid-cols-3">
                    <li v-for="(v, i) in content.visit" :key="v.title" data-reveal="fade"><TintCard :title="v.title" :text="v.text" :icon="v.icon" :tone="['tone-teal', 'tone-sky', 'tone-violet'][i]" /></li>
                </ul>
            </div>
        </section>
    </div>

    <GetInvolved />
</template>
