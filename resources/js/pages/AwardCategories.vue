<script setup lang="ts">
import { ref } from 'vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import { useReveal } from '@/composables/useReveal';
import type { DownloadItem, InnerPage, SectionHeading } from '@/types/inner';

// Brief §9: each category with who it is for, eligibility, deadline and a direct "nominate in this category" link.
defineProps<{
    page: InnerPage;
    content: {
        headings: Record<'categories' | 'criteria' | 'timeline', SectionHeading>;
        provisional: boolean;
        categories: { slug: string; name: string; icon: GlyphName; tone: string; text: string | null; href: string }[];
        eligibility: string;
        deadline: string;
        criteria: { icon: GlyphName; title: string; text: string }[];
        criteriaNote: string;
        statuses: string[];
        downloads: DownloadItem[];
    };
}>();
const root = ref<HTMLElement>();
useReveal(root);
const tones = ['tone-teal', 'tone-sun', 'tone-sky', 'tone-violet'];
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <section class="in-section pt-0!">
            <div class="container-site">
                <SectionHead :heading="content.headings.categories"><span v-if="content.provisional" class="in-pill">Provisional</span></SectionHead>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="c in content.categories" :key="c.slug" data-reveal="fade"><TintCard :title="c.name" :text="c.text" :icon="c.icon" :tone="c.tone" :href="c.href" label="Nominate in this category" /></li>
                </ul>
                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="in-card is-solid p-5" data-reveal="fade">
                        <p class="in-meta">Eligibility</p>
                        <p class="mt-1 text-night">{{ content.eligibility }}</p>
                    </div>
                    <div class="in-card is-solid p-5" data-reveal="fade">
                        <p class="in-meta">Submission deadline</p>
                        <p class="mt-1 font-bold text-night">{{ content.deadline }}</p>
                    </div>
                </div>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.criteria" />
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    <li v-for="(c, i) in content.criteria" :key="c.title" data-reveal="fade"><TintCard :title="c.title" :text="c.text" :icon="c.icon" :tone="tones[i]" /></li>
                </ul>
                <p class="in-meta mt-5">{{ content.criteriaNote }}</p>
            </div>
        </section>

        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center">
                <div class="lg:col-span-6">
                    <SectionHead :heading="content.headings.timeline" />
                    <ol class="grid gap-3 sm:grid-cols-2">
                        <li v-for="(s, i) in content.statuses" :key="s" class="in-card is-washed flex-row! items-center gap-4 p-4" :class="tones[i]" data-reveal="fade">
                            <span class="in-num text-2xl!">{{ String(i + 1).padStart(2, '0') }}</span>
                            <span class="font-bold text-night">{{ s }}</span>
                        </li>
                    </ol>
                </div>
                <ul class="grid gap-4 lg:col-span-6">
                    <li v-for="d in content.downloads" :key="d.kind" data-reveal="fade"><DownloadCard :item="d" tone="tone-sun" /></li>
                </ul>
            </div>
        </section>
    </div>

    <CtaBand title="Ready to nominate?" emphasis="nominate?" text="It takes a few minutes and you get a reference number straight away.">
        <InBtn href="/awards/nominate" tone="gold">Nominate for Awards</InBtn>
    </CtaBand>
</template>
