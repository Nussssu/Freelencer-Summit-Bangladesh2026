<script setup lang="ts">
import NjDivisions from '@/components/journey-page/NjDivisions.vue';
import NjFinder from '@/components/journey-page/NjFinder.vue';
import NjProgress from '@/components/journey-page/NjProgress.vue';
import NjRoute from '@/components/journey-page/NjRoute.vue';
import CtaBand from '@/components/inner/CtaBand.vue';
import SeoHead from '@/components/layout/SeoHead.vue';
import NewsHero from '@/components/news/NewsHero.vue';
import CtaLink from '@/components/ui/CtaLink.vue';
import type { InnerPage, JourneyContent } from '@/types/inner';

// Brief §15: progress counters, map + timeline from grassroots to Dhaka, 8 division summits, programme finder.
defineProps<{ page: InnerPage; content: JourneyContent }>();
</script>

<template>
    <SeoHead :title="page.title" :description="page.description" :path="page.path" />
    <NewsHero id="nj-title" :title="page.hero.title" :emphasis="page.hero.emphasis" :image="page.hero.image ?? undefined" />

    <div class="nj-heads on-light">
        <NjProgress :heading="content.headings.progress" :counters="content.journey.counters" :reel="content.reel" />
        <NjRoute :heading="content.headings.route" :steps="content.journey.steps" :beacon-label="content.journey.beaconLabel" />
        <NjDivisions :heading="content.headings.divisions" :summits="content.divisionSummits" :href="content.journey.steps[2].href" />
        <NjFinder :heading="content.headings.finder" :directory="content.journey.directory" />
        <CtaBand class="nj-cta" :title="content.headings.cta.title" :emphasis="content.headings.cta.emphasis ?? undefined" :text="content.headings.cta.intro ?? undefined">
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
/* The closing band in the About page's colours (AboutClosing): deep teal gradient, paper text, gold emphasis. */
.nj-cta :deep(.in-card) {
    background:
        radial-gradient(60% 120% at 100% 0%, rgb(8 127 140 / 0.55), transparent 70%),
        linear-gradient(120deg, #06454c, #0a5f69);
    border-color: transparent;
    color: var(--paper);
}
/* Same width as the About band: up to 90rem, 16px gutters (24px from md). */
.nj-cta :deep(.container-site) {
    max-width: 90rem;
    padding-inline: 1rem;
}
@media (min-width: 768px) {
    .nj-cta :deep(.container-site) {
        padding-inline: 1.5rem;
    }
}
.nj-cta :deep(.in-h2) {
    color: var(--paper);
}
.nj-cta :deep(.in-pool) {
    display: none;
}
.nj-cta :deep(.text-green) {
    color: var(--gold);
}
.nj-cta :deep(.in-lead) {
    color: rgb(242 236 223 / 0.8);
}
.nj-cta :deep(.btn-outline) {
    --btn-hover: rgb(242 236 223 / 0.12);
}
</style>
