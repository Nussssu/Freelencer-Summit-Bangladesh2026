<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import NewsCard from '@/components/news/NewsCard.vue';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { LatestNewsItem } from '@/types/content';

// Latest news as a sliding row of the shared news cards (components/news/NewsCard) on the site's .marquee loop:
// paused on hover, still under reduced motion, where the row scrolls by hand instead. The first copy is the real list; the second exists only for the loop, so
// it is hidden from assistive tech and taken out of the tab order. A short list repeats to fill the row.
const props = defineProps<{ heading: { title: string; emphasis?: string | null; intro?: string | null }; news: LatestNewsItem[] }>();
const root = ref<HTMLElement>();
useReveal(root);

const row = computed(() => {
    if (!props.news.length) return [];
    const out = [...props.news];
    while (out.length < 6) out.push(...props.news);
    return out;
});
const duration = computed(() => row.value.length * 7);
</script>

<template>
    <section v-if="row.length" ref="root" class="relative overflow-hidden py-20 md:py-28" aria-labelledby="ln-title">
        <div class="container-site text-center">
            <SplitHeading
                id="ln-title"
                :text="heading.title"
                :emphasis="heading.emphasis ?? undefined"
                emphasis-class="text-green"
                class="t-h2"
            />
            <p v-if="heading.intro" class="mt-4 text-xl font-semibold text-ink-2 md:text-2xl" data-reveal="fade">{{ heading.intro }}</p>
        </div>

        <!-- Padding gives the card shadows room inside the masked track (a mask clips to the box, which drew a hard
             line under the cards); the margins cancel it, so the spacing stays as it was. -->
        <div class="track mt-0 -mb-16 pt-12 pb-16 md:mt-4" data-reveal="fade">
            <div class="marquee gap-6 px-3" :style="{ '--marquee-dur': `${duration}s` }">
                <template v-for="copy in 2" :key="copy">
                    <NewsCard
                        v-for="(n, i) in row"
                        :key="`${copy}-${i}`"
                        :title="n.title"
                        :href="n.href"
                        :tag="n.tag"
                        :image="n.image"
                        :text="n.text"
                        :aria-hidden="copy === 2 || undefined"
                        :tabindex="copy === 2 ? -1 : undefined"
                        class="w-[17rem] shrink-0 md:w-[21rem]"
                    />
                </template>
            </div>
        </div>

        <div class="mt-12 text-center" data-reveal="fade">
            <Link href="/news" class="link-u t-label inline-flex min-h-11 items-center gap-2 text-night">All news &amp; updates <Icon name="arrow" :size="16" /></Link>
        </div>
    </section>
</template>

<style scoped>
/* Soft fade at both ends so cards slide in and out instead of being cut */
.track {
    mask-image: linear-gradient(to right, transparent, #000 6%, #000 94%, transparent);
}
.marquee:focus-within {
    animation-play-state: paused;
}
@media (prefers-reduced-motion: reduce) {
    .track {
        overflow-x: auto;
        mask-image: none;
    }
}
</style>
