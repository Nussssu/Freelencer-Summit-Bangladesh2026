<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import DetailLayout from '@/components/inner/DetailLayout.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InfoCard from '@/components/inner/InfoCard.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SampleRibbon from '@/components/inner/SampleRibbon.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import ShareButton from '@/components/inner/ShareButton.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import type { ArticleCard, InnerPage } from '@/types/inner';

// Brief §16 news article with an SEO-friendly URL (/news/{slug}), share and related posts.
defineProps<{ page: InnerPage; content: { article: ArticleCard & { body: string | null; date: string | null; related: ArticleCard[] } } }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" :image="content.article.image ?? undefined" />
    <InnerHero :page="page">
        <template #actions>
            <div class="flex flex-wrap items-center gap-2">
                <span class="in-pill">{{ content.article.categoryLabel }}</span>
                <span v-if="content.article.date" class="in-meta">{{ content.article.date }}</span>
                <SampleRibbon v-if="content.article.sample" />
            </div>
        </template>
    </InnerHero>

    <DetailLayout aside-label="Article">
        <div v-if="content.article.body" class="in-prose" v-html="content.article.body" />
        <p v-else class="in-body">{{ content.article.excerpt }}</p>

        <template #aside>
            <div><p class="in-meta">Category</p><p class="font-bold text-night">{{ content.article.categoryLabel }}</p></div>
            <div v-if="content.article.date"><p class="in-meta">Published</p><p class="font-bold text-night">{{ content.article.date }}</p></div>
            <div class="flex flex-wrap gap-3 border-t border-night/8 pt-5">
                <ShareButton :title="content.article.title" />
                <InBtn href="/register" tone="gold">Register</InBtn>
            </div>
            <Link href="/news" class="in-link">← All news</Link>
        </template>

        <template v-if="content.article.related.length" #related>
            <SectionHead :heading="{ title: 'Related news', emphasis: 'news' }" />
            <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                <li v-for="r in content.article.related" :key="r.slug"><InfoCard :title="r.title" :href="r.href" :image="r.image ?? '/media/photos/gallery-06.jpg'" :eyebrow="r.categoryLabel" :sample="r.sample" /></li>
            </ul>
        </template>
    </DetailLayout>
</template>
