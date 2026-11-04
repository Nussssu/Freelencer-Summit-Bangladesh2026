<script setup lang="ts">
import { computed, ref } from 'vue';
import AboutHero from '@/components/about/AboutHero.vue';
import NomineeCard from '@/components/awards/NomineeCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Pagination from '@/components/inner/Pagination.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { AwardNomineesContent, InnerPage } from '@/types/inner';

// Brief §9 shortlisted nominee showcase: the About page's title hero, then every nominee grouped by award category.
// A long list pages at PER_PAGE nominees (category order kept), with the pager at the bottom right.
const props = defineProps<{ page: InnerPage; content: AwardNomineesContent }>();
const root = ref<HTMLElement>();
useReveal(root);

const PER_PAGE = 12;
const current = ref(1);
const ordered = computed(() => {
    const order = [...new Set(props.content.nominees.map((n) => n.category))];
    return [...props.content.nominees].sort((a, b) => order.indexOf(a.category) - order.indexOf(b.category));
});
const meta = computed(() => ({ page: current.value, lastPage: Math.max(1, Math.ceil(ordered.value.length / PER_PAGE)), total: ordered.value.length, perPage: PER_PAGE }));
const groups = computed(() => {
    const slice = ordered.value.slice((current.value - 1) * PER_PAGE, current.value * PER_PAGE);
    return [...new Set(slice.map((n) => n.category))].map((category) => ({ category, nominees: slice.filter((n) => n.category === category) }));
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
        <section ref="list" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]" aria-label="Shortlisted nominees by category">
            <div class="container-site">
                <template v-if="groups.length">
                    <div v-for="g in groups" :key="g.category" class="mb-14 last:mb-0">
                        <h2 class="mb-6 text-2xl font-bold text-night md:text-3xl" data-reveal="fade">{{ g.category }}</h2>
                        <ul class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                            <li v-for="n in g.nominees" :key="n.name" data-reveal="fade"><NomineeCard :nominee="n" :category="false" /></li>
                        </ul>
                    </div>
                    <Pagination :meta="meta" class="mt-14 justify-end!" @go="go" />
                </template>
                <EmptyState v-else level="h2" icon="star" tone="tone-sky" title="Revealing soon" body="Shortlisted nominees are published here once the jury has reviewed every application." />
            </div>
        </section>
    </div>
</template>
