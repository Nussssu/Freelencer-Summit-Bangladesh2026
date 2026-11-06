<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import Photo from '@/components/inner/Photo.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import Icon from '@/components/ui/Icon.vue';
import { useReveal } from '@/composables/useReveal';
import type { ArticleCard, InnerPage } from '@/types/inner';

// Brief §16 news article (/news/{slug}), laid out like the reference article page: the "Latest News" title hero held
// in place while the story scrolls up over it on frosted glass (same ground as the page, no separate box): centred
// uppercase headline, date and topic, the full-width square-cornered photo with its italic caption, the story, and
// the way back to all news.
const props = defineProps<{ page: InnerPage; content: { article: ArticleCard & { body: string | null; date: string | null; related: ArticleCard[] } } }>();
const root = ref<HTMLElement>();
useReveal(root);

const a = computed(() => props.content.article);
const photo = computed(() => a.value.image ?? `/media/photos/gallery-0${(a.value.slug.length % 9) + 1}.jpg`);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" :image="a.image ?? undefined" />
    <!-- The hero stays put while the story slides up over it on a pane of frosted glass -->
    <div class="relative isolate">
        <div class="sticky top-0 -z-10">
            <NewsHero title="Latest News" emphasis="News" />
        </div>

        <section ref="root" class="glass relative py-12 md:py-20">
            <div class="container-site">
                <article class="mx-auto max-w-5xl" aria-labelledby="article-title">
                    <h2 id="article-title" class="text-center text-[clamp(1.5rem,2.8vw,2.375rem)] leading-[1.15] font-extrabold tracking-[-0.01em] text-night uppercase [text-wrap:balance]" data-reveal="fade">
                        {{ a.title }}
                    </h2>
                    <div class="mt-5 flex flex-wrap items-center justify-center gap-3" data-reveal="fade">
                        <span v-if="a.date" class="rounded-full bg-green px-4 py-1.5 text-sm font-bold text-snow">{{ a.date }}</span>
                        <span class="rounded-full border border-green/30 px-4 py-1.5 text-sm font-semibold text-green">{{ a.categoryLabel }}</span>
                    </div>

                    <figure class="mt-8 md:mt-10" data-reveal="fade">
                        <Photo :src="photo" :alt="a.title" eager class="aspect-[16/9] w-full" />
                        <figcaption v-if="a.excerpt" class="mt-4 text-sm leading-relaxed text-ink-2 italic md:text-base">{{ a.excerpt }}</figcaption>
                    </figure>

                    <div v-if="a.body" class="story in-prose mt-8 max-w-none md:mt-10" data-reveal="fade" v-html="a.body" />

                    <div class="mt-10 flex justify-center border-t border-night/8 pt-8 md:mt-14 md:pt-10">
                        <Link href="/news" class="group inline-flex min-h-12 items-center gap-3 rounded-full bg-gold px-7 text-sm font-bold tracking-[0.02em] text-night uppercase transition-colors duration-300 hover:bg-[#e0aa38]">
                            View all news <Icon name="arrow" :size="16" class="transition-transform duration-300 group-hover:translate-x-1" />
                        </Link>
                    </div>
                </article>
            </div>
        </section>
    </div>
</template>

<style scoped>
/*
 * The story's ground: the About page's ice blue and glows, made translucent and frosted, so the hero blurs through
 * as the story scrolls over it. A faint light edge marks the top of the pane. Solid where backdrop-filter is missing.
 */
.glass {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.16), transparent 70%),
        radial-gradient(900px 640px at 100% 100%, rgb(234 185 75 / 0.24), transparent 70%),
        rgb(232 242 245 / 0.8);
    backdrop-filter: blur(24px) saturate(1.2);
    -webkit-backdrop-filter: blur(24px) saturate(1.2);
    box-shadow: inset 0 1px 0 rgb(255 255 255 / 0.6);
}
@supports not ((backdrop-filter: blur(1px)) or (-webkit-backdrop-filter: blur(1px))) {
    .glass {
        background-color: var(--mist);
    }
}
/* Story text as on the reference: a bold lede, then plain paragraphs and bold subheads */
.story :deep(p:first-child) {
    font-weight: 600;
    color: var(--night);
}
.story :deep(p) {
    line-height: 1.8;
}
</style>
