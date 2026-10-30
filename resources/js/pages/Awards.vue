<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import DownloadCard from '@/components/inner/DownloadCard.vue';
import EmptyState from '@/components/inner/EmptyState.vue';
import type { GlyphName } from '@/components/inner/Glyph.vue';
import Glyph from '@/components/inner/Glyph.vue';
import InBtn from '@/components/inner/InBtn.vue';
import InnerHero from '@/components/inner/InnerHero.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import TintCard from '@/components/inner/TintCard.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import Accordion from '@/components/ui/Accordion.vue';
import { useReveal } from '@/composables/useReveal';
import type { AwardsContent, InnerPage } from '@/types/inner';

// Brief §9: dedicated awards landing: overview, categories, a nomination flow separate from registration, statuses,
// shortlist and winners archive, guidelines and FAQ.
defineProps<{ page: InnerPage; content: AwardsContent }>();
const root = ref<HTMLElement>();
useReveal(root);
const tones = ['tone-sky', 'tone-rose', 'tone-violet', 'tone-sun', 'tone-mint', 'tone-teal'];
const icons: GlyphName[] = ['rocket', 'star', 'ai', 'pen', 'building', 'cap'];
const factIcons: GlyphName[] = ['pen', 'calendar', 'trophy', 'check'];
const slug = (s: string) => s.toLowerCase().replace(/&/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <InnerHero :page="page" />

    <div ref="root">
        <!-- Overview + key facts -->
        <section class="in-section pt-0!">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:items-center lg:gap-12">
                <div class="lg:col-span-6">
                    <SectionHead :heading="content.headings.overview" />
                    <p class="in-lead -mt-4" data-reveal="fade">{{ content.awards.eligibility }}</p>
                </div>
                <dl class="grid gap-3 sm:grid-cols-2 lg:col-span-6">
                    <div v-for="(f, i) in content.facts" :key="f.label" class="in-card is-washed flex-row! items-center gap-4 p-4" :class="tones[i]" data-reveal="fade">
                        <span class="in-disc w-11!"><Glyph :name="factIcons[i]" /></span>
                        <div class="min-w-0"><dt class="in-meta">{{ f.label }}</dt><dd class="font-bold text-night">{{ f.value }}</dd></div>
                    </div>
                </dl>
            </div>
        </section>

        <!-- Categories -->
        <section class="in-section">
            <div class="container-site">
                <SectionHead :heading="content.headings.categories">
                    <span v-if="content.awards.categoriesProvisional" class="in-pill">{{ $t('common.provisional') }}</span>
                    <InBtn href="/awards/categories" tone="ghost">All category details</InBtn>
                </SectionHead>
                <ul class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    <li v-for="(c, i) in content.awards.categories" :key="c" data-reveal="fade"><TintCard :title="c" :text="content.categoryNote" :icon="icons[i % 6]" :tone="tones[i % 6]" :href="`/awards/nominate?category=${slug(c)}`" label="Nominate" /></li>
                </ul>
            </div>
        </section>

        <!-- How to apply + statuses -->
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12 lg:gap-12">
                <div class="lg:col-span-7">
                    <SectionHead :heading="content.headings.steps" />
                    <ol class="grid gap-4 sm:grid-cols-2">
                        <li v-for="(s, i) in content.steps" :key="s.title" data-reveal="fade"><TintCard :title="s.title" :text="s.text" :number="String(i + 1).padStart(2, '0')" :tone="tones[i]" /></li>
                    </ol>
                </div>
                <div class="lg:col-span-5">
                    <SectionHead :heading="content.headings.statuses" />
                    <div class="in-media is-round mb-4 aspect-[16/9]" data-reveal="clip"><img src="/media/photos/highlight-awards.jpg" alt="" loading="lazy" /></div>
                    <ol class="grid gap-3">
                        <li v-for="(st, i) in content.awards.statuses" :key="st" class="in-card is-washed flex-row! items-center gap-4 p-4" :class="i === content.awards.statuses.length - 1 ? 'tone-sun' : 'tone-teal'" data-reveal="fade">
                            <span class="in-num text-2xl!">{{ String(i + 1).padStart(2, '0') }}</span>
                            <span class="font-bold text-night">{{ st }}</span>
                        </li>
                    </ol>
                </div>
            </div>
        </section>

        <!-- Shortlist + winners archive -->
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-2">
                <div>
                    <SectionHead :heading="content.headings.shortlist" />
                    <EmptyState level="h3" icon="star" tone="tone-sky" :title="content.shortlistEmpty.title" :body="content.shortlistEmpty.body" />
                </div>
                <div>
                    <SectionHead :heading="content.headings.winners" />
                    <EmptyState level="h3" icon="trophy" tone="tone-sun" :title="content.winnersEmpty.title" :body="content.winnersEmpty.body" />
                </div>
            </div>
        </section>

        <!-- Guidelines + FAQ -->
        <section class="in-section">
            <div class="container-site grid gap-8 lg:grid-cols-12">
                <div class="lg:col-span-4">
                    <SectionHead :heading="content.headings.faq" />
                    <ul class="space-y-4">
                        <li v-for="d in content.downloads" :key="d.kind"><DownloadCard :item="d" tone="tone-sun" /></li>
                    </ul>
                    <p class="mt-4 text-sm"><Link href="/contact?topic=awards" class="font-semibold text-green">Ask the awards team</Link></p>
                </div>
                <div class="in-card is-solid in-pad lg:col-span-8" data-reveal="fade"><Accordion :items="content.faq" id-prefix="aw-faq" /></div>
            </div>
        </section>
    </div>

    <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
        <InBtn href="/awards/nominate" tone="gold">Nominate for Awards</InBtn>
        <InBtn href="/awards/categories" tone="ghost">View award categories</InBtn>
    </CtaBand>
</template>
