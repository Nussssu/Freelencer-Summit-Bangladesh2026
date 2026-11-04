<script setup lang="ts">
import { ref } from 'vue';
import { useI18n } from 'vue-i18n';
import EmptyState from '@/components/inner/EmptyState.vue';
import SpeakerDialog from '@/components/speakers/SpeakerDialog.vue';
import SpeakerHoverCard from '@/components/speakers/SpeakerHoverCard.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, SpeakersContent } from '@/types/inner';

// Brief §13: speaker grid of photo cards (photo, role, name, designation · organisation). No search, filters or profile pages.
defineProps<{ page: InnerPage; content: SpeakersContent }>();
const { t } = useI18n();
const root = ref<HTMLElement>();
useReveal(root);

// Each role gets its own pill colour.
const roleColours: Record<string, string> = {
    'Chief Guest': 'bg-gold text-night',
    'Special Guest': 'bg-[#e8506b] text-white',
    Guest: 'bg-[#2f8fdc] text-white',
    Speaker: 'bg-[#7b5cd6] text-white',
};
const rolePill = (role: string | null) => (role && roleColours[role]) || 'bg-gold text-night';
// Index of the speaker shown in the popup (null = closed).
const current = ref<number | null>(null);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <NewsHero id="speakers-title" showcase focus="60% 30%" :title="page.hero.title" :emphasis="page.hero.emphasis" :image="page.hero.image ?? undefined" />

    <div ref="root">
        <section class="in-section" aria-labelledby="sp-directory">
            <div class="container-site">
                <SectionHead id="sp-directory" :heading="content.headings.directory" />
                <ul v-if="content.speakers.length" class="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    <li v-for="(p, i) in content.speakers" :key="p.slug" data-reveal="fade">
                        <SpeakerHoverCard :person="p" @click="current = i" />
                    </li>
                </ul>
                <EmptyState v-else level="h3" icon="people" :title="t('inner.empty.title')" :body="t('inner.empty.body')" />
                <SpeakerDialog v-model:index="current" :people="content.speakers" :pill-class="rolePill" />
            </div>
        </section>
    </div>
</template>
