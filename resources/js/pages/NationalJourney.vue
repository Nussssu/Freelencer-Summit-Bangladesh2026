<script setup lang="ts">
import { ref } from 'vue';
import NjDivisions from '@/components/journey-page/NjDivisions.vue';
import NjProgress from '@/components/journey-page/NjProgress.vue';
import NjRoute from '@/components/journey-page/NjRoute.vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import SectionHead from '@/components/inner/SectionHead.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import JourneyDirectory from '@/components/sections/journey/JourneyDirectory.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import { useReveal } from '@/composables/useReveal';
import type { InnerPage, JourneyContent } from '@/types/inner';

// Brief §15: progress counters, map + timeline from grassroots to Dhaka, 8 division summits, activation directory.
defineProps<{ page: InnerPage; content: JourneyContent }>();
const root = ref<HTMLElement>();
useReveal(root);
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <NewsHero id="nj-title" :title="page.hero.title" :emphasis="page.hero.emphasis" :image="page.hero.image ?? undefined" />

    <div class="nj-heads on-light">
        <NjProgress :heading="content.headings.progress" :counters="content.journey.counters" :reel="content.reel" />
        <NjRoute :heading="content.headings.route" :steps="content.journey.steps" :beacon-label="content.journey.beaconLabel" />
        <NjDivisions :heading="content.headings.divisions" :summits="content.divisionSummits" :href="content.journey.steps[2].href" />

        <section class="border-t border-line-light">
            <JourneyDirectory :directory="content.journey.directory" :steps="content.journey.steps" />
        </section>

        <!-- What each activity page carries (brief §15) -->
        <section ref="root" class="section" aria-labelledby="nj-includes">
            <div class="container-site grid gap-12 lg:grid-cols-12">
                <div class="lg:col-span-4"><SectionHead id="nj-includes" :heading="content.headings.includes" class="lg:sticky lg:top-[calc(var(--header-h)+2rem)]" /></div>
                <dl class="grid border-t border-line-light sm:grid-cols-2 lg:col-span-8">
                    <div v-for="x in content.includes" :key="x.title" class="border-b border-line-light py-7 sm:odd:border-r sm:odd:pr-8 sm:even:pl-8" data-reveal="fade">
                        <dt class="flex items-center gap-3 text-xl font-bold"><span class="h-px w-5 bg-gold" aria-hidden="true" />{{ x.title }}</dt>
                        <dd class="mt-2 max-w-sm text-ink-2">{{ x.text }}</dd>
                    </div>
                </dl>
            </div>
        </section>

        <CtaBand :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
            <CtaLink href="/register">{{ page.hero.actions?.[0]?.label ?? 'Register for Summit' }}</CtaLink>
            <CtaLink :href="content.journey.steps[0].href" tone="outline">{{ content.journey.steps[0].tag }}</CtaLink>
        </CtaBand>
    </div>
</template>

<style scoped>
/*
 * The About page's ground under every section below the hero: ice blue with soft teal and summit-gold glows that
 * alternate sides down the page. Fixed-size glows keep their shape on a long page; one layer, so no seams.
 */
.nj-heads {
    background:
        radial-gradient(900px 640px at 0% 0%, rgb(8 127 140 / 0.2), transparent 70%),
        radial-gradient(900px 640px at 100% 30%, rgb(234 185 75 / 0.26), transparent 70%),
        radial-gradient(900px 640px at 0% 62%, rgb(8 127 140 / 0.18), transparent 70%),
        radial-gradient(900px 640px at 100% 92%, rgb(234 185 75 / 0.32), transparent 70%),
        var(--mist);
}
/*
 * One standard heading size for every section on this page, in the landing's heading face (Host Grotesk 800,
 * uppercase): the shared section heads, the closing band and the programme directory's own title (on this page only;
 * the landing keeps its display title). Not the division card names or table rows.
 */
.nj-heads :deep(.in-h2),
.nj-heads :deep(h3.text-4xl) {
    font-family: var(--font-sans);
    font-weight: 800;
    text-transform: uppercase;
    line-height: 1;
    letter-spacing: -0.02em;
    font-size: clamp(2rem, 3.6vw, 3.25rem);
}
</style>
