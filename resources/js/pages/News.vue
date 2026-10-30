<script setup lang="ts">
import { ref } from 'vue';
import FilterChips from '@/components/inner/FilterChips.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import ListLayout from '@/components/inner/ListLayout.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import GetInvolved from '@/components/sections/GetInvolved.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import { useReveal } from '@/composables/useReveal';
import type { ArticleCard, InnerPage, Option, Paginated } from '@/types/inner';

// Brief §16: announcements, roadshow updates, speaker announcements, press releases and coverage, with category tabs.
const props = defineProps<{
    page: InnerPage;
    content: { filters: { category: string }; categories: Option[]; posts: Paginated<ArticleCard>; empty: { title: string; body: string }; upcoming: { category: string; title: string }[] };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const { filters, loading, filtered, go, clear } = useDirectory({ ...props.content.filters }, { only: ['content'], debounce: [], anchor: '#news' });
const date = (iso: string | null) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Dhaka' }) : null);
const photo = (a: ArticleCard) => a.image ?? `/media/photos/gallery-0${(a.slug.length % 9) + 1}.jpg`;
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div id="news" ref="root" class="scroll-mt-[calc(var(--header-h)+1rem)]">
        <ListLayout class="pt-0!" :meta="content.posts.meta" :count="content.posts.data.length" :loading="loading" :filtered="filtered" :empty-title="content.empty.title" :empty-body="content.empty.body" @page="go" @clear="clear">
            <template #toolbar>
                <FilterChips v-model="filters.category" :options="content.categories" label="Category" class="min-w-0 flex-1 overflow-x-auto" />
            </template>
            <InfoCard v-for="a in content.posts.data" :key="a.slug" :title="a.title" :href="a.href" :image="photo(a)" :eyebrow="a.categoryLabel" :meta="[date(a.publishedAt)]" :text="a.excerpt" :sample="a.sample" />
            <template #empty>
                <ul class="grid w-full max-w-xl gap-2 text-left">
                    <li v-for="u in content.upcoming" :key="u.title" class="rounded-xl bg-white/70 p-3"><span class="in-meta">{{ u.category }}</span><p class="text-night">{{ u.title }}</p></li>
                </ul>
                <InBtn href="/register" tone="gold">Register to hear first</InBtn>
            </template>
        </ListLayout>
    </div>

    <GetInvolved />
</template>
