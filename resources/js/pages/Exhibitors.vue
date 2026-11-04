<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import ExhibitionHero from '@/components/exhibition/ExhibitionHero.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import InBtn from '@/components/inner/InBtn.vue';
import Pagination from '@/components/inner/Pagination.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { ExhibitorCard, InnerPage, Paginated } from '@/types/inner';

// Full exhibitor list after ai-expo.net/global/exhibitors, without the search: every exhibitor in sequence (A–Z), one
// row each with logo, name, profile text with "read more", sector tag and the stand number on the right; eight a page,
// pager on the right.
type Exhibitor = ExhibitorCard & { description: string | null; website: string | null };
defineProps<{ page: InnerPage; content: { exhibitors: Paginated<Exhibitor> } }>();
const root = ref<HTMLElement>();
useReveal(root);

// One colour per sector, derived from its name so it stays the same on every page
const palette = ['#e8306b', '#f06a1c', '#087f8c', '#2f8fdc', '#7b5cd6', '#b07a12'];
const sectorColour = (s: string) => palette[[...s].reduce((h, c) => (h * 31 + c.charCodeAt(0)) >>> 0, 7) % palette.length];
const list = ref<HTMLElement>();
function go(page: number) {
    router.get(location.pathname, page > 1 ? { page } : {}, {
        only: ['content'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => list.value?.scrollIntoView({ behavior: 'smooth', block: 'start' }),
    });
}
const initials = (name: string) => name.split(/\s+/).filter(Boolean).slice(0, 2).map((w) => w[0]).join('').toUpperCase();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <ExhibitionHero :page="page" />

    <div ref="root">
        <section ref="list" class="in-section scroll-mt-[calc(var(--header-h)+1rem)]" aria-label="Exhibitors">
            <div class="container-site">
                <ol v-if="content.exhibitors.data.length" class="border-t border-night/12">
                    <li v-for="e in content.exhibitors.data" :key="e.slug" class="grid gap-6 border-b border-night/12 py-10 md:grid-cols-[13rem_1fr_auto] md:items-center md:gap-10" data-reveal="fade">
                        <Link :href="e.href" class="grid h-28 w-full max-w-52 place-items-center rounded-xl bg-white p-4 ring-1 ring-night/8" :aria-label="e.name">
                            <img v-if="e.logo" :src="e.logo" alt="" loading="lazy" class="max-h-full w-auto object-contain" />
                            <span v-else class="font-display text-4xl font-black text-green" aria-hidden="true">{{ initials(e.name) }}</span>
                        </Link>

                        <div class="min-w-0">
                            <h2 class="text-[clamp(1.75rem,2.6vw,2.25rem)] leading-tight font-extrabold">
                                <Link :href="e.href" class="transition-colors hover:text-green">{{ e.name }}</Link>
                            </h2>
                            <p v-if="e.description" class="ex-desc mt-4 text-[1.0625rem] leading-[1.9] text-night/85">{{ e.description }}</p>
                            <Link :href="e.href" class="mt-3 inline-block font-semibold text-green hover:underline">…read more</Link>
                            <ul class="mt-5 flex flex-wrap gap-3">
                                <li class="rounded px-4 py-2 text-sm font-bold text-white" :style="{ background: sectorColour(e.sector) }">{{ e.sector }}</li>
                            </ul>
                        </div>

                        <div class="flex items-baseline gap-3 md:flex-col md:items-end md:gap-1 md:text-right">
                            <span class="text-sm font-bold">Stand</span>
                            <span class="font-display text-4xl leading-none font-black">{{ e.booth ?? 'TBA' }}</span>
                        </div>
                    </li>
                </ol>
                <div v-if="content.exhibitors.data.length" class="flex flex-wrap items-center justify-between gap-4">
                    <p class="in-meta mt-10">{{ (content.exhibitors.meta.page - 1) * content.exhibitors.meta.perPage + 1 }}–{{ (content.exhibitors.meta.page - 1) * content.exhibitors.meta.perPage + content.exhibitors.data.length }} of {{ content.exhibitors.meta.total }} exhibitors</p>
                    <Pagination :meta="content.exhibitors.meta" class="ml-auto justify-end!" @go="go" />
                </div>
                <EmptyState v-else level="h2" icon="store" title="Exhibitors announced soon" body="Confirmed exhibitors appear here with their stand number.">
                    <InBtn href="/exhibition/apply" tone="gold">Exhibit at Summit</InBtn>
                </EmptyState>
            </div>
        </section>
    </div>

</template>

<style scoped>
.ex-desc {
    display: -webkit-box;
    -webkit-line-clamp: 4;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
