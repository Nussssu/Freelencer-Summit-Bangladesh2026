<script setup lang="ts">
import { computed, ref } from 'vue';
import AboutHero from '@/components/about/AboutHero.vue';
import WinnerCard from '@/components/awards/WinnerCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Pagination from '@/components/inner/Pagination.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { AwardWinnersContent, InnerPage } from '@/types/inner';

// Brief §9 winner announcement page and permanent archive, laid out like the nominees page: the About page's title
// hero, then every winner grouped by year (newest first) with the category on each card. A long list pages at PER_PAGE
// winners, with the pager at the bottom right.
const props = defineProps<{ page: InnerPage; content: AwardWinnersContent }>();
const root = ref<HTMLElement>();
useReveal(root);

const PER_PAGE = 12;
const current = ref(1);
const ordered = computed(() => [...props.content.winners].sort((a, b) => b.year - a.year));
const meta = computed(() => ({ page: current.value, lastPage: Math.max(1, Math.ceil(ordered.value.length / PER_PAGE)), total: ordered.value.length, perPage: PER_PAGE }));
const groups = computed(() => {
    const slice = ordered.value.slice((current.value - 1) * PER_PAGE, current.value * PER_PAGE);
    return [...new Set(slice.map((w) => w.year))].map((year) => ({ year, winners: slice.filter((w) => w.year === year) }));
});
const list = ref<HTMLElement>();
const go = (p: number) => {
    current.value = p;
    list.value?.scrollIntoView({ behavior: 'smooth', block: 'start' });
};
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <AboutHero :page="page" />

    <div ref="root">
        <section ref="list" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]" aria-label="Winners by year">
            <div class="container-site">
                <template v-if="groups.length">
                    <div v-for="g in groups" :key="g.year" class="mb-14 last:mb-0">
                        <h2 class="mb-6 text-2xl font-bold text-night md:text-3xl" data-reveal="fade">{{ g.year }}</h2>
                        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            <li v-for="w in g.winners" :key="w.name" data-reveal="fade"><WinnerCard :winner="w" archive /></li>
                        </ul>
                    </div>
                    <Pagination :meta="meta" class="mt-14 justify-end!" @go="go" />
                </template>
                <EmptyState v-else level="h2" icon="trophy" tone="tone-sun" title="First edition" body="Winners are announced at the Grand Summit in Dhaka and kept here as a permanent archive." />
            </div>
        </section>
    </div>
</template>
