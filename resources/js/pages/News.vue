<script setup lang="ts">
import { ref } from 'vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import Pagination from '@/components/inner/Pagination.vue';
import { useDirectory } from '@/components/inner/useDirectory';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsCard from '@/components/news/NewsCard.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import { useReveal } from '@/composables/useReveal';
import type { ArticleCard, InnerPage, Paginated } from '@/types/inner';

// Brief §16 news, laid out like the reference "all news" page: a short title hero, then one long grid of cards,
// newest first (four across on desktop). Pages only appear when there are more posts than one page holds.
const props = defineProps<{
    page: InnerPage;
    content: { posts: Paginated<ArticleCard>; empty: { title: string; body: string } };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const { go } = useDirectory({}, { only: ['content'], debounce: [], anchor: '#news' });

const date = (iso: string | null) => (iso ? new Date(iso).toLocaleDateString('en-GB', { day: '2-digit', month: '2-digit', year: 'numeric', timeZone: 'Asia/Dhaka' }) : 'TBA');
const photo = (a: ArticleCard) => a.image ?? `/media/photos/gallery-0${(a.slug.length % 9) + 1}.jpg`;
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <NewsHero :title="page.hero.title" :emphasis="page.hero.emphasis" :image="page.hero.image ?? undefined" />

    <section id="news" ref="root" class="news-ground scroll-mt-[calc(var(--header-h)+1rem)] py-14 md:py-20" aria-label="News">
        <div class="container-site">
            <ul v-if="content.posts.data.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <li v-for="a in content.posts.data" :key="a.slug" class="flex" data-reveal="fade">
                    <NewsCard :title="a.title" :href="a.href" :tag="date(a.publishedAt)" :image="photo(a)" :lines="3" class="w-full" />
                </li>
            </ul>
            <EmptyState v-else :title="content.empty.title" :body="content.empty.body" />

            <Pagination v-if="content.posts.meta.lastPage > 1" :meta="content.posts.meta" class="mt-12" @go="go" />
        </div>
    </section>
</template>

<style scoped>
/* The About page's ground: ice blue with soft teal and summit-gold glows */
.news-ground {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 100%, rgb(234 185 75 / 0.28), transparent 70%),
        var(--mist);
}
</style>
