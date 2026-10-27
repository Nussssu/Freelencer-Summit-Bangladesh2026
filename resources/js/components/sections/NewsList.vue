<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import Icon from '@/components/ui/Icon.vue';
import SplitHeading from '@/components/ui/SplitHeading.vue';
import { useReveal } from '@/composables/useReveal';
import type { Heading, NewsItem } from '@/types/content';

// Section 14. Editorial list: date, category, headline, arrow.
defineProps<{ news: NewsItem[]; heading: Heading }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <section ref="root" data-section="14" aria-labelledby="nw-title" class="on-light section relative z-[1] bg-paper-2">
        <div class="container-site">
            <div class="flex flex-wrap items-end justify-between gap-4">
                <div>
                    <p class="t-label text-green">{{ t('sections.news') }}</p>
                    <SplitHeading id="nw-title" :text="heading.title" :emphasis="heading.emphasis" emphasis-class="text-green" class="t-h2 mt-3" />
                </div>
                <Link href="/news" class="link-u t-label inline-flex min-h-11 items-center gap-2">{{ t('sections.allNews') }} <Icon name="arrow" :size="16" /></Link>
            </div>
            <ul class="mt-10 border-t border-night">
                <li v-for="n in news" :key="n.title" data-reveal="fade">
                    <Link :href="n.slug ? `/news/${n.slug}` : '/news'" class="group grid grid-cols-[1fr_auto] items-center gap-x-6 gap-y-1 border-b border-line-light py-6 transition-colors duration-300 hover:bg-night hover:text-paper md:grid-cols-[8rem_12rem_1fr_auto] md:px-2">
                        <span class="t-label text-ink-2 group-hover:text-muted">{{ n.date ?? t('common.tba') }}</span>
                        <span class="t-label col-start-1 row-start-2 text-green group-hover:text-mint md:col-start-auto md:row-start-auto">{{ n.category }}</span>
                        <span class="col-start-1 row-start-3 font-display text-2xl leading-tight font-black uppercase md:col-start-auto md:row-start-auto md:text-3xl">{{ n.title }}</span>
                        <Icon name="arrow" class="col-start-2 row-span-3 row-start-1 transition-transform duration-300 group-hover:translate-x-1 md:col-start-auto md:row-span-1 md:row-start-auto" />
                    </Link>
                </li>
            </ul>
        </div>
    </section>
</template>
